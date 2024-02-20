<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\views\gabarits;


class Gabarit
{
    static protected $_singleton=array();
    
    public static function get( $selector, $params = array() )
    {
        $sel = new GabaritSelector( $selector, $params );

		if(! isset(Gabarit::$_singleton[$selector])){
			Gabarit::$_singleton[$selector] = Gabarit::create( $sel, $params );
		}
		return Gabarit::$_singleton[$selector];
    }

    public static function create( $selector, $params = array() )
    {
        if( is_string( $selector ) )
        {
            $selector = new GabaritSelector( $selector, $params );
        }
        
        $classname = '\\gabarit\\' . $selector->getClassName();
        
		if( ! class_exists($classname,false ) )
        {
			if( GabaritCompiler::compile( $selector, $params ) )
            {
                $temp = $selector->getTempPath();
                require_once( $temp );
            }
		}
        
        $obj = new $classname( $params );
        return $obj;
    }
	
	public static function clear( $selector, $params = array() ) : bool
    {
        $sel = new GabaritSelector( $selector, $params );
		$temp = $sel->getTempPath();
		return \damix\engines\tools\xFile::remove( $temp );
    }
}