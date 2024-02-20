<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\databases;


abstract class Db
{
	static public array $_singleton=array();
	public static ?\damix\engines\settings\SettingBase $profile = null;
	
	
	public static function getConnection( string $name = '' ) : DbConnection
	{
		if( empty( $name ) )
		{
			$name = self::getDriverDefault($name);
		}
		
		$driver = self::getDriverName( $name);
		
		if(! isset(self::$_singleton[$name])){
		
			$profile = self::$profile->getAllSection( $name );

			$selector = new DbSelector( $driver );
			self::$_singleton[$name] = self::create( $selector, $name );
		}
		
		return self::$_singleton[ $name ];
	}
	
	public static function create( DbSelector $selector, string $profile ) : DbConnection
	{
		$classname = $selector->getFullNamespace();
        
		if( ! class_exists($classname,false ) )
        {
			$temp = $selector->getPath();
			require_once( $temp );
		}
        
        $obj = new $classname( $profile );
		return $obj;
	}
	
	public static function getDriverDefault(string $name = '') : string
	{		
		if( !self::$profile ) 
		{
			self::$profile = \damix\engines\settings\Setting::get('profile');
		}
		if( empty( $name ) )
		{
			$name = self::$profile->get('database', 'default');
		}
		
		return $name;
	}
	
	public static function getDriverName(string $name = '') : string
	{
		if( !self::$profile ) 
		{
			self::$profile = \damix\engines\settings\Setting::get('profile');
		}
		if( empty( $name ) )
		{
			$name = self::getDriverDefault($name);
		}
		
		$profile = self::$profile->getAllSection( $name );
		
		return $profile['driver'];
	}
	
	public static function clear() : void
	{
		self::$profile = null;
		self::$_singleton = array();
	}
}