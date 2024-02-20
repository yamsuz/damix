<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\combo;


class OrmCombo
{
    static protected $_singleton=array();
    
    public static function get( string $selector, array $params = array() )
    {
        $sel = new OrmComboSelector( $selector, $params );
        $hashcode = $sel->getHashCode();
		if(! isset(OrmCombo::$_singleton[$hashcode])){
			OrmCombo::$_singleton[$hashcode] = OrmCombo::create( $sel, $params );
		}
        
		return OrmCombo::$_singleton[$hashcode];
    }
    
    public static function create( OrmComboSelector $selector, array $params = array() )
    {
        if( is_string( $selector ) )
        {
            $selector = new OrmComboSelector( $selector );
        }
        
        $classname = '\\orm\\combo\\' . $selector->getClassName();
        
		if( ! class_exists($classname,false ) )
        {
			OrmComboCompiler::compile( $selector, $params );
            $temp = $selector->getTempPath();
            require_once( $temp );
		}
        
        $obj = new $classname();
        return $obj;
    }
	
	public static function clear( string $selector, array  $params = array() ) : void
    {
		$selector = new OrmComboSelector( $selector );
        
		OrmComboCompiler::clear( $selector, $params );
    }
	
	public static function getHtml( string $selector, string $name, string $placeholder = '', bool $multiple = false ) : string
    {
		$combo = \damix\engines\orm\combo\OrmCombo::get($selector, array());
		return $combo->getHtml($name, $placeholder, $multiple);
    }
	
	public static function addJSLink() : void
	{
		// \damix\engines\scripts\Javascript::link( 'theme~select2.full.min' );
		\damix\engines\scripts\Javascript::link( 'jquery~select2~select2.full.min' );
	}
    
}