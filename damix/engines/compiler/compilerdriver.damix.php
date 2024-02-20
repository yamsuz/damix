<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler;

class CompilerDriver
{
    public CompilerContentTemp $content;
    public string $classname;
    public string $_driver;
    public array $_tags = array();
    public array $_attr = array();
    public array $_func = array();
    public array $_cfunc = array();
    public \damix\engines\tools\Language $_language;
    public \damix\core\Selector $selector;
    public CompilerDriver $parentDriver;
    
    protected ?string $_defaultelement = null;
    protected ?string $_defaultattribute = null;
    protected ?string $_defaultfunction = null;
    
    public function __construct()
    {
        $this->_language = new \damix\engines\tools\Language();
        $this->content = new CompilerContentTemp();
    }
    
    public function loaddefaultplugin()
    {
        if( $this->_defaultelement && isset( $this->_tags[ $this->_defaultelement ] ) )
        {
            require_once( $this->_tags[ $this->_defaultelement ][ 'fullpath' ] );
        }
        if( $this->_defaultattribute && isset( $this->_attr[ $this->_defaultattribute ] ) )
        {
            require_once( $this->_attr[ $this->_defaultattribute ][ 'fullpath' ] );
        }
    }
    
    public function read( $xnode, $dom )
    {
        if( $dom->childNodes )
        {
            foreach( $dom->childNodes as $child )
            {
                $cancel = false;
                $xcontent = new CompilerContentElement();
                
                if( $child->nodeType == XML_TEXT_NODE  )
                {
					$this->parseValueFunction( $child );
					
                    $xcontent->text = $child->nodeValue;
                    $xcontent->name = '';
                }
                else
                {
                    $xcontent->name = $child->nodeName;
                }
                
                $element = $this->getPluginElement( $child->nodeName );
                    
                if( $element )
                {
                    $xcontent->plugin = $element;
                }
                
                if( $child->hasAttributes() )
                {
                    foreach( $child->attributes as $attributes )
                    {
                        $compilercontentattribute = CompilerContentAttribute::getAttribute( $attributes );
                    
                        $xcontent->addAttributes( $compilercontentattribute );
                        
                        $attr = $this->getPluginAttribute( $attributes->nodeName );
                        
                        $compilercontentattribute->plugin = $attr;
                        if( $attr )
                        {
                            if( $attr->beforeRead( $this, $xcontent, $compilercontentattribute ) === false )
                            {
                                $cancel = true; 
                                break;
                            }
                        }
                    }
                }
                
                if( $cancel )
                {
                    continue;
                }
                
                if( $element )
                {
                    $element->beforeRead( $this, $xcontent, $child );
                }
               
                foreach( $xcontent->getAttributes() as $compilercontentattribute )
                {
                    $this->parseAttributeFunction( $compilercontentattribute );
                    
                    if( $compilercontentattribute->plugin )
                    {
                        $compilercontentattribute->plugin->read( $this, $xcontent, $compilercontentattribute );
                    }
                }
            
                if( $element )
                {
                    $element->read( $this, $xcontent );
                }

                foreach( $xcontent->getAttributes() as $compilercontentattribute )
                {
                    if( $compilercontentattribute->plugin )
                    {
                        $compilercontentattribute->plugin->afterRead( $this, $xcontent, $compilercontentattribute );
                    }
                }
            
                $this->read( $xcontent, $child );
                
                if( $element )
                {
                    $element->afterRead( $this, $xcontent );
                }
                
                $xnode->addChild( $xcontent );
            }
        }
    }
    
    public function execute( CompilerContentElement $node )
    {
        
    }
    
    public function write( CompilerContentElement $nodes, $dom )
    {
        $children = $nodes->children();
        
        foreach( $children as $node )
        {
            $obj = new \stdClass();
            $obj->cancel = false;
            $obj->dom = $dom;
            if( $node->plugin )
            {
                $node->plugin->write( $this, $node, $obj );
            }
            
            foreach( $node->getAttributes() as $attr )
            {
                if(  $attr->plugin )
                {
                    $attr->plugin->write( $this, $node, $attr, $obj );
                }
                
                if(  $attr->plugin )
                {
                    $attr->plugin->afterWrite( $this, $node, $attr, $obj );
                }
            }
            
            if( $node->plugin )
            {
                $node->plugin->afterwriteAttribute( $this, $node, $obj );
            }
            
            if( ! $obj->cancel )
            {
                $this->write( $node, $dom );
            }
            
            if( $node->plugin )
            {
                $node->plugin->afterWrite( $this, $node, $obj );
            }
        }
    }
    
