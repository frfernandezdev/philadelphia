<?php

  namespace Core\Helpers;

  use Core\Interfaces\RouterInterface;
  use Core\HTTP\Request;
  use Core\HTTP\Response;

  /**
   * Handler Router request.
   * 
   */
  class Router implements RouterInterface
  {
    // Base path
    public $base;
    // Storage request.
    public $request;
    // Storage response.
    private $response;
    // Allowed method.
    private $supportedHttpMethods = array(
      "GET",
      "POST",
      "PUT",
      "PATCH",
      "DELETE",
      "USE",
      "MIDDLEWARE"
    );

    /**
     * Wilds cards, pattern for parse params by url.
     * 
     */
    private $wild_cards = array(
      'int' => '/^[0-9]+$/',
      'string' => '/^[a-zA-Z]+$/',
      'bool' => '/^[0-1]|(true|false)+$/'
    );

    /**
     * 
     */
    public function __construct() 
    {
      $this->request = new Request; 
      $this->response = new Response($this->request);
    }
    
    /**
     * 
     */
    public function __call($name, $args) 
    {
      list($route, $fn) = $args;

      if (!in_array(strtoupper($name), $this->supportedHttpMethods)) {
        return $this->invalidMethodHandler();
      }
      $r = strtoupper($name) === 'MIDDLEWARE' && $route === ''
                                          ? 'MIDDLEWARE'
                                          : $this->formatRoute($route);
      
      $this->{strtolower($name)}[$r] = $fn;
    }

    /**
     * Removes trailing forward slashes from the right of the route.
     * @param route (string)
     */
    private function formatRoute(string $route)
    {
      $result = rtrim($route, '/');

      // extract params from url
      if (strpos($result, '?')) 
      {
        $arr = explode('?',$result);
        $result = $arr[0];
      }

      if ($result === '')
      {
        return '/';
      }
      return $result;
    }

    /**
     * Response with status 405 method not allowed.
     * 
     * @return header
     */
    private function invalidMethodHandler() : void
    {
      $this->response->http_response_code(405);
    }

    /**
     * Response with status 404 not found
     * 
     * @return header 
     */
    private function defaultRequestHandler() : void
    {
      $this->response->http_response_code(404);
    } 

    private function getUri($route) 
    {
      return $this->base 
        ? $this->base . $route
        : $route;
    }

    /**
     * Match wild cards in url, and return array with inside method to call.
     * 
     * @param string $route
     * @return mixed
     */
    private function _match_wild_cards($route)
    {
      $var = array();

      if ($route === 'MIDDLEWARE')
      {
        return true;
      }
      else 
      {
        $request = $this->formatRoute($this->request->requestUri);
        $route = $this->formatRoute($this->getUri($route));
      } 

      $exp_request = explode('/', $request);
      $exp_route = explode('/', $route);

      if (count($exp_request) === count($exp_route))
      {
        foreach($exp_route as $key => $value)
        {
          if ($value === $exp_request[$key])
          {
            if ($key === (count($exp_route) - 1)) 
            {
              return true;
            }
            continue;
          }
          elseif (substr($value, 0, 1) == '{' && substr($value, -1) == '}') 
          {
            $strip = str_replace(array('{', '}'), '', $value);
            $exp = explode(':', $strip); 

            if ($exp[0] != "" && array_key_exists($exp[0], $this->wild_cards))
            {
              $pattern = $this->wild_cards[$exp[0]];

              if (preg_match($pattern, $exp_request[$key]))
              {
                if (isset($exp[1]))
                {
                  $var[$exp[1]] = $exp_request[$key]; 
                }
                continue;
              }
            }
            else 
            {
              $var[$exp[1]] = $exp_request[$key];
              continue;
            }
          }

          return false;
        }

        return $var;
      }

      return false;
    }

    private function _match_path($path, $fn)
    {
      foreach($path as $key => $value) 
      {
        if ($match = $this->_match_wild_cards($key)) 
        {
          if (is_array($match)) 
          {
            $this->request->params->__new($match);
          }

          return call_user_func_array($fn, array($value, array($this->request, $this->response), $key));
        }
      }
      return false;
    }

    private function _match_method($method, $fn)
    {
      $request = $this->formatRoute($this->request->requestUri);
      foreach($method as $key => $value) 
      {
        $str = preg_replace('/[\/]/', '\/', substr($request, 1));
        preg_match('/['.$str.'-]+/', $key, $match);
        
        if ($match[0] === $key) 
        {
          return call_user_func_array($fn, array($value, array($this->request, $this->response), $key)); 
        }
      }
      return false;
    }

    private function _call_middleware() 
    {
      $this->_match_method($this->{'middleware'}, function($fn, $args) {
        return call_user_func_array($fn, $args);
      });
    }

    private function _call_use() 
    {
      return $this->_match_method($this->{'use'}, function($fn, $args, $base) {
        $fn->base = $base;
        $fn->request = $this->request;
        call_user_func(array($fn, 'resolve'));
        return true; 
      });
    }
    
    /**
     * Resolves a route
     * 
     * @return mixed{void|defaultRequestHandler}
     */
    public function resolve()
    {
      // Execute for method as ['middleware'].
      if (property_exists($this, 'middleware'))
      {
        $this->_call_middleware();
      }
      // Execute for method as ['use'].
      if (property_exists($this, 'use'))
      {
        $passed = $this->_call_use();

        if ($passed) {
          return;
        } 
      }

      // Execute for request method as ['GET', 'POST', 'PUT', 'PATH', 'DELETE'].
      $requestMethod = strtolower($this->request->requestMethod);
      if (!property_exists($this, $requestMethod))
      {
        return $this->defaultRequestHandler();
      }
      
      $method = $this->{$requestMethod};
      $passed = $this->_match_path($method, function($fn, $args) {
        return call_user_func_array($fn, $args);
      });

      if ($passed) {
        return $this->defaultRequestHandler();
      }
    }
  }