<?php 

	namespace Core\Helpers;

	class Util
	{
		/**
		 * has_string_keys
		 * 
		 * Determine if a array has keys typing string.
		 * Example: `
		 * 	has_string_keys(['a' => '', 'b' => '', 'c' => '']) : true
		 * 	has_string_keys([0 => 'a', 1 => 'b', 2 => 'c']) : false
		 * `
		 * 
		 * @param array $array
		 * @return bool
		 */
		public static function has_string_keys(array $array) {
			return count(array_filter(array_keys($array), 'is_string')) > 0;
		}

		/**
		 * merge_array_key_value
		 * 
		 * Convert associative array, in a indexed array 
		 * 
		 */
		public static function merge_array_key_value(array $array) {
			$arr = [];
			foreach($array as $key => $value)
			{
				$arr[] = $key;
				$arr[] = $value; 
			}

			return $arr;
		}
	}