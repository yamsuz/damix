<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request;


class OrmRequestCreate
	extends OrmRequest
{
    protected bool $ignore = false;

	public function __construct()
	{
		$this->sqltype = \damix\engines\orm\request\structure\OrmStructureType::SQL_CREATE;
		$this->structure = new \damix\engines\orm\request\structure\OrmTable();
	}
	
	public function setIgnore( bool $value ) : void
	{
		$this->ignore = $value;
	}
	
	public function getIgnore() : bool
	{
		return $this->ignore;
	}
	
}