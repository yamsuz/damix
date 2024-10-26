<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmStructure
{
	public function execute(\damix\engines\orm\request\OrmRequest $request, string $profile = '') : ?\damix\engines\databases\DbResultSet
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver($profile);

        return $driver->execute( $request );
    }
	
	public function executeNonQuery(\damix\engines\orm\request\OrmRequest $request, string $profile = '') : int|string
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver($profile);

        return $driver->executeNonQuery( $request );
    }
	
	public function getSQL(\damix\engines\orm\request\OrmRequest $request) : string
    {
		$driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();

        return $driver->getSQL( $request );
    }
	
	public function SchemaBase() : ?\damix\engines\databases\DbResultSet
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();
		$request = $driver->SchemaBase();
		
		return $driver->execute( $request );
    }
	
	public function SchemaTable(\damix\engines\orm\request\structure\OrmTable $table, string $profile = '') : ?\damix\engines\databases\DbResultSet
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver($profile);
		$request = $driver->SchemaTable($table);
		
		return $driver->execute( $request );
    }
	
	public function SchemaIndex(\damix\engines\orm\request\structure\OrmTable $table) : ?\damix\engines\databases\DbResultSet
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();
		$request = $driver->SchemaIndex($table);
		
		return $driver->execute( $request );
    }
	
	public function SchemaForeignKey(\damix\engines\orm\request\structure\OrmTable $table) : ?\damix\engines\databases\DbResultSet
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();
		$request = $driver->SchemaForeignKey($table);
		
		return $driver->execute( $request );
    }
	
	public function SchemaTrigger(\damix\engines\orm\request\structure\OrmTable $table) : ?\damix\engines\databases\DbResultSet
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();
		$request = $driver->SchemaTrigger($table);
		
		return $driver->execute( $request );
    }
	
	public function SchemaStored() : ?\damix\engines\databases\DbResultSet
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();
		$request = $driver->SchemaStored();
		
		return $driver->execute( $request );
    }
	
	public function SchemaEvent() : ?\damix\engines\databases\DbResultSet
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();
		$request = $driver->SchemaEvent();
		
		return $driver->execute( $request );
    }
	
	public function SchemaStoredParameter(string $storedname) : ?\damix\engines\databases\DbResultSet
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();
		$request = $driver->SchemaStoredParameter($storedname);
		
		return $driver->execute( $request );
    }
	
	public function SchemaColonne(\damix\engines\orm\request\structure\OrmTable $table, string $profile = '') : ?\damix\engines\databases\DbResultSet
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver($profile);
		$request = $driver->SchemaColonne($table);
		
		return $driver->execute( $request );
    }
}