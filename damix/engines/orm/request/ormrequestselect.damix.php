<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request;


class OrmRequestSelect
	extends OrmRequest
{
	
	protected \damix\engines\orm\conditions\OrmCondition $conditions;
	protected \damix\engines\orm\conditions\OrmCondition $having;
	protected array $displays = array();
	protected array $joins = array();
	protected \damix\engines\orm\request\structure\OrmGroups $groupby;
	protected \damix\engines\orm\request\structure\OrmOrders $orderby;
	protected ?\damix\engines\orm\request\structure\OrmFrom $from = null;
	protected \damix\engines\orm\request\structure\OrmLimits $limits;
	
	
	public function __construct()
	{
		$this->sqltype = \damix\engines\orm\request\structure\OrmStructureType::SQL_SELECT;
		$this->groupby = new \damix\engines\orm\request\structure\OrmGroups();
		$this->orderby = new \damix\engines\orm\request\structure\OrmOrders();
		$this->conditions = new \damix\engines\orm\conditions\OrmCondition();
		$this->having = new \damix\engines\orm\conditions\OrmCondition();
		$this->structure = new \damix\engines\orm\request\structure\OrmStructure();
		$this->limits = new \damix\engines\orm\request\structure\OrmLimits();
	}
	
	public function getHaving() : \damix\engines\orm\conditions\OrmCondition
	{
		return $this->having;
	}
	
	public function getConditions() : \damix\engines\orm\conditions\OrmCondition
	{
		return $this->conditions;
	}
		
	public function addValue(string $field, mixed $value, \damix\engines\orm\request\structure\OrmDataType $datatype = \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR) : void
	{
		$this->row->addValue($field, $value, $datatype);
	}
	
	public function addDisplay(\damix\engines\orm\request\structure\OrmColumn $col)
	{
		$this->displays[] = $col;
	}
	
	public function getDisplay() : array
	{
		return $this->displays;
	}
	
	public function setFrom( \damix\engines\orm\request\structure\OrmFrom $from ) : void
    {
        $join = new \damix\engines\orm\request\structure\OrmJointure();
        $join->type = $type;
        $join->table = \damix\engines\orm\request\structure\OrmTable::newTable( $name );
        $join->alias = $alias != '' ? $alias : $name;
        $this->joins[] = $join;
    }
	
	public function getFrom() : ?\damix\engines\orm\request\structure\OrmFrom
    {
        return $this->from;
    }
	
	public function getJoins() : array
	{
		return $this->joins;
	}

	public function addJoin( string $type, \damix\engines\orm\request\structure\OrmTable $table, string $alias = '' ) : \damix\engines\orm\request\structure\OrmJointure
    {
        $join = new \damix\engines\orm\request\structure\OrmJointure();
        $join->type = $type;
        $join->table = $table;
        $join->alias = $alias != '' ? $alias : $name;
        $this->joins[] = $join;
        
        return $join;
    }

	public function addJoinSubrequestSelector( string $type, string $selector, string $function, string $alias ) : \damix\engines\orm\request\structure\OrmJointure
    {
        $join = new \damix\engines\orm\request\structure\OrmJointure();
        $join->type = $type;
        $join->setSelector( $selector );
        $join->setFunction( $function );
        $join->alias = $alias;
        $this->joins[] = $join;
        
        return $join;
    }
	
	public function addJoinSubrequest( string $type, \damix\engines\orm\request\OrmRequestSelect $subrequest, string $alias ) : \damix\engines\orm\request\structure\OrmJointure
    {
        $join = new \damix\engines\orm\request\structure\OrmJointure();
        $join->type = $type;
        $join->setSubrequest( $subrequest );
        $join->alias = $alias;
        $this->joins[] = $join;
        
        return $join;
    }
	
	public function getGroupBy() : \damix\engines\orm\request\structure\OrmGroups
	{
		return $this->groupby;
	}
	
	public function addGroupBy( \damix\engines\orm\request\structure\OrmGroup $col ) : void
    {
        $this->groupby->add( $col );
    }
	
	public function getOrderBy() : \damix\engines\orm\request\structure\OrmOrders
	{
		return $this->orderby;
	}
	
	public function addOrderBy( \damix\engines\orm\request\structure\OrmOrder $order ) : void
    {
        $this->orderby->add( $order );
    }
	
	public function getLimits() : \damix\engines\orm\request\structure\OrmLimits
	{
		return $this->limits;
	}
	
	public function setLimits( \damix\engines\orm\request\structure\OrmLimits $limits ) : void
    {
        $this->limits = $limits;
    }
}