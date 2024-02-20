<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request;


class OrmRequestDrop
	extends OrmRequest
{
    

	public function __construct()
	{
		$this->sqltype = \damix\engines\orm\request\structure\OrmStructureType::SQL_DROP;
		$this->structure = new \damix\engines\orm\request\structure\OrmTable();
	}
	
	
}