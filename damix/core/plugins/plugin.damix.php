<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\plugins;

class Plugin
{
	static protected $_singleton=array();
    
    public static function get( string $selector ) : ?PluginBase
    {
        $sel = new PluginSelector( $selector );
		if(! isset(self::$_singleton[$selector])){
			$obj = self::create( $sel );
			if( ! $obj )
			{
				return null;
			}
			self::$_singleton[$selector] = $obj;
		}
		return self::$_singleton[$selector];
    }

    public static function create( PluginSelector $selector )  : ?PluginBase
    {
        $classname = $selector->getFullNamespace();
        
		if( ! class_exists($classname,false ) )
        {
			$temp = $selector->getPath();
			if( file_exists ( $temp ) )
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
}