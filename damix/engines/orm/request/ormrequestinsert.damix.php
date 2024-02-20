<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request;


class OrmRequestInsert
	extends OrmRequest
{
	protected array $rows = array();
	protected bool $ignore = false;
	
	public function __construct()
	{
		$this->sqltype = \damix\engines\orm\request\structure\OrmStructureType::SQL_INSERT;
		$this->structure = new \damix\engines\orm\request\structure\OrmStructure();
	}
	
	public function getRows() : array
	{
		return $this->rows;
	}
	
	public function newRow() : \damix\engines\orm\request\structure\OrmRow
	{
		return new \damix\engines\orm\request\structure\OrmRow();
	}
	
	public function addRow( \damix\engines\orm\request\structure\OrmRow $row) : void
	{
		$this->rows[] = $row;
	}
	
	public function setIgnore(bool $value) : void
	{
		$this->ignore = $value;
	}
	
	public function getIgnore() : bool
	{
		return $this->ignore;
	}
	
}