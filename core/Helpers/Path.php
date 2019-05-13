<?php

  namespace Core\Helpers;

  /**
   * 
   * Path, manages the obtaining of 
   * the properties of an array, based on a string
   * 
   */  
  class Path
  {
    /**
     * Normalize, Take a string and convert it to array.
     * 
     * `test.name` to [`test`, `name`]
     * 
     * @param string $path
     * @return array
     */
    public static function normalize(string $path) : array
    {
      return explode('.', $path);
    } 

    /**
     * Loop, Take two array, a within 
     * parts search in the array.
     * 
     * @param array $parts
     * @param array $arr
     * @return any
     */    
    public static function loop(array $parts, array $arr)
    {
      $keyArrs = array_keys($arr);
      $keyParts = array_values($parts);
      $current = $arr;
      
      while(true) 
      {
        $current_arr = current($keyArrs);
        $current_part = current($keyParts);
        
        if ($current_arr === $current_part) 
        {
          
          if (end($parts) === $current_part) 
          {
            return $current[$current_arr];
          } 
          
          if ((count($parts) - 1) === key($keyParts)) {
            return;
          }
          

          $keyArrs = array_keys($current[$current_arr]);
          $current = $current[$current_arr];
          next($keyParts);
          continue;
        }

        if (end($current) === $current_arr)
        {
          return;
        }

        if (($k = array_keys($current)) && (end($k) === $current_arr)) {
          return;
        }

        next($keyArrs);
      }
    }

    /**
     * Convert first letter from string on uppercase and floor(_).
     * 
     * @param string $string
     * @return string
     */
    public static function toCamelCase(string $string) : string
    {
      $result = strtolower($string);
        
      preg_match_all('/_[a-z]/', $result, $matches);
      foreach($matches[0] as $match)
      {
        $c = str_replace('_', '', strtoupper($match));
        $result = str_replace($match, $c, $result);
      }
      return $result;
    }
  }