    public function getPluginElement( $name )
    {
        if( isset( $this->_tags[ $name ] ) )
        {
            if( ! $this->_tags[ $name ][ 'load' ] )
            {
                if( is_readable( $this->_tags[ $name ][ 'fullpath' ] ) )
                {
                    require_once( $this->_tags[ $name ][ 'fullpath' ] );
                    $classname = '\\damix\\engines\\compiler\\drivers\\'.$this->_driver.'\\elements\\' . ucfirst($this->_driver) . 'Element' . ucfirst($name);
                    $this->_tags[ $name ][ 'load' ] = $classname;
                }
            }

            $classname = $this->_tags[ $name ][ 'load' ];

            return new $classname();
        }
        
        if( isset( $this->_tags[ $this->_defaultelement ] ) )
        {
            if( ! $this->_tags[ $this->_defaultelement ][ 'load' ] )
            {
                if( is_readable( $this->_tags[ $this->_defaultelement ][ 'fullpath' ] ) )
                {
                    require_once( $this->_tags[ $this->_defaultelement ][ 'fullpath' ] );
                    $classname = '\\damix\\engines\\compiler\\' . $this->_driver . 'element' . ucfirst($this->_defaultelement);
                    $this->_tags[ $this->_defaultelement ][ 'load' ] = $classname;
                }
            }
            $classname = $this->_tags[ $this->_defaultelement ][ 'load' ];
            return new $classname();
        }
        
        return null;
    }

    public function getPluginAttribute( $name )
    {
        if( isset( $this->_attr[ $name ] ) )
        {
            if( ! $this->_attr[ $name ][ 'load' ] )
            {
                if( is_readable( $this->_attr[ $name ][ 'fullpath' ] ) )
                {
                    require_once( $this->_attr[ $name ][ 'fullpath' ] );
					$classname = '\\damix\\engines\\compiler\\drivers\\'.$this->_driver.'\\attributes\\' . ucfirst($this->_driver) . 'Attribute' . ucfirst($name);
                    $this->_attr[ $name ][ 'load' ] = new $classname();
                }
            }
			
            return $this->_attr[ $name ][ 'load' ];
        }
        if( isset( $this->_attr[ $this->_defaultattribute ] ) )
        {
            if( ! $this->_attr[ $this->_defaultattribute ][ 'load' ] )
            {
                if( is_readable( $this->_attr[ $this->_defaultattribute ][ 'fullpath' ] ) )
                {
                    require_once( $this->_attr[ $this->_defaultattribute ][ 'fullpath' ] );
					$classname = '\\damix\\engines\\compiler\\drivers\\'.$this->_driver.'\\attributes\\' . ucfirst($this->_driver) . 'Attribute' . ucfirst($this->_defaultattribute);
                    $this->_attr[ $this->_defaultattribute ][ 'load' ] = new $classname();
                }
            }
            return $this->_attr[ $this->_defaultattribute ][ 'load' ];
        }
        
        return null;
    }
    
    protected function getPluginFunction( $name )
    {
        if( isset( $this->_func[ $name ] ) )
        {
            if( ! $this->_func[ $name ][ 'load' ] )
            {
                require_once( $this->_func[ $name ][ 'fullpath' ] );
                $classname = '\compiler\\' . $this->_driver . 'function' . $name;
                $this->_func[ $name ][ 'load' ] = new $classname();
            }
            return $this->_func[ $name ][ 'load' ];
        }
        
        return null;
    }
    
    protected function parseAttributeFunction( CompilerContentAttribute $attribute ) : void
    {
        $value = $attribute->value;
        
        if( $this->_language->parse( strval( $value ) ) )
        {
            $out = $this->_language->execute( array( $this, 'callback' ));
            
            if( is_array( $out ) && count( $out ) )
            {
                $attribute->value = $out[0];
            }
        }       
    }
    
	protected function parseValueFunction( \DOMNode $child )
    {
        $value = $child->nodeValue;
		
		if( preg_match('/(.*)\{([a-zA-Z0-9_\(\)\|]*)\}(.*)/', $value, $match ) )
		{
			$value = $match[2];
			
			if( $this->_language->parse( $value ) )
			{
				$out = $this->_language->execute( array( $this, 'callback' ));
				
				if( is_array( $out ) && count( $out ) )
				{
					$child->nodeValue = $match[1] . $out[0] . $match[3];
				}
			}       
		}
        
		
    }
    
    public function callback( $obj, $params )
    {
        $objfunction = $this->getPluginFunction( $obj->name );
        if( $objfunction )
        {
			$objfunction->parentDriver = $this->parentDriver;
            $out = call_user_func_array( array( $objfunction, 'execute' ), $params);
            return $out;
        }
    }
}