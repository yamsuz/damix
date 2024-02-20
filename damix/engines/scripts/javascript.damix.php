<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\scripts;


class Javascript
{
    public static array $_url = array();
    
    public static function link( string $selector )
    {
        if( ! isset( self::$_url[ $selector ] ) )
        {
            $sel = new JavascriptSelector( $selector );
            
            Javascript::create( $sel );
            self::$_url[ $selector ] = $sel->getUrlPath();
        }
    }
    
    public static function create( JavascriptSelector $selector )
    {
		JavascriptCompiler::compile( $selector );		
    }
    
    
    public static function getUrl() : array
    {
        $out = array();
        foreach( self::$_url as $url )
        {
            $out[] = $url;
        }
        
        return $out;
    }
	
    public static function addToResponse( \damix\core\response\ResponseBase $response ) : void
    {
        foreach( self::$_url as $url )
        {
            $response->addJSLink( $url );
        }
    }
    
}