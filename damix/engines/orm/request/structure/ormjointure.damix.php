<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmJointure
{
    public string $type;
    public \damix\engines\orm\request\structure\OrmTable $table;
    public \damix\engines\orm\request\OrmRequestSelect $subrequest;
    public string $alias;
    public string $selector;
    public string $function;
    public array $conditions = array();
    
    public function addConditionField( string $reftable, string $reffield, \damix\engines\orm\conditions\OrmOperator $operator, string $withtable, string $withfield ) : void
    {
        $c = new OrmJointureCondition();
        $c->addField( $reftable, $reffield, $operator, $withtable, $withfield );
        
        $this->conditions[] = array( 'type' => \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD, 'condition' => $c );
    }
	
    public function addConditionString( string $reftable, string $reffield, \damix\engines\orm\conditions\OrmOperator $operator, string $value ) : void
    {
        $c = new OrmJointureCondition();
		$v = new OrmValue();
		$v->setValue( $value );
        $c->addString( $reftable, $reffield, $operator, $v );
        
        $this->conditions[] = array( 'type' => \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD, 'condition' => $c );
    }
    
    public function addGroupBegin() : void
    {
        $this->conditions[] = array( 'type' => \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPBEGIN );
    }
    
    public function addLogic( \damix\engines\orm\conditions\OrmOperator $logic ) : void
    {
        $this->conditions[] = array( 'type' => \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_LOGIC, 'logic' => $logic );
    }
    
    public function addGroupEnd() : void
    {
        $this->conditions[] = array( 'type' => \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPEND );
    }
    
    public function clear() : void
    {
        $this->type = '';
        $this->conditions = array();
    }
	
	public function setTable(OrmTable $table) : void
	{
		$this->table = $table;
	}
	
	public function getTable() : ?OrmTable
	{
		return $this->table ?? null;
	}
	
	public function setAlias(string $alias) : void
	{
		$this->alias = $alias;
	}
	
	public function getAlias() : string
	{
		return $this->alias ?? '';
	}
	
	public function setSelector(string $selector) : void
	{
		$this->selector = $selector;
	}
	
	public function getSelector() : string
	{
		return $this->selector ?? '';
	}
	
	public function setSubrequest(\damix\engines\orm\request\OrmRequestSelect $subrequest) : void
	{
		$this->subrequest = $subrequest;
	}
	
	public function getSubrequest() : ?\damix\engines\orm\request\OrmRequestSelect
	{
		return $this->subrequest ?? null;
	}
	
	public function setFunction(string $function) : void
	{
		$this->function = $function;
	}
	
	public function getFunction() : string
	{
		return $this->function ?? '';
	}
}