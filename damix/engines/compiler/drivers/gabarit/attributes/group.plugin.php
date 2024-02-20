<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\attributes;

class gabaritattributegroup
    extends GabaritAttributeAll
{    
    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \damix\engines\compiler\CompilerContentAttribute $attribute ) : bool
    {
        $attr = $node->getAttr( 'class' );
        
		$split = preg_split('/;/', $attribute->value);
		foreach( $split as $value)
		{
			if( $attr === null )
			{
				$attr = new \xcompiler\xcompilercontentattribute();
				$attr->name = 'class';
				$attr->value = 'xfrm_gp_' . $value;
				$attr->plugin = $driver->getPluginAttribute( $attr->name );
				$node->appendAttributes( $attr );
			}
			else
			{
				$attr->value .= ' xfrm_gp_' . $value;
			}
		}
        
        $node->removeAttributes( $attribute->name );
        return true;
    }
    
}