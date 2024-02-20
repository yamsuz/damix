<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\drivers;


class OrmDrivers
{
	static protected $_singleton=array();
	static protected $_singletonpattern=array();

	public static function clear( string $selector = '' ) : void
	{
		self::$_singleton = array();
		self::$_singletonpattern = array();
	}
	
	public static function getDriver( string $selector = '' ) : OrmDriversBase
    {
		$driver = \damix\engines\databases\Db::getDriverName( $selector );
		
        $sel = new OrmDriversSelector($driver);
		
		$unique = $selector . $driver;
		
		if(! isset(self::$_singleton[$unique])){
			self::$_singleton[$unique] = self::create( $sel, $selector );
		}
		
		$obj = self::$_singleton[$unique] ?? null;
		
		return $obj;
        
    }
	
	private static function create( OrmDriversSelector $selector, string $drivername ) : OrmDriversBase
    {
        $classname = $selector->getFullNamespace();
		
		if( ! class_exists($classname,false ) )
        {
			$temp = $selector->getPath();
			require_once( $temp );
		}

		
		$driver = \damix\engines\databases\Db::getDriverDefault( $drivername );
		$obj = new $classname($driver);
		
        return $obj;
    }
	
	
	public static function getDriverPatterns( string $pattern, string $driver = '' ) : ?object
    {
        
		$driver = \damix\engines\databases\Db::getDriverName( $driver );
        
        
        $driver = strtolower( $driver );
        
        if(! isset(OrmDrivers::$_singletonpattern[ $driver ]))
        {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $driver . DIRECTORY_SEPARATOR . 'pattern';

			OrmDrivers::$_singletonpattern[$driver] = OrmDrivers::loadpattern( $dir );
		}
        
        $pattern = strtolower( $pattern );
		
        if( isset( OrmDrivers::$_singletonpattern[$driver][ $pattern ] ) )
        {
            $obj = OrmDrivers::$_singletonpattern[$driver][ $pattern ];
            if( ! $obj[ 'load' ] )
            {
                if( is_readable( $obj[ 'fullpath' ] ) )
                {
                    require_once( $obj[ 'fullpath' ] );
                    $classname = '\\damix\\engines\\orm\\drivers\\pattern\\OrmDriversPattern' . ucfirst( strtolower( $pattern ) );
                    $obj[ 'load' ] = $classname;
                }
            }
            $classname = $obj[ 'load' ];
            return new $classname();
        }
        
        return null;
    }
    
    
    private static function loadpattern( $dir )
    {
        $directories = scandir( $dir );
        $out = array();
        foreach( $directories as $elt )
        {
            if( $elt != '.' && $elt != '..' )
            {
                if( is_dir( $dir . DIRECTORY_SEPARATOR . $elt ) )
                {
                    $out = array_merge( $out, OrmDrivers::loadpattern( $dir . DIRECTORY_SEPARATOR . $elt ) );
                }
                else
                {
                    if( preg_match( '/^([a-zA-Z0-9]*)\.pattern\.php$/', $elt, $match ) )
                    {
                        $name = $match[1];
                        $out[ $name ] = array( 
                            'name' => $name, 
                            'load' => false, 
                            'fullpath' => $dir . DIRECTORY_SEPARATOR . $elt,
                            );
                    }
                }
            }
        }
        
        return $out;
    }
    
}