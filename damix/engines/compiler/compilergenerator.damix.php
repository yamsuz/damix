<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler;

class CompilerGenerator
{
    private $_driver;
    private CompilerContentElement $_xcontent;
    private $_xcompiledriver;
    private array $_dom = array();
    private \damix\engines\tools\xmlDocument $_document;
    private string $_type = '';
   
    public function generate( \damix\core\Selector $xcompilerselector ) : bool
    {
        if( $this->open( $xcompilerselector ) )
        {       
            $dir = $this->getDirectory();
            $classname = 'damix\\engines\\compiler\\drivers\\' . $this->_driver . '\\' . $this->_driver . 'Drivers';
            my_autoload_register( $classname );
            $this->_xcompiledriver = new $classname();
            $this->_xcompiledriver->classname = $classname;
            $this->_xcompiledriver->selector = $xcompilerselector;
            $this->_xcompiledriver->init();
            
            $this->_xcompiledriver->_driver = $this->_driver;
            $this->_xcompiledriver->_tags = $this->loaddriver( $dir . 'elements' );
            $this->_xcompiledriver->_attr = $this->loaddriver( $dir . 'attributes' );
            $this->_xcompiledriver->_func = $this->loaddriver( $dir . 'functions' );
            $this->_xcompiledriver->_cfunc = $this->loaddriver( $dir . 'cfunctions' );
            $this->_xcompiledriver->loaddefaultplugin();
            
            $this->_document = \damix\engines\tools\xmlDocument::createDocument(null);
            
            
            foreach( $this->_dom as $dom )
            {
                $this->compilefiles( $this->_document, $dom['xml']);
            }

            $this->read();
            $this->execute();
            $this->write();
            
            $this->_xcompiledriver->writefile( $xcompilerselector );
            
            return true;
        }
        
        return false;
    }
    
    private function getDirectory() : string
    {
        return __DIR__ . DIRECTORY_SEPARATOR .'drivers' . DIRECTORY_SEPARATOR . $this->_driver . DIRECTORY_SEPARATOR;
    }
    
    private function open( \damix\core\Selector $selector ) : bool
    {
        foreach( $selector->files as $files )
        {
            $dom = new \damix\engines\tools\xmlDocument();
            if( $dom->load( $files[ 'filename' ] ) )
            {
                if( $this->_driver === null )
                {
                    $this->_driver = $dom->documentElement->getAttribute( 'driver' );
                    $this->_type = $dom->documentElement->getAttribute( 'confcompletion' );
                }
                else
                {
                    throw new \Exception('Error driver');
                }
                
                $this->_dom[] = array( 
                            'version' => $dom->getAttribute( $dom->documentElement, 'version', '1.0' ), 
                            'xml' => $dom
                            );
            }
        }
        
		if( property_exists( $selector, 'completion' ) )
        {
			foreach( $selector->completion as $files )
			{
				$dom = new \damix\engines\tools\xmlDocument();            
				if( $dom->load( $files[ 'filename' ] ) )
				{
					if( $this->_driver !== null )
					{
						$this->_driver = $dom->getAttribute( $dom->documentElement, 'driver' );
					}
					else
					{
						throw new \Exception('Error driver');
					}
					
					$this->_dom[] = array( 
								'version' => $dom->getAttribute( $dom->documentElement, 'version', '1.0' ), 
								'xml' => $dom
								);
				}
			}
		}
        
        return count( $this->_dom ) > 0;
    }
    
    private function loaddriver( string $dir ) : array
    {
        $out = array();
		if( ! is_dir( $dir ) )
		{
			return $out;
		}
        $directories = scandir( $dir );
        foreach( $directories as $elt )
        {
            if( $elt != '.' && $elt != '..' )
            {
                if( is_dir( $dir . DIRECTORY_SEPARATOR . $elt ) )
                {
                    $out = array_merge( $out, $this->loaddriver( $dir . DIRECTORY_SEPARATOR . $elt ) );
                }
                else
                {
                    if( preg_match( '/^([a-zA-Z0-9]*)\.plugin\.php$/', $elt, $match ) )
                    {
                        $name = $match[1];
                        $out[ $name ] = array( 
                            'name' => $name, 
                            'load' => false, 
                            'fullpath' => $dir . DIRECTORY_SEPARATOR . $elt,
                            );
                    }
                }
            }
        }
        
        return $out;
    }

    private function compilefiles( $general, $dom )
    {
        $completion = 'completion';

        foreach( $dom->childNodes as $node )
        {
            if( $node instanceof \DOMElement )
            {
                if( ! $node->hasAttribute( $completion ) )
                {
					$new = $node->cloneNode();
					$new = $this->_document->importNode($new, true);
					$general->appendChild( $new );

                    $this->compilefiles( $new, $node );
                }
                else
                {
                    $query = '//'. $node->nodeName .'[@name="'. $node->getAttribute( $completion ) .'"]' ;
					
                    $item = $this->_document->xPath( $query );
                    if( $item && $item->length == 1)
                    {
					    if( $this->_type == 'replace' )
                        {
					        $this->completionreplace( $item[0], $node );
                        }
                        else
                        {
					        $this->completioncopy( $item[0], $node );
                        }
                    }
					else
					{
						if( $node->nodeName == 'compiler' )
						{
							$this->compilefiles( $general->firstChild, $node );
						}
					}
                }
            }
            elseif( $node instanceof \DOMText )
            {
                $query = '//'. $node->parentNode->nodeName;
                
                $general->nodeValue = $node->nodeValue;
            }
        }
        
    }
    
    private function completioncopy( $dest, $src )
    {
        foreach( $src->attributes as $attribute)
        {
            $dest->setAttribute( $attribute->name, $attribute->value );
        }
        
        foreach( $src->childNodes as $node )
        {
            $new = $node->cloneNode();
            $new = $this->_document->importNode($new, true);
            $dest->appendChild( $new );
        }
    }
    
    private function completionreplace( $dest, $src )
    {
        $new = $src->cloneNode(true);
        $new = $this->_document->importNode($new, true);
        $dest->parentNode->replaceChild($new, $dest);
    }
    
    private function read()
    {
        $this->_xcontent = new CompilerContentElement();
        $this->_xcompiledriver->read( $this->_xcontent, $this->_document );
    }
    
    private function execute()
    {
        $this->_xcompiledriver->execute( $this->_xcontent );
    }
    
    private function write()
    {
        $this->_xcompiledriver->write( $this->_xcontent, $this->_document );
        
    }
}