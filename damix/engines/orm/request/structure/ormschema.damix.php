<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmSchema
{
    protected string $name;
    protected string $realname;
    
    private array $tables = array();
	
	public function getTables() : array
	{
		return $this->tables;
	}
		
	public function addTable(OrmTable $table) : void
	{
		$this->tables[ $table->name ] = $table;
		$table->setSchema( $this );
	}
	
	public function getTable(string $name) : ?OrmTable
	{
		return $this->tables[ $name ] ?? null;
	}
	
	public function setName(string $value) : void
	{
		$this->name = $value;
	}
	public function getName() : string
	{
		return $this->name;
	}
	
	public function setRealname(string $value) : void
	{
		$this->realname = $value;
	}
	public function getRealname() : string
	{
		return $this->realname;
	}
	
	public static function newSchema(string $name, string $realname = '' ) : OrmSchema
	{
		$schema = new OrmSchema();
		$schema->name = $name;
		$schema->realname = (! empty($realname) ? $realname : $name);
		return $schema;
	}	
	
	
}