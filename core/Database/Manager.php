<?php 

	namespace Core\Database;

	use PDO;
	use Core\Exceptions;
	use Core\Settings;
	use Core\Database\Interfaces\ManagerInterface;
	use Core\Database\Interfaces\BuilderInterface as IBuilder;

	class Manager 
						extends PDO 
								implements ManagerInterface
  {
		private $driver;
		private $host;
		private $dbname;
		private $username;
		private $password;
		private $chatset;
		private $options = array();
		private $sql;

		/**
		 * Constructor, run connection with database.
		 * 
		 */
		public function __construct() 
		{
			$settings = new Settings::$settings;
			$this->driver = $settings->get('database.driver');
			$this->host = $settings->get('database.host');
			$this->dbname = $settings->get('database.dbname');
			$this->username = $settings->get('database.username');
			$this->password = $settings->get('database.password');
			$this->chatset = $settings->get('database.chatset');
			$this->sql = "";
			$this->values = [];

			$this->setConfig($settings);

			$this->__connect();
		}

		/**
		 * Connected with database.
		 * 
		 * @param array $options.
		 */
		public function __connect()
		{
			$dsn = "{$this->driver}:dbname={$this->dbname};host={$this->host};chatset={$this->chatset}";
			try 
			{
				parent::__construct($dsn, $this->username, $this->password, $this->options);
				// print('Connection Success');
			} 
			catch(PDOException $ex) 
			{
				$this->rollBack();
				throw new Exceptions($e->getCode(), 'Connection failed:'.$e->getMessage());
			}
		}

		/**
		 * Set config.
		 * 
		 * @param ISettings $settings.
		 */
		public function setConfig($settings) 
		{
			$this->options = array(
				PDO::ATTR_PERSISTENT 		    => $settings->get('database.persistent') ? true : false,
				PDO::ATTR_ERRMODE    		    => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE 	=> PDO::FETCH_OBJ,
				PDO::MYSQL_ATTR_FOUND_ROWS 		=> true 
			);
		} 

		/**
		 * Run execute sql with values.
		 * 
		 * @param PDO::prepare $statement.
		 * @param bool $last.
		 * 	if is true
		 * 		return lastInsertId.
		 * 	else if false
		 * 		return $statement.
		 * 	else 
		 * 		return true|false
		 * @return statement|bool|int
		 */
		public function runExecute(&$statement, $last = false, $op = false) 
		{
			try 
			{
				$rs = $statement->execute($this->values);

				if($rs && $last) 
				{
					return $this->lastInsertId();
				}
				else if (!$last && $op)
				{
					return true;
				}
				else if ($rs && !$last && $statement->rowCount() > 0)
				{
					return $statement;
				}
				else if ($statement->errorCode() != '00000' && $statement->errorInfo())
				{
					throw new Exceptions($statement->errorCode(), implode(" ", $statement->errorInfo()));
				}
				return false;
			}
			catch(PDOException $ex)
			{
				throw new Exceptions($ex->getCode(), 'Error at runExecute' .implode(" ", $ex->errorInfo()));
			}
			catch(Exception $ex)
			{
				throw new Exceptions($ex->getCode(), 'Error at runExecute' .implode(" ", $ex->errorInfo()));
			}
		}
		
		/**
		 * Run execute sql with values and transaction,
		 * for insert many rows in just one time.
		 * 
		 * @param PDO::prepare $statement.
		 * @param bool $last.
		 * 	if is true
		 * 		return lastInsertId.
		 * 	else if false
		 * 		return true|false.
		 * @return int|bool 
		 */
		public function runExecuteTransation(&$statement, $last = false, $op = false) 
		{
			try 
			{
				$this->beginTransaction();
				
				foreach($this->values as $row)
				{
					$statement->execute($row);
				}
				
				$response = false;
				if($last && $this->lastInsertId()) 
				{
					$response = $this->lastInsertId();
				}
				else if (!$last && !$op) 
				{
					$response = true;
				}
				else if ($statement->errorCode() != '00000' && $statement->errorInfo())
				{
					throw new Exceptions($statement->errorCode(), implode(" ", $statement->errorInfo()));
				}
				
				$this->commit();
				return $response;
			} 
			catch (PDOException $ex) {
				if ($this->inTransaction())
				{
					$this->rollBack();
				}  

				throw new Exceptions($ex->getCode(), 'Error transaction at insert many document `PDOException`' .implode(" ", $ex->errorInfo()));
			}
			catch (Exception $ex) {
				if ($this->inTransaction())
				{
					$this->rollBack();
				}  

				throw new Exceptions($ex->getCode(), 'Error transaction at insert many document `Exception`' .implode(" ", $ex->errorInfo()));
			}
		}

		/**
		 * Run query sql.
		 * 
		 * @return array|bool
		 */
		public function runQuery() 
		{
			try 
			{
				$result = $this->query($this->sql);
				
				if ($result->errorCode() != '00000' && $result->errorInfo())
				{
					throw new Exceptions($result->errorCode(), implode(" ", $result->errorInfo()));
				}
				return $result;
			}
			catch (PDOException $ex) {
				throw new Exceptions($ex->getCode(), 'Error at runQuery sql `PDOException`' .implode(" ", $ex->errorInfo()));
			}
			catch (Exception $ex) {
				throw new Exceptions($ex->getCode(), 'Error at runQuery sql `Exception`' .implode(" ", $ex->errorInfo()));
			}
		}

		/**
		 * Run exec sql.
		 * 
		 * @return int number affeted rows.
		 */
		public function runExec() 
		{
			return $this->exec($this->sql);
		}

		/**
		 * Select by sql.
		 * 
		 * @param IBuilder $builder.
		 * 
		 * @return array
		 */
		public function __select(IBuilder $builder) 
		{
			try
			{
				$this->sql = "SELECT" . $builder->sql;
				return $this->runQuery();
			}
			catch(PDOException $ex)
			{
				throw new Exceptions($ex->getCode(), 'Error at select document' .implode(" ", $ex->errorInfo()));
			}
			catch(Exception $ex)
			{
				throw new Exceptions($ex->getCode(), 'Error at select document' .implode(" ", $ex->errorInfo()));
			}
		}

		/**
		 * Insert by sql.
		 * 
		 * @param IBuilder $builder.
		 * 
		 * @return int lastInsertId.
		 */
		public function __insert(IBuilder $builder) 
		{
			try 
			{
				$this->sql = "INSERT INTO" . $builder->sql;
				$this->values= $builder->values;
				$statement = $this->prepare($this->sql);
				return $this->runExecute($statement, true);
			}
			catch(PDOException $ex)
			{
				throw new Exceptions($ex->getCode(), 'Error at insert document' .implode(" ", $ex->errorInfo()));
			}
			catch(Exception $ex)
			{
				throw new Exceptions($ex->getCode(), 'Error at insert document' .implode(" ", $ex->errorInfo()));
			}
		}

		/**
		 * InsertMany by sql.
		 * 
		 * @param IBuilder $builder.
		 * 
		 * @return int [lastInsertId].
		 */
		public function __insertMany(IBuilder $builder)
		{
			try 
			{
				$this->sql = "INSERT IGNORE INTO" . $builder->sql;
				$this->values = $builder->values;
				$statement = $this->prepare($this->sql);
				return $this->runExecuteTransation($statement, true);
			}
			catch(PDOException $ex)
			{
				throw new Exceptions($ex->getCode(), 'Error at insert many document' .implode(" ", $ex->errorInfo()));
			}
			catch(Exception $ex)
			{
				throw new Exceptions($ex->getCode(), 'Error at insert many document' .implode(" ", $ex->errorInfo()));
			}
		}

		/**
		 * Update by sql.
		 * 
		 * @param IBuilder $builder.
		 * 
		 * @return bool [bool].
		 */
		public function __update(IBuilder $builder) 
		{
			try 
			{
				$this->sql = "UPDATE" . $builder->sql;
				$this->values = $builder->values;
				$statement = $this->prepare($this->sql);
				return $this->runExecute($statement, false, true);
			}
			catch(PDOException $ex)
			{
				throw new Exceptions($ex->getCode(), 'Error at update document' .implode(" ", $ex->errorInfo()));
			}
			catch(Exception $ex)
			{
				throw new Exceptions($ex->getCode(), 'Error at select document' .implode(" ", $ex->errorInfo()));
			}
		}

		/**
		 * Delete by sql.
		 * 
		 * @param IBuilder $builder.
		 * 
		 * @return int|bool 
		 * 	number offected rows or true if sucess operation.
		 */
		public function __delete(IBuilder $builder)
		{
			try
			{
				$this->sql = "DELETE" . $builder->sql;
				$this->values = $builder->values();

				if (empty($this->values))
				{
					return $this->runExec();
				}

				$statement = $this->prepare($this->sql);
				return $this->runExecute($statement, false, true);
			}
			catch(PDOException $ex)
			{
				throw new Exceptions($ex->getCode(), 'Error at select document' .implode(" ", $ex->errorInfo()));
			}
			catch(Exception $ex)
			{
				throw new Exceptions($ex->getCode(), 'Error at select document' .implode(" ", $ex->errorInfo()));
			}
		} 
  };
