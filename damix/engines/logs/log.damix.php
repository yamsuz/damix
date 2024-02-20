<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\logs;


class Log
{
	static protected $_singleton=array();
	
	private static function get( string $selector, array $params = array() ) : LogBase
    {
        $sel = new LogSelector( $selector );

		if(! isset(self::$_singleton[$selector])){
			self::$_singleton[$selector] = self::create( $sel, $params );
		}
		$obj = self::$_singleton[$selector];
		
		return $obj;
    }

    private static function create( LogSelector $selector, array $params ) : LogBase
    {
        $classname = '\\damix\\engines\\logs\\' . $selector->getClassName();
		
		if( ! class_exists($classname,false ) )
        {
			$temp = $selector->getPath();
			require_once( $temp );
		}
        
        $obj = new $classname();
		$obj->selector = $selector;
        return $obj;
    }
	
    public static function log( mixed $message, string $category = 'default' )
	{
		$c = \damix\engines\settings\Setting::get('default');
		
		$driver = $c->get( 'log', 'driver' );
		
		$log = self::get( $driver );
		$log->setMessage( $message );
		$log->write( $category );
	}
	
    public static function dump( mixed $message, string $category = 'default' )
	{
		$c = \damix\engines\settings\Setting::get('default');
		
		$driver = $c->get( 'log', 'driver' );
		
		$log = self::get( $driver );
		$log->setMessageObject( $message );
		$log->write( $category );
	}
	
	
}