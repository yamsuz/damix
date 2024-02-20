<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class gabaritelementnavonglet
    extends \damix\engines\compiler\GabaritElementAll
{
    protected $_autoClose = true;
    
    
    public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \DOMNode $child ) : void
    {
        $node->name = 'li';
     
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'nav-item';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $node->appendAttributes( $attr );
        
        $name = $driver->selector->parameters['name'] ?? '';
        
        $a = new \damix\engines\compiler\CompilerContentElement();
        $a->name = 'a';
        $a->plugin = $driver->getPluginElement( $a->name );
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'href';
        $attr->value = $child->getAttribute( 'href' );
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $attr->plugin->read( $driver, $a, $attr );
        $a->appendAttributes( $attr );
        
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'nav-link ' . ( $name == $child->getAttribute('name') ? 'active' : '');
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $a->appendAttributes( $attr );

        $i = new \damix\engines\compiler\CompilerContentElement();
        $i->name = 'i';
        $i->plugin = $driver->getPluginElement( $i->name );
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'ox-onglet__link-icon ' . $child->getAttribute( 'iconfont' );
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $i->appendAttributes( $attr );
        $a->addChild( $i );
        
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'role';
        $attr->value = 'tab';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $a->appendAttributes( $attr );
        $node->addChild( $a );
        
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'locale';
        $attr->value = $child->getAttribute( 'locale' );
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $a->appendAttributes( $attr );
        $attr->plugin->read( $driver, $a, $attr );
        
        
        $node->removeAttributes( 'locale' );
        $node->removeAttributes( 'iconfont' );
        $node->removeAttributes( 'name' );
        $node->removeAttributes( 'href' );
        
        
        return parent::read( $driver, $node );
    }
    
    
}