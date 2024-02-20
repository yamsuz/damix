<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\databases\drivers;

include( 'pgsqlbresultset.db.php');

class PgsqlDbConnection
	extends \damix\engines\databases\DbConnection
{
	protected object|bool $statement = false;
	protected array $bind = array();
	
	protected function _connect() : bool
	{	
		$conn_string = "host=" . $this->host . " port=" . $this->port . " dbname=" . $this->database . " user=" .  $this->user . " password=" . $this->password;

		if( $this->cnx == null )
		{
			if( $cnx = @pg_connect($conn_string) )
			{
				$this->cnx = $cnx;
			}
			else
			{
				throw new \damix\core\exception\OrmException('');
			}
			
			pg_set_client_encoding( $this->cnx, $this->charset );
		}
		
		return true;
	}
	
	protected function _disconnect() : bool
	{
		if( $this->cnx )
		{
			$stat = pg_connection_status($this->cnx);
			if( $stat == PGSQL_CONNECTION_OK )
			{
				@pg_close( $this->cnx );
				$this->cnx = null;
			}
			return true;
		}
		return false;
	}
	
	protected function _doQuery(string $query) : ?\damix\engines\databases\DbResultSet
	{
		\damix\engines\logs\log::log( $query, 'sql' );
		if($qI=pg_query($this->cnx, $query)){
			return new \damix\engines\databases\drivers\PgsqlDbResultSet($qI);
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
		if($rs=pg_query($this->cnx, $query)){
			return pg_affected_rows($rs);
		}
		else{
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
		throw new Exception();
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
		return pg_num_rows( $this->cnx );
	}
	
	public function beginTransaction() : void
	{
		if( $this->_doExec('BEGIN') )
		{
			$this->_autoCommitNotify(false);
		}
	}
	
	public function commit() : void
	{
		$this->_doExec('COMMIT');
	}
	
	public function rollback() : void
	{
		$this->_doExec('ROLLBACK');
	}
	
	protected function _autoCommitNotify(bool $state) : bool
	{
		$this->_doExec('SET AUTOCOMMIT TO '.($state ? 'ON' : 'OFF'));
		
		return true;
	}
	
	
	protected function _quote(string $text ) : string
	{
		return pg_escape_string($this->cnx, $text);
	}
	
	public function lastInsertId() : int
	{
		if($rs=$this->_doQuery('SELECT lastval()')){
			if( $info = $rs->fetch() )
			{
				return intval($info->lastval ?? 0 );
			}
		}
		return 0;
	}
	
	public function errorCode() : string
	{
		return pg_last_error( $this->cnx);
	}
	
	public function errorMessage() : string
	{
		return pg_last_error($this->cnx);
	}
}