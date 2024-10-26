<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\urls;


class Url
{
    static protected $_singleton=array();
    
    public static function getPath( string $selector, array $params = array() ) : string
    {
        $url = self::get( $selector );
		return $url->toString($params);
    }
	
    public static function get( string $selector ) : UrlBase
    {
        $sel = new UrlSelector( $selector );

		if(! isset(self::$_singleton[$selector])){
			self::$_singleton[$selector] = self::create( $sel );
		}
		return self::$_singleton[$selector];
    }

    public static function create( UrlSelector $selector )
    {
        $classname = '\\damix\\core\\urls\\basic\\' . $selector->getClassName();
        
		if( ! class_exists($classname,false ) )
        {
			$temp = $selector->getPath();
			require_once( $temp );
		}
        
        $obj = new $classname();
		$obj->selector = $selector;
		$obj->parse(null);
        return $obj;
    }
	
	public static function getBasePath(bool $scriptname = false) : string
	{
		$c = \damix\engines\settings\Setting::get('default');
		$basepath = $c->get( 'url', 'basepath' );
		
		return ( !empty($basepath) ? '/' . $basepath : '' ) . '/' . ( $scriptname? $c->get( 'url', 'scriptname' ) : '' );
	}
}