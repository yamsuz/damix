<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\classes;

class Classe
{
	static protected $_singleton=array();
    
    public static function get( string $selector ) : ?object
    {
		if(! isset(self::$_singleton[$selector])){
			$obj = self::create( $selector );
			if( $obj )
			{
				self::$_singleton[$selector] = $obj;
			}
			else
			{
				return null;
			}
		}
		return self::$_singleton[$selector];
    }
	
    public static function inc( string $selector ) : ?ClasseSelector
    {
		$sel = new ClasseSelector( $selector );

        $classname = $sel->getFullNamespace();
		
		if( ! class_exists($classname, false ) )
        {
			$temp = $sel->getPath();
			if( file_exists( $temp )) 
			{
				require_once( $temp );
			}
			else
			{
				return null;
			}
		}
		
		return $sel;
    }
	
	public static function create( string $selector ) : ?object
    {
		$sel = self::inc( $selector );
		if( $sel )
		{
			$classname = $sel->getFullNamespace();
			
			$obj = new $classname();
			return $obj;
		}
		
		return null;
    }
}