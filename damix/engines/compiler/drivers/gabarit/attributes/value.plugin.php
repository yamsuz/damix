<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\attributes;

class gabaritattributevalue
    extends GabaritAttributeAll
{
	public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \damix\engines\compiler\CompilerContentAttribute $attribute ) : bool
    {
		if( ! empty($node->getAttrValue( 'name' )) )
		{
			$content[] = '$this->_properties[\'' . $node->getAttrValue( 'name' ) . '\'][\''. $attribute->name .'\'] = ' . $driver->content->quote( $node->getAttrValue( $attribute->name ) ) . ';';
			
			$driver->content->appendFunction( 'propertyinit', array(), array( implode( "\n", $content) ), 'public');
			$driver->content->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
		}
        
		return parent::beforeRead( $driver, $node, $attribute );
    }
	
}