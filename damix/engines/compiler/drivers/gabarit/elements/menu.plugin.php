<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class gabaritelementmenu
    extends \damix\engines\compiler\GabaritElementAll
{
    public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \DOMNode $child ) : void
    {
        $node->name = 'li';
        
        
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'kt-menu__item';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $node->appendAttributes( $attr );
        
        
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'aria-haspopup';
        $attr->value = 'true';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $node->appendAttributes( $attr );
        
        
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
        $attr->value = 'kt-menu__link ';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $a->appendAttributes( $attr );
        $node->addChild( $a );
        
        
        $i = new \damix\engines\compiler\CompilerContentElement();
        $i->name = 'i';
        $i->plugin = $driver->getPluginElement( $i->name );
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'kt-menu__link-icon ' . $child->getAttribute( 'iconfont' );
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $i->appendAttributes( $attr );
        $a->addChild( $i );
        
        
        
        $span = new \damix\engines\compiler\CompilerContentElement();
        $span->name = 'span';
        $span->plugin = $driver->getPluginElement( $span->name );
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'kt-menu__link-text';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $span->appendAttributes( $attr );
        
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'locale';
        $attr->value = $child->getAttribute( 'locale' );
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $span->appendAttributes( $attr );
        $attr->plugin->read( $driver, $span, $attr );
        $a->addChild( $span );
        
        $node->removeAttributes( 'locale' );
        $node->removeAttributes( 'iconfont' );
        $node->removeAttributes( 'href' );
       
        return parent::read( $driver, $node );
    }
}