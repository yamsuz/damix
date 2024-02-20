<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\method;


class OrmMethod
{
    static protected $_singleton=array();
    
    public static function get( string $selector ) : \damix\engines\orm\OrmBaseProperties
    {
        $sel = new OrmMethodSelector( $selector );
		$hashcode = $sel->getHashCode();
		
		if(! isset(OrmMethod::$_singleton[$hashcode])){
			OrmMethod::$_singleton[$hashcode] = OrmMethod::create( $sel );
		}
		return OrmMethod::$_singleton[$hashcode];
    }
    
    public static function execute( string $selector, \damix\engines\orm\OrmBaseFactory $factory )
    {
        $sel = new OrmMethodSelector( $selector );
        $sel->setFactory( $factory );
		$hashcode = $sel->getHashCode() .'exec';
		if(! isset(OrmMethod::$_singleton[$hashcode])){
		
			OrmMethod::$_singleton[$hashcode] = OrmMethod::createExecute( $sel );
		}
		return OrmMethod::$_singleton[$hashcode];
    }
    
    private static function create( OrmMethodSelector $selector ) : \damix\engines\orm\OrmBaseProperties
    {

        $classname = '\\orm\\method\\' . $selector->getClassName();
        
		if( ! class_exists($classname,false ) )
        {
			if( OrmMethodCompiler::compile( $selector ) )
            {
                $temp = $selector->getTempPath();
                require_once( $temp );
            }
            else
            {
                return false;
            }
		}
        
        $obj = new $classname();
        return $obj;
    }
    
    private static function createExecute( OrmMethodSelector $selector )
    {        
        $classname = '\\orm\\method\\' . $selector->getExecuteClassName();
		if( ! class_exists($classname,false ) )
        {
			// \damix\engines\logs\log::dump( $selector );
			if( OrmMethodCompiler::compileExecute( $selector ) )
            {
                $temp = $selector->getTempPathExecute();
                require_once( $temp );
            }
            else
            {
                return false;
            }
		}
        
        $obj = new $classname();
		
        return $obj;
    }
}