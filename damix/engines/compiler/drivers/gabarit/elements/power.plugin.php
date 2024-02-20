<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class GabaritElementPower
    extends \damix\engines\compiler\GabaritElementAll
{
    protected $_autoClose = false;
    
    public function write( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void
    {
        $node->name = 'span';
        
        
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'kt-switch kt-switch--outline kt-switch--icon kt-switch--brand ';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $node->appendAttributes( $attr );
       
        
        $label = new \damix\engines\compiler\CompilerContentElement();
        $label->name = 'label';
        $label->plugin = $driver->getPluginElement( $label->name );
        $node->addChild( $label );
        
        
        $divtitle = new \damix\engines\compiler\CompilerContentElement();
        $divtitle->name = 'input';
        $divtitle->plugin = $driver->getPluginElement( $divtitle->name );
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'type';
        $attr->value = 'checkbox';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $attr->plugin->read( $driver, $label, $attr );
        $divtitle->appendAttributes( $attr );
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'name';
        $attr->value = $node->getAttrValue( 'name' );
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $attr->plugin->read( $driver, $label, $attr );
        $divtitle->appendAttributes( $attr ); $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'xktfrm_radio';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $attr->plugin->read( $driver, $label, $attr );
        $divtitle->appendAttributes( $attr );
        $label->addChild( $divtitle );

        if( $node->hasAttribute( 'title' ) )
        {
            $attr = new \damix\engines\compiler\CompilerContentAttribute();
            $attr->name = 'title';
            $attr->value = $node->getAttrValue( 'title' );
            $attr->plugin = $driver->getPluginAttribute( $attr->name );
            $attr->plugin->read( $driver, $label, $attr );
            $divtitle->appendAttributes( $attr );
        }
        
        
        $divtitle = new \damix\engines\compiler\CompilerContentElement();
        $divtitle->name = 'span';
        $divtitle->plugin = $driver->getPluginElement( $divtitle->name );
        $label->addChild( $divtitle );
        
        
        $node->removeAttributes( 'name' );
        
        parent::write( $driver, $node, $obj );
    }
    
}