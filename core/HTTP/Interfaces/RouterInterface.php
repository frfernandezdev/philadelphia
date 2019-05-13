<?php 

	namespace Core\Interfaces;

	use Core\HTTP\Interfaces\RequestInterface as IRequest;
	use Core\HTTP\Interfaces\ResponseInterface as IResponse;

	interface RouterInterface 
	{
		/**
		 * 
		 */
		public function __construct();
		
		/**
		 * 
		 */
		public function __call(string $name, $args);

	}