<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class GabaritElementTabs
    extends \damix\engines\compiler\GabaritElementAll
{
	public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \DOMNode $child ) : void
    {
		$id = $child->getAttribute( 'id' );
		$node->name = 'div';
		if( empty( $id ) )
		{
			$id = uniqid();
			$attr = new \damix\engines\compiler\CompilerContentAttribute();
			$attr->name = 'id';
			$attr->value = $id;
			$attr->plugin = $driver->getPluginAttribute( $attr->name );
			$node->appendAttributes( $attr );
		}
		
		$attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = 'class';
        $attr->value = 'xform_tabs';
        $attr->plugin = $driver->getPluginAttribute( $attr->name );
        $node->appendAttributes( $attr );
		
		$ul = new \damix\engines\compiler\CompilerContentElement();
		$ul->name = 'ul';
		$ul->plugin = $driver->getPluginElement( $ul->name );
		$node->addChild( $ul );
		
		foreach( $child->childNodes as $childnode)
		{
			$li = new \damix\engines\compiler\CompilerContentElement();
			$li->name = 'li';
			$li->plugin = $driver->getPluginElement( $li->name );
			$ul->addChild( $li );
			
			
			$a = new \damix\engines\compiler\CompilerContentElement();
			$a->name = 'a';
			$a->plugin = $driver->getPluginElement( $a->name );
			
			$attr = new \damix\engines\compiler\CompilerContentAttribute();
			$attr->name = 'href';
			$attr->value = '#' . $childnode->getAttribute( 'name' );
			$attr->plugin = $driver->getPluginAttribute( $attr->name );
			$a->appendAttributes( $attr );
			
			$attr = new \damix\engines\compiler\CompilerContentAttribute();
			$attr->name = 'locale';
			$attr->value = $childnode->getAttribute( 'locale' );
			$attr->plugin = $driver->getPluginAttribute( $attr->name );
			$a->appendAttributes( $attr );
			$attr->plugin->read($driver, $a, $attr );
			$li->addChild( $a );
			
		}
		
		$js = '\'popup.bind( \\\''. $driver->selector->_selector .'\\\', \\\'loaded\\\', function(){$( \\\'#'. $id .'\\\' ).tabs();});\'';
		$driver->content->appendFunction( 'initjavascript', array(), array( '$this->_javascript .= ' . $js . ';' ) );
	}
	
}