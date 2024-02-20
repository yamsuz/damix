<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class gabaritelementtab
    extends \damix\engines\compiler\GabaritElementAll
{
	public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \DOMNode $child ) : void
    {
		$id = $node->getAttr( 'name' );
		$id->name = 'id';
	
		$node->name = 'div';
		
		
		$attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'xform_tabs';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $node->appendAttributes( $attr );
		
		$node->removeAttributes( 'locale' );
		
	}
	
}