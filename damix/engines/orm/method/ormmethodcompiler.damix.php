<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\orm\method;

class OrmMethodCompiler
{
    public static function compile( \damix\engines\orm\method\OrmMethodSelector $selector )
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
            $xcompilergenerator = new OrmMethodGenerator();
            $xcompilergenerator->generate( $selector );
            
        }
        return true;
    }
    
    public static function compileExecute( \damix\engines\orm\method\OrmMethodSelector $selector )
    {
        $selector->getFiles();
        
        $compile = false;
        $temp = $selector->getTempPathExecute();
        
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
        // $compile = true;
        if( $compile )
        {
            $obj = \damix\engines\orm\method\OrmMethod::get( $selector->_selector );
            $factory = $selector->getFactory();
            $factory->setRequestBase( $obj->getRequest() );
			// \damix\engines\logs\log::dump( $obj );
            $xcompilergenerator = new OrmMethodGenerator();
            $xcompilergenerator->generateExecute( $selector );
        }
		
        return true;
    }
    
}