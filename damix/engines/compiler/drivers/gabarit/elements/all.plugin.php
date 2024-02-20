<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler;

class GabaritElementAll
    extends \damix\engines\compiler\PluginElementDriver
{
    protected $_autoClose = false;
    public ?\damix\engines\orm\OrmBaseStructure $ormStructure;
    public ?array $ormField;
    
    public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \DOMNode $child ) : void
    {
        if( $child->nodeType == XML_ELEMENT_NODE )
        {
            if( $child->hasAttribute( 'name' ) && ! $child->hasAttribute( 'id' ))
            {
                $child->setAttribute( 'id', uniqid() );
            }
        }
    }
    
    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node ) : void
    {
        $content = array();
        
        if( boolval( $node->getAttrValue( 'includejs', 'true' ) ) )
        {
			if( $node->hasAttribute( 'name' ) )
			{
				if( $attr = $node->getAttr( 'name' ) )
				{
					$content[] = '$this->_properties[\'' . $attr->value . '\'][\'name\'] = \'' . $attr->value . '\';';
					
					if( $id = $node->getAttr( 'id' ) )
					{
						$content[] = '$this->_properties[\'' . $attr->value . '\'][\'id\'] = \'' . $id->value . '\';';
					}
				
					if( count( $content ) > 0 )
					{
						$driver->content->appendFunction( 'propertyinit', array(), array( implode( "\n", $content) ), 'public');
						$driver->content->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
					}
				}
			}
        }
        
    }
    
    public function write( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void
    {
        if( $node->name == '' )
        {
            return;
        }
        
        $driver->content->addData( '_html', '<' .$node->name );
    }
       
    public function afterwriteAttribute( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void
    {
        if( $node->name == '' )
        {
            return;
        }
    
        if( $this->_autoClose )
        {
            $driver->content->addData( '_html', '/>' );
        }
        else
        {
            $driver->content->addData( '_html', '>' );
        }
    }
    
    public function afterWrite( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void
    {
        if( $node->text != '' )
        {
            $driver->content->addData( '_html', $node->text);
        }
        
        if( $node->name == '' ) 
        {
            return;
        }
        
    
        if( ! $this->_autoClose )
        {
            $driver->content->addData( '_html', '</' .$node->name . '>' );
        }
    }
    
}