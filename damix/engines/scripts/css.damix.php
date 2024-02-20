<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\scripts;


class Css
{
    public static function link( $selector ) : string
    {
        $sel = new CssSelector( $selector );
		
		Css::create( $sel );
		
		return $sel->getUrlPath();
    }
    
    public static function create( $selector )
    {
		CssCompiler::compile( $selector );
    }
    
}