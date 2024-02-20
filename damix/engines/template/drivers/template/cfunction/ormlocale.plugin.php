<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\template\drivers;

class TemplatesFunctionOrmlocale
    extends \damix\engines\template\drivers\template\TemplateDriverCfunction
    
{
    public function Execute( string $args ) : string
    {
		eval( '$argument = ' . $args .';');
				
		$define = \damix\engines\orm\Orm::getDefine( $argument );
		
		if( $define )
		{
			$argument = $define['orm']->selector->_selector . ':' . $define['field']['name'];
		}
		$ormstructure = \damix\engines\orm\Orm::getStructure( $argument );
		
		$name = $ormstructure->selector->getPart('function');
		$property = $ormstructure->getProperty( $name );
		
        return 'print \damix\engines\locales\Locale::get(\'' . $property['locale'] . '\');';
    }
}