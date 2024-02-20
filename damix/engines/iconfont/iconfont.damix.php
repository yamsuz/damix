<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\iconfont;


class IconFont
{
    static protected $_singleton=null;
    
    public static function get()
    {
        $sel = new IconFontSelector();
 
		if( IconFont::$_singleton === null ){
			IconFont::$_singleton = IconFont::create( $sel );
		}
		return IconFont::$_singleton;
    }

    public static function getIcon( $name )
    {
        $icon = IconFont::get();
        return $icon->getHtml( $name );
    }

    public static function create( $selector = '' )
    {
        if( is_string( $selector ) )
        {
            $selector = new IconFontSelector();
        }
        
        $classname = '\\' . $selector->getFullNamespace();
        
		if( ! class_exists($classname,false ) )
        {
			if( IconFontCompiler::compile( $selector ) )
            {
                $temp = $selector->getTempPath();
                require_once( $temp );
            }
		}
        
        $obj = new $classname();
        return $obj;
    }
}