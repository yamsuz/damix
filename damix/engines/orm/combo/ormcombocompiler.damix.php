<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\combo;

class OrmComboCompiler
{
    public static function compile( $selector, $params )
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
            $ormcombogenerator = new OrmComboGenerator();
            $ormcombogenerator->generate( $selector, $params );
        }
        
    }

	public static function clear( $selector, $params )
    {
		$selector->getFiles();
		
		$ormcombogenerator = new OrmComboGenerator();
		$ormcombogenerator->clearTemp( $selector, $params );
    }
}