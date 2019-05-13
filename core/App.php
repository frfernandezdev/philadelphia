<?php

  namespace Core;

  use Core\Settings;
  use Core\Interfaces\SettingsInterface;

  use Core\HTTP\Router;

  use InvalidArgumentException;

  /**
   * 
   * App
   * 
   * This is the primary class with you instantiate,
   * configure, and run the application.
   * 
   */
  class App
  {
    /**
     * Current version
     * 
     * @var string 
     */
    const VERSION = '1.0.0';  

    /**
     * Create a new application
     * 
     */
    public function __construct()
    {
      $this->route = new Router();
    }

    public function setting(string $settings)
    {
      Settings::$path = $settings;
    }

    public function use() 
    {
      $path = '';
      if (func_num_args() == 1) 
      {
        $router = func_get_arg(0);
      }
      else {
        $path = func_get_arg(0);
        $router = func_get_arg(1);
      }
      
      $this->route->use($path, $router);
    }

    public function middleware()
    {
      $path = '';
      if (func_num_args() == 1) 
      {
        $router = func_get_arg(0);
      }
      else {
        $path = func_get_arg(0);
        $router = func_get_arg(1);
      }

      // array_push($this->middleware, $this->route->middleware($path, $router));
      $this->route->middleware($path, $router);
    }

    public function get($path, $fn) 
    {
      return $this->route->get($path, $fn);
    }

    public function post($path, $fn) 
    {
      return $this->route->post($path, $fn);
    }

    public function put($path, $fn) 
    {
      return $this->route->put($path, $fn);
    }

    public function path($path, $fn) 
    {
      return $this->route->path($path, $fn);
    }
    
    public function delete($path, $fn) 
    {
      return $this->route->delete($path, $fn);
    }

    public function run() 
    {
      $this->route->resolve();
    }
  }