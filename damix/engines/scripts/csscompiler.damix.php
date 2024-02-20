<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\scripts;

class CssCompiler
{   
    public static function compile( CssSelector $selector )
    {
        $compile = false;
        $temp = $selector->getTempPath();
        $path = $selector->getPath();
 
        if( file_exists( $temp ) )
        {
            if( filemtime( $path ) > filemtime( $temp ) )
            {
                $compile = true;
            }
        
        }
        else
        {
            $compile = true;
        }
        
		if( $compile )
		{
            $xcssgenerator = new \damix\engines\scripts\CssGenerator();
            $xcssgenerator->generate( $selector );
        }
    }
}