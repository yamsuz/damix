<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

if( !function_exists( 'tobool' ) )
{
    function tobool( $bool )
    {
        if( is_scalar( $bool ) || is_null( $bool ) )
        {
            if( $bool === true || $bool === 1 ) { return true; }
            if( $bool === false || $bool === 0 || $bool === null || $bool === '' ) { return false; }
            if( in_array( strtolower( (string)$bool ) , array( 'yes', 'on', 'true', '1' ) ) ) { return true; }
            if( in_array( strtolower( (string)$bool ) , array( 'no', 'off', 'false', '0' ) ) ) { return false; }
        }
        return false;
    }
}

require( __DIR__ . DIRECTORY_SEPARATOR . 'selector.damix.php' );


class SelectorCore
	extends \damix\core\Selector
{
}

class includes
{
    public static function inc( $selector )
    {
        $sel = new SelectorCore( $selector );
        $filename = $sel->getPath();
        
        if( is_readable( $filename ) )
        {
            require_once( $filename );
        }
    }
}

class c extends includes{}


c::inc( 'tools~xfile' );
c::inc( 'tools~xdate' );
c::inc( 'tools~xtools' );

c::inc( 'xcompiler~xcompilergenerator' );
c::inc( 'xcompiler~xcompilerselector' );

c::inc( 'orm~ormselector' );
c::inc( 'orm~orm' );

