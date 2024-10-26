<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\databases;


abstract class DbResultSet
   implements \Iterator
{
	protected ?object $_idResult;
	protected object|null|false $_currentRecord=false;
	protected int $_recordIndex=0;
	
	abstract protected function _free() : void;
	abstract protected function _rewind() : void;
	abstract protected function rowCount() : int|string;
	abstract protected function columnCount() : int;
	abstract public function columnNames() : array;
	protected abstract function _fetch() : object|null|false;
	
	public function __construct($result)
	{
		$this->_idResult = $result;
	}
	
	public function __destruct()
	{
		if($this->_idResult){
			$this->_free();
			$this->_idResult = null;
		}
	}
	
	public function current() : object|null|false
	{
		return $this->_currentRecord;
	}
	public function key() : mixed
	{
		return $this->_recordIndex;
	}
	public function next() : void
	{
		$this->_currentRecord=$this->fetch();
		if($this->_currentRecord)
			$this->_recordIndex++;
	}
	public function rewind() : void
	{
		$this->_rewind();
		$this->_recordIndex=0;
		$this->_currentRecord=$this->fetch();
	}
	
	public function valid() : bool
	{
		return($this->_currentRecord!=false);
	}
	
	public function fetch() : object|null|false
	{
		return $this->_fetch();
	}
	
	public function seek(int $value) : void
	{
		$this->_recordIndex=$value;
		$this->_currentRecord=$this->fetch();
	}
	
	public function fetchAll() : array
	{
		$result=array();
		while($res=$this->fetch()){
			$result[]=$res;
		}
		return $result;
	}
	
}