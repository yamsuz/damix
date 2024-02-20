<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\template\drivers;

class TemplatesFunctionBouton
    extends \damix\engines\template\drivers\template\TemplateDriverFunction
    
{
    public function Execute( array|string $args ) : string
    {
		if( is_array( $args ) )
		{
			$sel = $args['selecteur'];
		}
		else
		{
			$sel = $args;
		}
		
		$obj = \damix\engines\boutons\Bouton::get( $sel );

		return $obj->getHtml();
	}
}