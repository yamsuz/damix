<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\template\drivers;

class TemplatesFunctionIconfont
    extends \damix\engines\template\drivers\template\TemplateDriverCfunction
    
{
    public function Execute( string $args ) : string
    {
		eval( '$argument = array( ' . $args .' );');
        return 'print \damix\engines\iconfont\IconFont::getIcon( '. $this->quote($argument[0]) .' );';
    }
}