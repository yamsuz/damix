<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\scripts;


class JavascriptCompiler
{   
    public static function compile( JavascriptSelector $selector ) : bool
    {
        $compile = false;
        $temp = $selector->getTempPath();
        $path = $selector->getPath();
        if( file_exists( $path ) )
        {
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
                $xjavascriptgenerator = new \damix\engines\scripts\JavascriptGenerator();
                $xjavascriptgenerator->generate( $selector );
            }
			return true;
        }
		
		return false;
    }
}