<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\stored;

class OrmStoredCompiler
{
    public static function compile( $selector )
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
            $ormstoredgenerator = new OrmStoredGenerator();
            $ormstoredgenerator->generate( $selector );
        }
    }
   
}