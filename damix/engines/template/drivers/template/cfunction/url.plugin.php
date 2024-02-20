<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\template\drivers;

class TemplatesFunctionUrl
    extends \damix\engines\template\drivers\template\TemplateDriverCfunction
    
{

	protected string $classname = '\damix\core\urls\Url::getPath';
   
}