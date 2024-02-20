<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmJointureCondition
{
    protected \damix\engines\orm\request\structure\OrmColumnType $datatype;
    protected string $reftable;
    protected string $reffield;
    protected \damix\engines\orm\conditions\OrmOperator $operator;
    protected string $withtable;
    protected string $withfield;
    protected OrmValue $value;
    
    public function addField( string $reftable, string $reffield, \damix\engines\orm\conditions\OrmOperator $operator, string $withtable, string $withfield ) : void
    {
        $this->datatype = \damix\engines\orm\request\structure\OrmColumnType::COLUMN_FIELD;
        $this->reftable = $reftable;
        $this->reffield = $reffield;
        $this->operator = $operator;
        $this->withtable = $withtable;
        $this->withfield = $withfield;
    }
    
    public function addString( string $reftable, string $reffield, \damix\engines\orm\conditions\OrmOperator $operator, OrmValue $value ) : void
    {
        $this->datatype = \damix\engines\orm\request\structure\OrmColumnType::COLUMN_RAW;
        $this->reftable = $reftable;
        $this->reffield = $reffield;
        $this->operator = $operator;
        $this->value = $value;
    }
	
	public function getDataType() : \damix\engines\orm\request\structure\OrmColumnType
	{
		return $this->datatype;
	}
	
	public function getOperator() : \damix\engines\orm\conditions\OrmOperator
	{
		return $this->operator;
	}
	
	public function getRefTable() : string
	{
		return $this->reftable;
	}
	
	public function getRefField() : string
	{
		return $this->reffield;
	}
	
	public function getWithTable() : string
	{
		return $this->withtable;
	}
	
	public function getWithField() : string
	{
		return $this->withfield;
	}
	
	public function getValue() : OrmValue
	{
		return $this->value;
	}
}