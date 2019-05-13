<?php 

	namespace Core\Database\Query;

	use Core\Helpers\Util;

	trait Insert 
	{
		protected function __insertOne(array $body) 
		{
			foreach($body as $field => $value)
			{
				$this->colonFields[] = ":$field";
				$this->fields[] = $field;
			}

			$this->values = $body;
			$this->types = 2;
			return $this->autoRun();
		}

		protected function __insertMany(array $body)
		{
			$fields = [];

			foreach($body as $row)
			{
				$current = array_keys($row);
				if (empty($fields))
				{
					$fields = $current;
				}
				
				if ($merge = array_merge(array_diff($fields, $current), array_diff($current, $fields)))
				{
					$fields = array_merge($fields, $merge);
				}
			}

			$colonFields = [];
			foreach($fields as $field) 
			{
				$colonFields[] = ":$field"; 
			}

			$values = [];
			foreach($body as $row) 
			{
				$current = array_keys($row);
				
				if($merge = array_merge(array_diff($fields, $current), array_diff($current, $fields)))
				{
					foreach($merge as $m) 
					{
						$row["$m"] = null;
					}
				}
				$values[] = $row;
			}

			$this->fields = $fields;
			$this->colonFields = $colonFields;
			$this->values = $values;
			$this->types = 3;
			return $this->autoRun();
		}

		public function insert(array $body)
		{
			if (Utils::has_string_keys($body))
			{
				return $this->__insertOne($body);
			}
			else 
			{
				return $this->__insertMany($body);
			}
		}
	}
