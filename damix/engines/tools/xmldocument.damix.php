<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\tools;

class xmlDocument 
	extends \DOMDocument
{
	public function __construct()
	{
		parent::__construct('1.0', 'UTF-8');
	}
	
    public function load( $filename, $preserveWhiteSpace = false) : DOMDocument|bool
    {
        if( is_readable( $filename ) )
        {
            $this->preserveWhiteSpace = $preserveWhiteSpace;
            return parent::load( $filename );
        }
        
        return false;
    }
    
	public function save(string $filename, int $options = 0): int|false
	{
		$this->saveDocument( $filename );
		return filesize( $filename );
	}
	
    public function saveDocument( string $filename, bool $formatOutput = true )
    {
        $path = pathinfo( $filename );

        if( !is_dir( $path[ 'dirname' ] ) )
        {
            xFile::createDir( $path[ 'dirname' ] );
        }
        
        $this->formatOutput = $formatOutput;
        $xml = $this->saveXML();
        
        xFile::write( $filename, $xml );
    }
    
    public function getAttribute( \DOMNode $node, string $attr, string $default = '' ) : string
    {
		if( $node->nodeType == XML_TEXT_NODE  )
		{
			return $default;
		}
		
        if( $node->hasAttribute( $attr ) )
        {
            return $node->getAttribute( $attr );
        }
        
        return $default;
    }
    
    public function hasAttribute( \DOMNode $node, string $attr ) : bool
    {
        return $node->hasAttribute( $attr );
    }
    
    public function setAttribute( \DOMNode $node, string $name, string $value ) : \DOMNode
    {
        return $node->setAttribute( $name, $value );
    }
    
    public static function createDocument( string $root = null ) : xmlDocument
    {
        $xml = new xmlDocument();
		if( $root !== null )
		{
			$elt = $xml->createElement($root);
			$xml->appendChild( $elt );
		}
        
        return $xml;
    }
    
    public function xPath( $requete, $node = null ) : \DOMNodeList
    {
        $xpath = new \DOMXpath( $this );
        if( $node )
            return $xpath->query( $requete, $node, false );
        else
            return $xpath->query( $requete );
    }
    
    public function addElement( string $name, \DOMNode $parent = null, array $attributes = array(), string $text = '' ) : \DOMNode
    {
        $element = parent::createElement( $name );
        if( is_array( $attributes ) )
        {
            foreach( $attributes as $attr_name => $attr_value )
            {
                $element->setAttribute( $attr_name, $attr_value );
            }
        }
        if( is_scalar( $text ) && $text != '' )
        {
            $element->appendChild( $this->createTextNode( $text ) );
        }
        if( $parent !== null )
        {
            $parent->appendChild( $element );
        }
        else
        {
            $this->appendChild( $element );
        }
        return $element;
    
    }
    
    public function removeElement( \DOMNode | \DOMNodeList | array $elements, \DOMNode $parent = null ) : array
    {
		$list = array();
        if( method_exists( $parent, 'removeChild' ) )
        {
            if( is_array( $elements ) )
            {
                foreach( $elements as $i => $element )
                {
					$list[] = $element;
                    $this->removeElement( $element, $parent );
                }
            }
            elseif( $elements instanceof \DOMNodeList )
            {
                foreach( $elements as $i => $element )
                {
                    $list[] = $element;
                }
                
                $this->removeElement( $list, $parent );
            }
            else
            {
				$list[] = $elements;
                $parent->removeChild( $elements );
            }
        }
		
		return $list;
    }
}