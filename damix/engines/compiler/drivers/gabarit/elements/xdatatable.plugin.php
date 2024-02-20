<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class gabaritelementxdatatable
    extends \damix\engines\compiler\GabaritElementAll
{
    public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \DOMNode $child ) : void
    {
		$selector =  $child->getAttribute( 'selector' );
		
        
        $obj = \datatable\Datatable::get( $selector );
        
        $node->name = 'div';
        
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'damix-dt__content-filtre-bouton';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $node->appendAttributes( $attr );
        
        
        $div1 = new \damix\engines\compiler\CompilerContentElement();
        $div1->name = 'div';
        $div1->plugin = $driver->getPluginElement( $div1->name );
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'kt-portlet';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $attr->plugin->read( $driver, $div1, $attr );
        $div1->appendAttributes( $attr );
        $node->addChild( $div1 );
        
        $div2 = new \damix\engines\compiler\CompilerContentElement();
        $div2->name = 'div';
        $div2->plugin = $driver->getPluginElement( $div2->name );
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'damix-dt__body';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $attr->plugin->read( $driver, $div2, $attr );
        $div2->appendAttributes( $attr );
        $div1->addChild( $div2 );
        
        $div3 = new \damix\engines\compiler\CompilerContentElement();
        $div3->name = 'div';
        $div3->plugin = $driver->getPluginElement( $div3->name );
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'id';
        $attr->value = $obj->getId();
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $attr->plugin->read( $driver, $div3, $attr );
        $div3->appendAttributes( $attr );
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'style';
        $attr->value = 'width:100%';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $attr->plugin->read( $driver, $div3, $attr );
        $div3->appendAttributes( $attr );
        $div2->addChild( $div3 );
        
        $table = new \damix\engines\compiler\CompilerContentElement();
        $table->name = 'table';
        $table->plugin = $driver->getPluginElement( $table->name );
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'display nowrap datatable';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $attr->plugin->read( $driver, $table, $attr );
        $table->appendAttributes( $attr );
        $div3->addChild( $table );
       
        
        $driver->content->appendFunction( 'initjavascript', array(), array( '$this->_javascript .= \''. preg_replace('/\'/', '\\\'', $obj->getJavascript()) .'\';' ) );
        // $driver->content->appendFunction( 'initjavascript', array(), array( '$this->_javascript .= \'xscreen.list_load( \\\'location~ctrmouvementliste:detail\\\' );\';' ) );
        
        
        $node->removeAttributes( 'locale' );
    }
    
}