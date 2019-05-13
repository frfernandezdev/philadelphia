<?php 

  namespace Core\Database\Query;

  trait Pipeline
  {
    public function count(...$args) 
    {
      $this->types = 0;
      
      if (!empty($args))
      {
        $this->parse_args($args);
      }
      if (!empty($args) && is_array($args))
      {
        $sql = implode(",", $args);
        $this->select = "COUNT({$sql})";
      }
      else 
      {
        $this->select = "COUNT(*)";
      }
      $this->count = true;
      return $this->autoRun();
		}

		public function avg(string $field) 
		{
			$this->select = "MAX({$field})";
			return $this->autoRun();
		}
		
		public function max(string $field) 
		{
			$this->select = "MAX({$field})";
			return $this->autoRun();
    }
    
    public function increment() 
		{

		}

		public function decrement() 
		{

		}
  }