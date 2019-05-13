<?php

  namespace Core;

  use Exception;

  ini_set("html_errors", 1); 
  ini_set("error_prepend_string", "<pre style='color: #333; font-face:monospace; font-size:8pt;'>"); 
  ini_set("error_append_string ", "</pre>"); 

  class Exceptions extends Exception
  {
    public function __construct() {}
  }