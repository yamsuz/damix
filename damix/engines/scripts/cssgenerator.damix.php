<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\scripts;


class CssGenerator
    extends \damix\engines\tools\GeneratorContent
{   
    public static function generate( $selector )
    {
        $temp = $selector->getTempPath();
        $path = $selector->getPath();
        
        
        $content = '';
        if( ob_start() )
        {
            require( $path );
            $content = ob_get_contents();
            ob_end_clean();
        }
        
        \damix\engines\tools\xfile::write( $temp, $content );
        
        return true;
    }
    
}