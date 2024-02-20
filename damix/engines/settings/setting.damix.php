<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\settings;


class Setting
{
    static protected array $_singleton=array();
    
    public static function get( string $selector ) : SettingBase
    {
        $sel = new SettingSelector( $selector );

		if(! isset(self::$_singleton[$selector])){
			self::$_singleton[$selector] = self::create( $sel );
		}
		return self::$_singleton[$selector];
    }
	
    public static function getValue( string $selector, string $section, string $name ) : ?string
    {
        $conf = self::get( $selector );
		return $conf->get( $section, $name );
    }

    public static function create( SettingSelector $selector ) : SettingBase
    {
        $classname = '\\' . $selector->getFullNamespace();
        
		if( ! class_exists($classname,false ) )
        {
			if( SettingCompiler::compile( $selector ) )
            {
                $temp = $selector->getTempPath();
                require_once( $temp );
            }
			else
			{
				throw new \Exception();
			}
		}
        
        $obj = new $classname();
        return $obj;
    }
	
	public static function clear() : void
	{
		self::$_singleton = array();
	}
}