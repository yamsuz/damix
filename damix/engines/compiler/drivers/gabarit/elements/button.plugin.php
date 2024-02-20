<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class GabaritElementButton
    extends \damix\engines\compiler\GabaritElementAll
{
	protected $_autoClose = true;
	
    public function write( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void
    {
        $node->name = 'span';
		
        $ref = $node->getAttrValue( 'button' );
        $onclick = $node->getAttrValue( 'onclick' );
        $class = $node->getAttrValue( 'class' );
        $couleur = $node->getAttrValue( 'couleur' );
        
		if( ! empty( $ref ) )
		{
			$icon = \damix\engines\iconfont\IconFont::get();
			$htmlicon = $icon->getHtml( $ref );
			$prop = $icon->getProperty( $ref );
			
			if( $couleur == '' )
			{
				$couleur = 'bleu1';
			}
			$couleur = 'btn-damix-' . $couleur;  
			
				 
			$i = new \damix\engines\compiler\CompilerContentElement();
			$i->name = 'i';
			$i->plugin = $driver->getPluginElement( $i->name );
			$node->addChild( $i );
			
			$attr = new \damix\engines\compiler\CompilerContentAttribute();
			$attr->name = 'class';
			$attr->value = 'btn '. $couleur . ' ' . $class .' damix-dt_btn-action xbutton_default icon-130-open-text-book';
			
			$attr->plugin = $driver->getPluginAttribute( $attr->name );
			$i->appendAttributes( $attr );
			
			$attr = new \damix\engines\compiler\CompilerContentAttribute();
			$attr->name = 'title';
			$attr->value = \damix\engines\locales\Locale::get( $prop['title'] );
			$attr->plugin = $driver->getPluginAttribute( $attr->name );
			$i->appendAttributes( $attr );
			
			
			$a = new \damix\engines\compiler\CompilerContentElement();
			$a->name = 'a';
			$a->text = $htmlicon;
			$a->plugin = $driver->getPluginElement( $a->name );
			if( $node->hasAttribute( 'label' ) )
			{
				if( $label != '' )
				{
					$label = $node->getAttrValue( 'label' );
					$a->text = \damix\engines\locales\Locale::get( $label );
				}
			}
			else
			{
				$a->text = \damix\engines\locales\Locale::get( $prop['label'] );
			}
			$i->addChild( $a );
			
			$attr = new \damix\engines\compiler\CompilerContentAttribute();
			$attr->name = 'class';
			$attr->value = $couleur . ' ' . $class;
			$attr->plugin = $driver->getPluginAttribute( $attr->name );
			$a->appendAttributes( $attr );
			
			$attr = new \damix\engines\compiler\CompilerContentAttribute();
			$attr->name = 'href';
			$attr->value = 'javascript:'. $onclick .';';
			$attr->plugin = $driver->getPluginAttribute( $attr->name );
			$a->appendAttributes( $attr );
		}
		$node->removeAttributes( 'name' );
		$node->removeAttributes( 'onclick' );
		$node->removeAttributes( 'button' );
       
        parent::write( $driver, $node, $obj );
    }
}