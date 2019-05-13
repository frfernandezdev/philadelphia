<?php 

  namespace Core\Database;

	use Core\Exceptions;
  use Core\Database\Interfaces\BuilderInterface;
  use Core\Database\Manager;

  use Core\Database\Query\Where;
  use Core\Database\Query\OrderBy;
  use Core\Database\Query\Pipeline;
  use Core\Database\Query\Select;
  use Core\Database\Query\Insert;
  use Core\Database\Query\Update;
  use Core\Database\Query\Delete;
  
	use Core\Helpers\Util;

  class Builder implements BuilderInterface
  {
    use Where; 
    use OrderBy; 
    use Pipeline;
    use Select; 
    use Insert; 
    use Update; 
    use Delete;

    protected static $sql;
    protected static $select;
    protected static $skip;
    protected static $limit;
    protected static $orderBy;
    protected static $where;
    protected static $fields = [];
    protected static $colonFields = [];
    protected static $values = [];
    protected static $db;
    protected static $types;
    protected static $omit;
    protected static $as;
    protected static $count;

    protected function __construct() 
    {
      self::$db = new Database();
    }
    
    protected static function addAnd(&$sql)
    {
      return $sql .= " AND ";
    }

    protected static function addOr(&$sql)
    {
      return $sql .= " OR ";
    }

    protected static function addNot(&$sql)
    {
      return $sql .= " NOT ";
    }

    protected static function operator($op) 
    {
      switch($op)
      {
        case '=': 
          return $op;
        case '<': 
          return $op;
        case '>': 
          return $op;
        case '<=': 
          return $op;
        case '>=': 
          return $op;
        case '!=': 
          return $op;
        case '<>': 
          return $op;
        case 'LIKE': 
          return $op;
        default:
          throw new Exceptions('Not exists this operator.');
          break;
      }
    }

    protected static function parse_args(&$in)
    {
      if (key_exists(0, $in) && (count($in) <= 1) && is_array($in[0]))
      {
        return $in = $in[0];
      }

      $out = array_values($in);
      
      if (count($out) > 3) {
        throw new Exceptions("
                          Just can only pass 2 or 3 parameters for the pipeline `where`
                          example
                          `
                            ->where('column', 'other columns')
                            ->where('column', 'operator('=', '>', '<' ...)', 'other columns')
                          `");
      }
      
      return $in = [$out];
    }

    protected static function prepare_select()
    {
      $select = null;
      $where = null;

      if (!empty(self::$select))
      {
        $select = self::$select;
      }
      self::$select = $select ?: "*";

      if (!empty(self::$where))
      {
        $where = self::$where;
      }
      self::$where = $where ? " WHERE " . $where : "";
      
      self::$sql = " {self::$select} FROM {self::$table} {self::$as} {self::$where} {self::$orderBy} {self::$limit} {self::$skip}";
    }

    protected static function prepare_insert()
    {
      self::$fields = implode(",", self::$fields);
      self::$colonFields = implode(",", self::$colonFields);
      self::$sql = " {self::$table} {self::$as} ({self::$fields}) VALUES ({self::$colonFields}) ";
    }
    
    protected static function prepare_insertMany()
    {
      self::$fields = implode(",", self::$fields);
      self::$colonFields = implode(",", self::$colonFields);
      self::$sql = " {self::$table} {self::$as} ({self::$fields}) VALUES ({self::$colonFields}) ";
    }
    
    protected static function prepare_update()
    {
      $where = null;
      self::$fields = implode(",", self::$fields);

      if (!empty(self::$where))
      {
        $where = self::$where;
      }
      self::$where = $where ? " WHERE " . $where : "";

      self::$sql = " {self::$table} {self::$as} SET {self::$fields} {self::$where}";

    }

    protected static function prepare_delete() 
    {

    }

    protected static function autoRun()
    {
      $response = null;
      
      switch(self::$types)
      {
        case 0:
          self::$prepare_select();
          
          if (self::$omit)
          {
            break;
          }
          
          if (self::$count) {
            $response = self::$db->__select(self)->fetchColumn();
            $response = (object) array(
              'count' => $response
            ); 
            break;
          }

          $response = self::$db->__select(self)->fetch();
          
          if (!$response)
          {
            $response = (object) array(
              'item' => array());
            break;
          }
          
          $response = (object) array(
            'item' => $response);
          break;
        case 1:
          self::$prepare_select();
          
          if (self::$omit)
          {
            break;
          }
          
          $response = self::$db->__select(self);
          if (!$response->rowCount() > 0)
          {
            $response = (object) array(
              'items' => [],
              'count' => 0);
          }
          $response = (object) array(
                      'items' => $response->fetchAll(),
                      'count' => $response->rowCount());
          break;
        case 2:
          self::$prepare_insert();

          if (self::$omit)
          {
            break;
          }
          
          $response = self::$db->__insert(self);
          $response = (object) array(
            'id' => $response);
          break;
        case 3:
          self::$prepare_insertMany();
          
          if (self::$omit)
          {
            break;
          }
          
          $response = self::$db->__insertMany(self);
          $response = (object) array(
            'id' => $response);
          break;
        case 4:
          self::$prepare_update();
          
          if (self::$omit)
          {
            break;
          }
          
          $response = self::$db->__update(self);
          break;
        case 5:
          self::$prepare_delete();
          
          if (self::$omit)
          {
            break;
          }
          
          $response = self::$db->__delete(self);
          $response = (object) array(
            'id'
          );
          break;
      }

      self::resetProperties(self);
      
      return $response;
    }

    public static function resetProperties(&$self)
    {
      $self::$sql = null;
      $self::$select = null;
      $self::$skip = null;
      $self::$limit = null;
      $self::$orderBy = null;
      $self::$where = null;
      $self::$fields = [];
      $self::$colonFields = [];
      $self::$values = [];
      $self::$types = null;
      $self::$as = null;
      $self::$count = null;
    }
  }



