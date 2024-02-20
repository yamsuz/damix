<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request;


class OrmRequestDelete
	extends OrmRequest
{
	protected \damix\engines\orm\conditions\OrmCondition $conditions;
	
	
	public function __construct()
	{
		$this->sqltype = \damix\engines\orm\request\structure\OrmStructureType::SQL_DELETE;
		$this->structure = new \damix\engines\orm\request\structure\OrmStructure();
		$this->conditions = new \damix\engines\orm\conditions\OrmCondition();
	}
		
	public function getConditions() : \damix\engines\orm\conditions\OrmCondition
	{
		return $this->conditions;
	}

}