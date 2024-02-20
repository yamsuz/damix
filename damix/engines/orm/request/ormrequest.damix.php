<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request;


class OrmRequest
{
	protected \damix\engines\orm\request\structure\OrmStructureType $sqltype;
	protected \damix\engines\orm\request\structure\OrmStructure $structure;
	protected ?\damix\engines\orm\request\structure\OrmSchema $schema = null;
	protected \damix\engines\orm\request\structure\OrmTable $table;
	
    public static function createTable() : \damix\engines\orm\request\structure\OrmTable
    {
        return new \damix\engines\orm\request\structure\OrmTable();        
    }
	
    public static function createSelect() : \damix\engines\orm\request\OrmRequestSelect
    {
        return new \damix\engines\orm\request\OrmRequestSelect();        
    }
	
	public function getSQLType() : \damix\engines\orm\request\structure\OrmStructureType
	{
		return $this->sqltype;
	}
	
	public function setSchema(string|\damix\engines\orm\request\structure\OrmSchema $schema) : void
	{
		if( is_string( $schema ) )
		{
			if( ! empty( $schema ) )
			{
				$this->schema = \damix\engines\orm\request\structure\OrmSchema::newSchema($schema);
			}
		}
		else
		{
			$this->schema = $schema;
		}
	}
	
	public function getSchema() : \damix\engines\orm\request\structure\OrmSchema
	{
		return $this->schema;
	}
	
	public function setTable(string|\damix\engines\orm\request\structure\OrmTable $table) : void
	{
		if( is_string( $table ) )
		{
			$this->table = \damix\engines\orm\request\structure\OrmTable::newTable($table);
		}
		else
		{
			$this->table = $table;
		}
		
		if( $this->schema )
		{
			$this->schema->addTable( $this->table );
		}
	}
	
	public function getTable() : \damix\engines\orm\request\structure\OrmTable
	{
		return $this->table;
	}
	public function query() : string
    {
        return $this->structure->query( $this );
    }
	
	public function executeNonQuery() : int|string
    {
        return $this->structure->executeNonQuery( $this );
    }
	
	public function execute() : ?\damix\engines\databases\DbResultSet
    {
        return $this->structure->execute( $this );
    }
	
	public function getSQL() : string
    {
        return $this->structure->getSQL( $this );
    }
	
	public function SchemaBase() : ?\damix\engines\databases\DbResultSet
    {
        return $this->structure->SchemaBase();
    }
	
	public function SchemaTable() : ?\damix\engines\databases\DbResultSet
    {
        return $this->structure->SchemaTable( $this->table );
    }
	
	public function SchemaIndex() : ?\damix\engines\databases\DbResultSet
    {
        return $this->structure->SchemaIndex( $this->table );
    }
	
	public function SchemaForeignKey() : ?\damix\engines\databases\DbResultSet
    {
        return $this->structure->SchemaForeignKey( $this->table );
    }
	
	public function SchemaTrigger() : ?\damix\engines\databases\DbResultSet
    {
        return $this->structure->SchemaTrigger( $this->table );
    }
	
	public function SchemaColonne() : ?\damix\engines\databases\DbResultSet
    {
        return $this->structure->SchemaColonne( $this->table );
    }
	
	public function SchemaStored() : ?\damix\engines\databases\DbResultSet
    {
        return $this->structure->SchemaStored();
    }
	
	public function SchemaEvent() : ?\damix\engines\databases\DbResultSet
    {
        return $this->structure->SchemaEvent();
    }
	
	public function SchemaStoredParameter(string $storedname) : ?\damix\engines\databases\DbResultSet
    {
        return $this->structure->SchemaStoredParameter($storedname);
    }
	
	public function lastInsertId() : int
    {
		$driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();

        return $driver->_cnx->lastInsertId();
    }
	
	 public function getPattern( $name )
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriverPatterns( $name );

        return $driver->execute();
    }
	
	
}