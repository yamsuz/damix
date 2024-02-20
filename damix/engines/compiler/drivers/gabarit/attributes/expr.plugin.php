<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\attributes;

class gabaritattributeexpr
    extends GabaritAttributeAll
{    
    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \damix\engines\compiler\CompilerContentAttribute $attribute ) : bool
    {
        $content = array();
        
        
        $attr = $node->getAttr( 'expr' );
        if( $node->getAttr( 'name' ) ) 
        {
            $content[] = '$this->_properties[\'' . $node->getAttrValue( 'name' ) . '\'][\'expr\'] = \'' . $attr->value. '\';';
           
            $driver->content->appendFunction( 'propertyinit', array(), array( implode( "\n", $content) ), 'public');
            $driver->content->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
        }
    
        
        return true;
    }
    
}