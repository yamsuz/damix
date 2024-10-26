<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\template\drivers;

class TemplatesFunctionBasepath
    extends \damix\engines\template\drivers\template\TemplateDriverCfunction
    
{
    public function Execute( string $args ) : string
    {
		
        return 'print \damix\core\urls\Url::getBasePath(false);';
    }
}