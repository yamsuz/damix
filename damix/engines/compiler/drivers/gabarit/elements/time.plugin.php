<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class gabaritelementtime
    extends \damix\engines\compiler\GabaritElementAll
{
    protected $_autoClose = true;
    
    public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \DOMNode $child ) : void
    {
        if( ! $child->hasAttribute( 'type' ) )
        {
            $child->setAttribute( 'type', 'text' );
        }
        
        if( $child->hasAttribute( 'class' ) )
        {
            $class = $child->getAttribute( 'class' );
            $class->value .= ' timepicker';
        }
        else
        {
            $child->setAttribute( 'class', 'timepicker' );
        }
             
        $node->name = 'input';
        
        parent::beforeRead( $driver, $node, $child );
    }
    
}