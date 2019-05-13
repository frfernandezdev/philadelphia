<?php 

  namespace Core\HTTP;

  /**
   * Handler file of request.
   */
  class File
  {
    public $error;
    public $name;
    public $tmp_name;
    public $size;
    public $type;
    
    /**
     * Add file a context at instance of this class.
     * 
     * @param array $file
     * @return void 
     */
    public function __construct(array $file) 
    {
      $error = $file['error'];
      $filename = $file['name'];
      $tmp_name = $file['tmp_name'];
      $size = $file['size'];
      $type = $file['type'];
    }

    public function __toString() {
      return $this->name;
    }

    /**
     * Return info of context file at instance of this class.
     * 
     * @return array
     */
    public function info()
    {
      return $this;
    }
    
    /**
     * Delete file in based a path.
     * 
     * @param string $path
     * @return void
     */
    public function delete(string $path) 
    {
      unlink(''.$path);
    }

    /**
     * Save image in base 64 bits
     * 
     * @param string $name
     * @param string $content
     * @return mixed{bool|string} return true|false || name of file.
     */
    public function save64Image(string $name, string $content) 
    {
      $binary_data = base64_decode($content);
      if (file_put_contents(__DIR__. '' . time() . '_' . $name, $binary_data))
      {
        return $name;
      }
      else 
      {
        return false;
      }
    }
    
    /**
     * Save context info of context file at instance of this class.
     * 
     * @return mixed{bool|string} return true|false || name of file.
     */
    public function save() 
    {
      try 
      {
        if (!isset($this->file['error']) || is_array($this->file['error']))
        {
          return false;
        }

        switch ($this->file['error'])
        {
          case UPLOAD_ERR_OK:
            break;
          default:
            return false;
        }

        $name = time() . '_' . $this->file['name'];

        if (!file_put_contents(__DIR__. '' . $name, file_get_contents($this->file['tmp_name'])))
        {
          return false;
        }

        return $name;
      }
      catch (RuntimeException $e) {
        return false;
      }
    }
  }