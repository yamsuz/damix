<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\acls;


abstract class Acl
{
	static protected ?AclBase $_singleton = null;
	public static ?\damix\engines\settings\SettingBase $profile = null;
	
	
	public static function get() : AclBase
	{
		if(! self::$_singleton ){
			$driver = self::getDriverName( 'acl' );
			$selector = new AclSelector( $driver );
			self::$_singleton = self::create( $selector );
		}
		
		return self::$_singleton;
	}
	
	public static function create( AclSelector $selector ) : AclBase
	{
		$classname = $selector->getFullNamespace();
        
		if( ! class_exists($classname,false ) )
        {
			$temp = $selector->getPath();
			require_once( $temp );
		}
        
        $obj = new $classname();
		return $obj;
	}
	
	public static function getDriverDefault(string $name = '') : string
	{
		if( !self::$profile ) 
		{
			self::$profile = \damix\engines\settings\Setting::get('default');
		}
		if( empty( $name ) )
		{
			$name = self::$profile->get('acl', 'driver');
		}

		return $name;
	}
	
	public static function getDriverName(string $name = '') : string
	{
		if( !self::$profile ) 
		{
			self::$profile = \damix\engines\settings\Setting::get('default');
		}
		if( empty( $name ) )
		{
			$name = self::getDriverDefault($name);
		}
		
		$profile = self::$profile->getAllSection( $name );

		return $profile['driver'];
	}
	
	public static function check( string|array $subject ) : bool
	{
		if(! self::$_singleton )
		{
			self::get();
		}
		
		if( ! is_array( $subject ) )
		{
			$subject = array( $subject );
		}
		
		return self::$_singleton->check( $subject );
	}
	
	
}