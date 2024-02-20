<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class GabaritElementDate
    extends \damix\engines\compiler\GabaritElementAll
{
    protected $_autoClose = true;
    
    public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \DOMNode $child ) : void
    {
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'type';
        $attr->value = 'text';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $node->appendAttributes( $attr );
		
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'form-control datepicker';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $node->appendAttributes( $attr );
		
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'autocomplete';
        $attr->value = 'off';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $node->appendAttributes( $attr );

        $node->name = 'input';
		
        parent::beforeRead( $driver, $node, $child );
    }
}