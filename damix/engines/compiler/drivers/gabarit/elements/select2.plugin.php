<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class GabaritElementSelect2
    extends \damix\engines\compiler\GabaritElementAll
{
    protected $_autoClose = false;

    // public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \DOMNode $child ) : void
    // {
        // if( ! $child->hasAttribute( 'selectmultiple' ) )
		// {
            // $attr = new \damix\engines\compiler\CompilerContentAttribute();
            // $attr->name = 'class';
            // $attr->value = 'form-control m-select2 select2_simple xform_popup';
            // $attr->plugin = $driver->getPluginAttribute( $attr->name );
            // $node->appendAttributes( $attr );
            // $node->name = 'select';
        // }
		// else
		// {
            // $attr = new \damix\engines\compiler\CompilerContentAttribute();
            // $attr->name = 'class';
            // $attr->value = 'form-control m-select2 select2_multiple xform_popup';
            // $attr->plugin = $driver->getPluginAttribute( $attr->name );
            // $node->appendAttributes( $attr );

            // $attr = new \damix\engines\compiler\CompilerContentAttribute();
            // $attr->name = 'multiple';
            // $attr->plugin = $driver->getPluginAttribute( $attr->name );
            // $node->appendAttributes( $attr );
			
			// $attr = new \damix\engines\compiler\CompilerContentAttribute();
            // $attr->name = 'selectmultiplemax';
            // $attr->value = $child->getAttribute( 'selectmultiple' );
            // $attr->plugin = $driver->getPluginAttribute( $attr->name );
            // $node->appendAttributes( $attr );

            // $node->name = 'select';
            // $node->removeAttributes( 'selectmultiple' );
        // }

        // parent::beforeRead( $driver, $node, $child );
    // }

    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node ) : void
    {
        // if( $node->hasAttribute( 'selector' ) )
		// {
            // $rowcount = $node->getAttrValue( 'rowcount' );
            // $selector = $node->getAttrValue( 'selector' );
            // $node->removeAttributes( 'selector' );

            // $attributes = $node->getAttributes();

            // $params = array();
            // foreach( $attributes as $attr ) 
			// {
                // $params[ $attr->name ] = $attr->value;
            // }

            // $param = array();
            // if( $rowcount > 0 )
            // {
                // $param[ 'limit' ] = array( 'rowcount' => $rowcount ) ;
            // }
            
            // $combo = \damix\engines\orm\combo\OrmCombo::get( $selector, $param );
			
            // $html = $combo->getInnerHtml( $params );
			
            // $node->name = 'select';
            // $node->text = $html;
        // }

		$driver->content->appendFunction( 'propertyinit', array(), array( '\damix\engines\orm\combo\OrmCombo::addJSLink();' ), 'public');

        parent::read($driver, $node);
    }

	public function write( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void
    {
        if( $node->name == '' )
        {
            return;
        }
		
		$selector = $node->getAttrValue( 'selector' );
		$name = $node->getAttrValue( 'name' );
        
        $driver->content->addData( '_html', '__QUOTE__ . \damix\engines\orm\combo\OrmCombo::getHtml( __QUOTE__' . $selector . '__QUOTE__, __QUOTE__' . $name . '__QUOTE__, ) . __QUOTE__');
		
		$node->clearAttributes();
    }
	
	public function afterwriteAttribute( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void
    {
       
    }
  
}