<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\locales;

class LocaleCompiler
{
    public static function compile( LocaleSelector $selector ) : bool
    {
        $selector->getFiles();
        
        
        $compile = false;
        $temp = $selector->getTempPath();
        
        if( file_exists( $temp ) )
        {
            $compiledate = filemtime( $temp );
            foreach( $selector->files as $files )
            {
                if( filemtime( $files[ 'filename' ] ) > $compiledate )
                {
                    $compile = true;
                }
            }
        }
        else
        {
            $compile = true;
        }
        
        if( $compile )
        {
            $localegenerator = new \damix\engines\locales\LocaleGenerator();
            return $localegenerator->generate( $selector );
        }
        
        return true;
    }
}