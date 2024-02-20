<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\stored;


class OrmStoredBase
{
    public $cnx = null;
    
    public function __construct()
    {
        $this->cnx = \damix\engines\databases\Db::getConnection();
    }
    
    protected function execute( string $schemaname, string $name, array $params )
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();
        
        $sql = $driver->getRequestProcedure( $schemaname, $name, $params );
        \damix\engines\logs\Log::log( $sql, 'sql' );
		return $this->cnx->executeNonQuery( $sql );
    }
    
    protected function query( string $schemaname, string $name, array $params, \damix\engines\orm\request\structure\OrmDataType $type )
    {
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();
        
        $sql = $driver->getRequestFunction( $schemaname, $name, $params, 'vdata' );
        \damix\engines\logs\Log::log( $sql, 'sql' );
        $out = $this->cnx->query( $sql );
        
        if( $info = $out->fetch() )
        {
        	return $driver->getData( $info->vdata, $type );
        }
        
        return null;
    }
}