<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm;

class OrmCompiler
{
	public static function compileStucture( OrmStructureSelector $selector ) : bool
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
            $ormgeneratorstructure = new \damix\engines\orm\OrmGeneratorStructure();
            return $ormgeneratorstructure->generate( $selector );
        }
        
		return false;
    }
	
	public static function compile( OrmSelector $selector ) : bool
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
            $ormgenerator = new \damix\engines\orm\OrmGenerator();
            return $ormgenerator->generate( $selector );
        }
		
        return false;
    }
}