<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\databases;


abstract class DbConnection
{	
	protected string $database;
	protected string $host;
	protected int $port;
	protected string $user;
	protected string $password;
	protected string $charset;
	protected bool $persistent;	
	protected ?object $cnx = null;
	protected string $lastQuery;
	protected string $driverName;
	protected bool $_autocommit;
	protected array $profile;
	protected ?\damix\engines\settings\SettingBase $setting = null;
	
	abstract protected function _connect() : bool;
	abstract protected function _disconnect() : bool;
	abstract protected function _doQuery(string $query)  : ?\damix\engines\databases\DbResultSet;
	abstract protected function _doExec(string $query) : int|string;
	abstract protected function rowCount() : int|string;
	abstract public function beginTransaction() : void;
	abstract public function commit() : void;
	abstract public function rollback() : void;
	abstract public function prepare(string $query):void;
	abstract public function execute():bool;
	abstract public function bindString(string $query):void;
	abstract public function bindInt(int $query):void;
	abstract public function bindDecimal(float $query):void;
	abstract public function errorMessage() : string;
	abstract public function errorCode() : string;
	abstract public function lastInsertId() : int;
	abstract protected function _autoCommitNotify(bool $state) : bool;	
	
	
	public function __construct( string $name = '' )
	{
		if( ! $this->setting )
		{
			$this->setting = \damix\engines\settings\Setting::get('profile');
		}
		
		$this->driverName = \damix\engines\databases\Db::getDriverDefault( $name );
		
		
		$this->setProfile();
	}
	
	public function getDriver() : string
	{
		return $this->profile['driver'];
	}
	
	public function getDriverName() : string
	{
		return $this->driverName;
	}
	
	public function getProfile() : array
	{
		return $this->profile;
	}
	
	public function getProfileValue(string $name) : string
	{
		return $this->profile[$name] ?? '';
	}
	
	public function setProfile() : void
	{
		$profile = $this->setting->getAllSection( $this->driverName );
		
		$this->database = $profile['database'] ?? '';
		$this->host = $profile['host'] ?? '';
		$this->port = intval($profile['port'] ?? 0);
		$this->user = $profile['user'] ?? '';
		$this->password = $profile['password'] ?? '';
		$this->charset = $profile['charset'] ?? '';
		$this->persistent = boolval( $profile['persistent']  ?? false);
		$this->profile = $profile ?? array();
		
		$this->_connect();
	}
	
	public function __destruct(){
		if($this->cnx){
			$this->_disconnect();
		}
	}
	
	public function getDatabase() : string
	{
		return $this->database;
	}
	
	public function query( string $sql ): ?\damix\engines\databases\DbResultSet
	{
		$this->lastQuery = $sql;
		return $this->_doQuery( $sql );
	}
	
	public function executeNonQuery( string $sql ) : int|string
	{
		$this->lastQuery = $sql;
		return $this->_doExec( $sql );
	}
	
	public function setAutoCommit(bool $state=true){
		$this->_autocommit=$state;
		$this->_autoCommitNotify($this->_autocommit);
	}
	
	public function quote(string $text, bool $checknull = true){
		if($checknull)
			return (is_null($text)? 'NULL' : "'" . $this->_quote( $text ) . "'");
		else
			return "'" . $this->_quote( $text ) . "'";
	}
	
	protected function _quote( string $text ){
		return addslashes($text);
	}
}