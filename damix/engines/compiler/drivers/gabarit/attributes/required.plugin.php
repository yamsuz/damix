<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\attributes;

class gabaritattributerequired
    extends GabaritAttributeAll
{    
    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $nodes, \damix\engines\compiler\CompilerContentAttribute $attribute ) : bool
    {
        $content = array();
		
		switch( $nodes->name )
		{
			case 'label':
				
				$nodes->removeAttributes( $attribute->name );
								
				$nodes->text .= '<span class="form-required">'. \damix\engines\locales\Locale::get('damix~lclcore.form.label.required') .'</span>';
				break;
			default:
				$attr = $nodes->getAttr( 'required' );
				if( $nodes->hasAttribute( 'name' ) ) 
				{
					$content[] = '$this->_properties[\'' . $nodes->getAttrValue( 'name' ) . '\'][\'required\'] = ' . ( tobool( $attr->value ) ? 'true' : 'false' ) . ';';
				   
					$driver->content->appendFunction( 'propertyinit', array(), array( implode( "\n", $content) ), 'public');
					$driver->content->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
				}
		}
    
        
        return true;
    }
    
}