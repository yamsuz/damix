<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\template\drivers;

class TemplatesFunctionLocalejs
    extends \damix\engines\template\drivers\template\TemplateDriverCfunction
    
{
	protected string $classname = '\damix\engines\locales\Locale::get';
	
	public function Execute( string $args ) : string
	{
        return 'print \'\\\'\' . addslashes(' . $this->classname .'( ' . $args . ')) . \'\\\'\';';
	}
	
}