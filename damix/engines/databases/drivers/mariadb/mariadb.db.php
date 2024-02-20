<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\databases\drivers;

include( 'mariadbdbresultset.db.php');

class MariadbDbConnection
	extends \damix\engines\databases\DbConnection
{
	protected object|bool $statement = false;
	protected array $bind = array();
	
	protected function _connect() : bool
	{
		$this->cnx = @new \mysqli($this->host, $this->user, $this->password, $this->database, $this->port);
		
		$this->cnx->set_charset( $this->charset );
		
		return true;
	}
	
	protected function _disconnect() : bool
	{
		return $this->cnx->close();
	}
	
	protected function _doQuery(string $query) : ?\damix\engines\databases\DbResultSet
	{
		\damix\engines\logs\log::log( $query, 'sql' );
		if($qI=$this->cnx->query($query)){
			return new \damix\engines\databases\drivers\MariadbDbResultSet($qI);
		}
		else
		{
			\damix\engines\logs\log::log( $query, 'error' );
		}
		return null;
	}
	
	protected function _doExec(string $query) : int|string
	{
		\damix\engines\logs\log::log( $query, 'sql' );
		if($this->cnx->query($query))
		{
			return $this->cnx->affected_rows;
		}
		else
		{
			\damix\engines\logs\log::log( $query, 'error' );
		}
		return 0;
	}
	
	public function prepare(string $query) : void
	{
		$this->statement = $this->cnx->prepare( $query );
	}
	
	public function execute() : bool
	{
		if( $this->statement )
		{
			$keys = array();
			$val = array();
			$keys[0] = '';
			foreach( $this->bind as $param)
			{
				$keys[0] .= $param['type'];
				$val[] = $param['value'];
			}
			
			$params = array_merge($keys,$val);
			call_user_func_array(array($this->statement, 'bind_param'), $this->makeValuesReferenced($params));
			return $this->statement->execute();
		}
		return false;
	}
	
	private function makeValuesReferenced($arr){

        $refs = array();

        foreach($arr as $key => $value)

        $refs[$key] = &$arr[$key];

        return $refs;
    }

	public function bindString(string $value) : void
	{
		$this->bind[] = array( 'type'=> 's', 'value' => $value );
	}
	
	public function bindInt(int $value) : void
	{
		$this->bind[] = array( 'type'=> 'i', 'value' => $value );
	}
	
	public function bindDecimal(float $value) : void
	{
		$this->bind[] = array( 'type'=> 'd', 'value' => $value );
	}
	
	protected function rowCount() : int|string
	{
		return $this->cnx->affected_rows;
	}
	
	public function beginTransaction() : void
	{
		$this->_autoCommitNotify(false);
	}
	
	public function commit() : void
	{
		$this->cnx->commit();
		$this->_autoCommitNotify(true);
	}
	
	public function rollback() : void
	{
		$this->cnx->rollback();
		$this->_autoCommitNotify(true);
	}
	
	protected function _autoCommitNotify(bool $state) : bool
	{
		return $this->cnx->autocommit($state);
	}
	
	
	protected function _quote(string $text ) : string
	{
		return $this->cnx->real_escape_string($text);
	}
	
	public function lastInsertId() : int
	{
		return $this->cnx->insert_id;
	}
	
	public function errorCode() : string
	{
		return $this->cnx->errno;
	}
	
	public function errorMessage() : string
	{
		return $this->cnx->error;
	}
}