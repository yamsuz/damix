<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request;


class OrmRequestUpdate
	extends OrmRequest
{
	protected \damix\engines\orm\request\structure\OrmRow $row;
	protected \damix\engines\orm\conditions\OrmCondition $conditions;
	
	
	public function __construct()
	{
		$this->sqltype = \damix\engines\orm\request\structure\OrmStructureType::SQL_UPDATE;
		$this->structure = new \damix\engines\orm\request\structure\OrmStructure();
		$this->row = new \damix\engines\orm\request\structure\OrmRow();
		$this->conditions = new \damix\engines\orm\conditions\OrmCondition();
	}
		
	public function getConditions() : \damix\engines\orm\conditions\OrmCondition
	{
		return $this->conditions;
	}
	
	public function getRow() : \damix\engines\orm\request\structure\OrmRow
	{
		return $this->row;
	}
	
	public function addValue(string $field, mixed $value, \damix\engines\orm\request\structure\OrmDataType $datatype, bool $isPrimaryKey) : void
	{
		$this->row->addValue($field, $value, $datatype, $isPrimaryKey);
	}
}