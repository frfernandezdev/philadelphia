<?php

  namespace Core\HTTP\Interfaces;

  interface ResponseInterface 
  {
    /**
     * Print html.
     * 
     * @param HTML $html 
     *  example: $var = <<<HTML
     *  <h1>Hello World</h1> 
     * HTML;
     * html($var);
     * 
     * @return echo $html.
     */
    public function html($html);
    /**
     * Change status the response.
     */
    public function status(int $code);
    /**
     * Response with format type json.
     * 
     * @param array $args
     * example:
     *  json(array('Hello' => 'World'));
     * @return echo json_encode
     */
    public function json($args);
  };