<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler;

class CompilerContentElement
{
    public $name;
    public $plugin;
    public $text;
    private $_nodes = array();
    private $_attributes = array();
    
    public function addChild( CompilerContentElement $node )
    {
        $this->_nodes[] = $node;
    }
	
    public function children() : array
    {
        return $this->_nodes;
    }
	
    public function firstChildren() : CompilerContentElement | null
    {
        return $this->_nodes[0] ?? null;
    }
  

    public function hasAttribute( string $name ) : bool
    {
        return isset( $this->_attributes[$name] );
    }
    
    public function addAttributes( CompilerContentAttribute $attr )
    {
        $this->_attributes[$attr->name] = $attr;
    }
    
    public function appendAttributes( CompilerContentAttribute $attr )
    {
        if( isset( $this->_attributes[$attr->name] ) )
        {
            $this->_attributes[$attr->name]->value .= ' ' . $attr->value;
        }
        else
        {
            $this->addAttributes( $attr );
        }
    }
    
    public function removeAttributes( string $name )
    {
        unset( $this->_attributes[ $name ] );
    }
    public function clearAttributes()
    {
        $this->_attributes = array();
    }  
    public function getAttr( string $name ) : CompilerContentAttribute | null
    {
        return $this->_attributes[$name] ?? null;
    }
    public function getAttributes() : array
    {
        return $this->_attributes;
    }
    public function getAttrValue( string $name, string $default = '' ) : string
    {
        return strval( $this->_attributes[$name]->value ?? $default );
    }
}