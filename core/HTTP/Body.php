<?php

  namespace Core\HTTP;

  use Core\HTTP\Interfaces\RequestInterface as IRequest;
  use Core\HTTP\File;
  use Core\Helpers\Path;

  /**
   * Handler body of request.
   * 
   */
  class Body
  {
    /**
     * Parse request, depending on the type of request.
     * 
     * Allowed content types.
     *  - multipart/form-data.
     *  - application/x-www-form-urlencoded.
     *  - application/json.
     * 
     * @param IRequest $request
     * @return void
     */
    public function __construct(IRequest $request)
    {
      if (property_exists($request, 'contentType')) {
        $type = explode(';', $request->contentType)[0];
        
        switch ($type) 
        {
          case 'multipart/form-data':
          case 'application/x-www-form-urlencoded': 
            $this->_FormData();
            break;
          case 'application/json': 
            $this->_JsonData(); 
            break;
        }
      }
    }

    /**
     * Parse raw data from header of request. 
     */
    private function _FormData()
    {
      $putdata = fopen("php://input", "r");
      $raw_data = '';
      while ($chunk = fread($putdata, 1024))
      {
        $raw_data .= $chunk;
      }
      fclose($putdata);
      $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));
      
      if(empty($boundary))
      {
        parse_str($raw_data, $data);
        return $this->__new($data);
      }
      
      $parts = array_slice(explode($boundary, $raw_data), 1);
      $data = array();
      
      foreach ($parts as $part) 
      {
        if ($part == "--\r\n") {
          break;
        } 
        
        $part = ltrim($part, "\r\n");
        list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);
        
        $raw_headers = explode("\r\n", $raw_headers);
        $headers = array();
        foreach ($raw_headers as $header) 
        {
          list($name, $value) = explode(':', $header);
          $headers[strtolower($name)] = ltrim($value, ' ');
        }

        if (isset($headers['content-disposition'])) 
        {
          $filename = null;
          $tmp_name = null;
          preg_match(
            '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
            $headers['content-disposition'],
            $matches
          );
          list(, $type, $name) = $matches;
          if(isset($matches[4]))
          {
            if(isset($_FILES[$matches[2]]))
            {
              $this->__file($matches[2], $_FILES[$matches[2]]);
              continue;
            }
            $filename = $matches[4];
            $filename_parts = pathinfo($filename);
            $tmp_name = tempnam( ini_get('upload_tmp_dir'), $filename_parts['filename']);
            $file = $_FILES[$filename] = new File(array(
              'error'    => 0,
              'name'     => $filename,
              'tmp_name' => $tmp_name,
              'size'     => strlen($body),
              'type'     => $value
            ));
            $this->__file($filename, $file);
            file_put_contents($tmp_name, $body);
          }
          else
          {
            $data[$name] = substr($body, 0, strlen($body) - 2);
          }
        }
      }
      $this->__new($data);
    }

    /**
     * Parse json format data from header of request.
     */
    private function _JsonData() 
    {
      $raw = file_get_contents('php://input', true);
      $body = json_decode($raw);
      
      $this->__new((array) $body);
    }

    /**
     * Add property a this class instance.
     */
    public function __add(string $name, $value) 
    {
      if (!property_exists($this, $name)) 
      {
        $this->{Path::toCamelCase($name)} = $value;
      }
    }

    /**
     * Add property to this class instance; if has exists add a new file at array.
     * 
     * @param string $name
     * @param array $file
     * @return void
     */
    public function __file(string $name, array $file) {
      if (!property_exists($this, 'file')) 
      {
        $this->{'file'} = [$name => $file];
        return;
      }

      $this->file = array_merge($this->file, array($name => $file));
    }
    
    /**
     * Add at array properties to this class instance.
     * 
     * @param array $arr [properties]
     * @return void
     */
    public function __new(array $arr) 
    {
      if (is_array($arr)) {
        foreach ($arr as $key => $value)
        {
          $this->__add($key, $value);
        }
      }
    }
  }