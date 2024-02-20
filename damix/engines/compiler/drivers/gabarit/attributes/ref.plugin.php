<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\attributes;

class GabaritAttributeRef
    extends GabaritAttributeAll
{
    public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \damix\engines\compiler\CompilerContentAttribute $attribute ) : bool
    {
        switch( $node->name )
        {
            case 'string':
				$this->addAttributes( 'placeholder', $driver, $node, $attribute );
                $this->addAttributes( 'title', $driver, $node, $attribute );
                break;
            case 'combo':
                $this->addAttributes( 'title', $driver, $node, $attribute );
                break;
            case 'label':
                $this->addAttributes( 'locale', $driver, $node, $attribute );
                break;
        }


        $ref = \damix\engines\orm\Orm::getDefine( $attribute->value );
        
        if( $ref !== null )
        {
			if( $node->hasAttribute( 'name' ) )
			{
				$node->plugin->ormStructure = $ref['orm'];
				$node->plugin->ormField = $ref['field'];
				if( $ref['field'] != null )
				{
					$content[] = '$this->_properties[\'' . $node->getAttrValue( 'name' ) . '\'][\'ref\'] = array( \'orm\' => ' . $driver->content->quote( $ref['orm']->selector->_selector ) . ', \'property\' => ' . $driver->content->quote( $ref['field']['name'] ) . ');';
				}
			   
				$driver->content->appendFunction( 'propertyinit', array(), array( implode( "\n", $content) ), 'public');
				$driver->content->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
			}
        }
        
        $node->removeAttributes( $attribute->name );
		return true;
    }   
}