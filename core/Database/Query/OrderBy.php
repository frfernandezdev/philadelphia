<?php 

	namespace Core\Database\Query;

	trait OrderBy
	{
		public function orderBy($field, $sort = 1)
		{
			$orderBy = $sort == -1 ? "DESC" : "ASC";  

			$this->orderBy = "ORDER BY {$field} {$orderBy}";
			return $this;
		}

		public function latest(string $field = 'create_at')
		{   
			$this->orderBy($field, -1);
			return $this;
		}
		
		public function oldest(string $field = 'create_at')
		{
			$this->orderBy($field, 1);
			return $this;
		}

		public function inRandomOrder()
		{
			$this->orderBy = "ORDER BY RAND()";
			return $this;
		}
	}