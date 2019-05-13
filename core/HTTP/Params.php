<?php

  namespace Core\HTTP;

  use Core\Helpers\Path;

  /**
   * Handler params of request.
   * 
   */
  class Params 
  {
    /**
     * Add the params in $_GET to this class instance.
     */
    public function __construct() 
    {
      $this->__new($_GET);
    }

    /**
     * Add property to this class instance; if has exists add a new file at array.
     * 
     * @param string $name
     * @param array $file
     * @return void
     */
    public function __add(string $name, $value) 
    {
      if (!property_exists($this, $name))
      {
        $this->{Path::toCamelCase($name)} = $value;
      }
    }
    
    /**
     * Add at array properties to this class instance.
     * 
     * @param array $arr [properties]
     * @return void
     */
    public function __new(array $arr) 
    {
      foreach ($arr as $key => $value) {
        $this->__add($key, $value);
      }  
    }
  }