<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\response;


class Response
{
    static protected $_singleton=array();
    
    public static function get( string $selector ) : ResponseBase
    {
        $sel = new ResponseSelector( $selector );

		if(! isset(self::$_singleton[$selector])){
			self::$_singleton[$selector] = self::create( $sel );
		}
		return self::$_singleton[$selector];
    }

    public static function create( ResponseSelector $selector )
    {
        $classname = $selector->getClassName();
        
		if( ! class_exists($classname,false ) )
        {
			$temp = $selector->getFileDefault();
			
			require_once( $temp );
		}
        
        $obj = new $classname();
        return $obj;
    }
}