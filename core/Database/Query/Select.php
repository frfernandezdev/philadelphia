<?php 

	namespace Core\Database\Query;

	use Core\Exceptions;

	trait Select
	{
		public function select(...$args)
		{
			$this->parse_args($args);

			$this->select = implode(",", $args);
			return $this;
		}   

		public function selectRaw() 
		{

		}

		public function skip(int $n) 
		{
			if (empty($this->limit) && empty($this->take) && empty($this->offset))
			{
				throw new Exceptions(
					"Function skip need spefitly the limit, 
					please used the functions as `take`, `limit`, `offset`");
			}
			
			$this->skip = " OFFSET {$n} ";
			return $this;
		}

		public function limit(int $n)
		{
			$this->limit = " LIMIT {$n} ";
			return $this;
		}

		public function values(...$args)
		{
			$this->types = 1;
			$this->select = implode(",", $args);
			return $this->autoRun();
		}
		
		public function exists()
		{
			if (empty($this->where))
			{
				throw new Exceptions("Not found sql with sentence WHERE before");  
			}
				
			return (empty($this->first()) ? false : true);
		}
	
		public function doesntExist() 
		{
			if (empty($this->where))
			{
				throw new Exceptions("Not found sql with sentence WHERE before");  
			}

			return (empty($this->first()) ? true : false);
		}

		public function get() 
		{
			$this->types = 1;
			return $this->autoRun();
		}
		
		public function first()
		{
			$this->types = 0;
			return $this->autoRun();
		}
	}