<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class GabaritElementString
    extends \damix\engines\compiler\GabaritElementAll
{
    protected $_autoClose = true;
    
    public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \DOMNode $child ) : void
    {
        if( ! $child->hasAttribute( 'type' ) )
        {
            $child->setAttribute( 'type', 'text' );
        }
        
        parent::beforeRead( $driver, $node, $child );
    }
    
    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node ) : void
    {
        $node->name = 'input';
        
        
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'form-control';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $node->appendAttributes( $attr );
        
        parent::read( $driver, $node );
    }
    
}