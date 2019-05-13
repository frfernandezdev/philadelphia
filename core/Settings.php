<?php

  namespace Core;

  use Core\Exceptions;
  use Core\Helpers\Path;
  use Core\Interfaces\SettingsInterface;

  /**
   * Settings
   * 
   * 
   */
  class Settings
  {
    public static $path;
    public static $settings;

    public $file;

    // Initialitation var config.
    private $config = [
      'database' => [],
      'jwt' => []
  ];

    // Default configuratio
    private $defaultItemsSettings = [
      'database' => [
          'driver' => '',
          'host' => '',
          'port' => '',
          'dbname' => '',
          'username' => '',
          'password' => '',
          'chatset' => 'utf8',
          'persistent' => false,
      ],
      'jwt' => [
        'host' => '',
        'privateKey' => '',
        'algorithm' => ''
      ]
    ];
    
    public function __construct()
    {
      if (!file_exists(static::$path))
      {
        throw new Exceptions("File settings not exists, inside projects $path");
      }

      $this->file = parse_ini_file(static::$path);

      $this->extractParams();

      static::$settings = $this;
    }

    private function extractParams() 
    {
      if (!array_key_exists('database', $this->file)) 
      {
        $this->config['database'] = $this->defaultItemsSettings['database'];
      }

      $database = $this->defaultItemsSettings['database'];

      $this->config['database']['driver'] = array_key_exists('driver', $this->file) 
                      ? $this->file['driver']
                        : $database['driver'];
      $this->config['database']['host'] = array_key_exists('host', $this->file) 
                      ? $this->file['host']
                        : $database['host'];
      $this->config['database']['dbname'] = array_key_exists('dbname', $this->file) 
                      ? $this->file['dbname']
                        : $database['dbname'];
      $this->config['database']['username'] = array_key_exists('username', $this->file)  
                      ? $this->file['username']
                        : $database['username'];
      $this->config['database']['password'] = array_key_exists('password', $this->file)
                      ? $this->file['password']
                        : $database['password'];
      $this->config['database']['chatset'] = array_key_exists('chatset', $this->file) 
                      ? $this->file['chatset']
                        : $database['chatset'];
      $this->config['database']['persistent'] = array_key_exists('persistent', $this->file)
                      ? $this->file['persistent']
                        : $database['persistent'];
          
      if (!array_key_exists('jwt', $this->file)) 
      {
        $this->config['jwt'] = $this->defaultItemsSettings['jwt'];
      }

      $jwt = $this->defaultItemsSettings['jwt'];

      $this->config['jwt']['host'] = array_key_exists('host', $this->file)
                      ? $this->file['host']
                        : $jwt['host'];

      $this->config['jwt']['privateKey'] = array_key_exists('privateKey', $this->file)
                      ? $this->file['privateKey']
                        : $jwt['privateKey'];
      
      $this->config['jwt']['algorithm'] = array_key_exists('algorithm', $this->file)
                      ? $this->file['algorithm']
                        : $jwt['algorithm'];
    }

    public static function setSettingsToDatabase(string $path)
    {
      static::$path = $path;
    }

    /**
     * Get params by path typing string.
     * 
     * @param string $id
     * @return any 
     */
    public function get($id)
    {   
      $parts = Path::normalize($id);
      
      return (Path::loop($parts, $this->config));
    }

    /**
     * Has params by path typing string.
     * 
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
      $parts = Path::normalize($id);

      return (Path::loop($parts, $this->config) ? true : false);
    }
  }