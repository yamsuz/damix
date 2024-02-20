<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\sessions;


class Session
{
    static protected $_singleton=array();
	
	private static function get( string $selector, array $params = array() ) : ?SessionBase
    {
        $sel = new SessionSelector( $selector );

		if(! isset(self::$_singleton[$selector])){
			self::$_singleton[$selector] = self::create( $sel, $params );
		}
		$obj = self::$_singleton[$selector];
		
		return $obj;
    }

    private static function create( SessionSelector $selector, array $params ) : ?SessionBase
    {
        $classname = $selector->getFullNamespace();
		
		if( ! class_exists($classname,false ) )
        {
			$temp = $selector->getPath();
			
			if( file_exists( $temp ) )
			{
				require_once( $temp );
			}
			else
			{
				return null;
			}
		}
        
        $obj = new $classname();
		
        return $obj;
    }
	
	public static function start() : bool
	{
		$driver = \damix\engines\settings\Setting::getValue('default', 'session', 'driver');
		$userdummy = \damix\engines\settings\Setting::getValue('default', 'auth', 'userdummy');
		if( $userdummy )
		{
			\damix\core\classes\Classe::inc( $userdummy );
		}
		if( ! empty( $driver ) )
		{
			$obj = Session::get( $driver );
			if( $obj )
			{
				session_set_save_handler($obj, true);
			}
			
			// $obj->gc(3600);
		}
		
		session_start();
		return true;
	}
	
	public static function end() : bool
	{
		session_write_close();
		return true;
	}
	
	public static function isStarted() :  bool
	{
		if(php_sapi_name()!=='cli'){
			return (session_status()===PHP_SESSION_ACTIVE);
		}
		return false;
	}
	
	
}