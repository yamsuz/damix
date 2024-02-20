<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\settings;

class SettingGenerator
   extends \damix\engines\tools\GeneratorContent
{
	public array $_dom = array();
	public \damix\engines\tools\xmlDocument $_document;
    
	public function generate( SettingSelector $selector ) : bool
    {
        if( $this->open( $selector ) )
        {
            $this->_document = \damix\engines\tools\xmlDocument::createDocument('config');
            
            foreach( $this->_dom as $dom )
            {
                $this->compilefiles( $this->_document->childNodes[0], $dom['xml']->childNodes[0] );
            }
            
            $this->generatesetting();
			
			
            $this->writeLine( '<?php' );
            $this->writeLine( 'namespace '. $selector->getNamespace() .';' );
            $this->writeLine( '' );
			$this->writeLine( 'class ' . $selector->getClassName() );
			$this->tab( 1 );
			$this->writeLine( 'extends \damix\engines\settings\SettingBase' );
			$this->tab( -1 );
			$this->writeLine( '{' );
            $this->writecontent();
			$this->writeLine( '}' );
			
            
            \damix\engines\tools\xfile::write( $selector->getTempPath(), $this->getText() );
            return true;
        }
		
		return false;
    }
    
    protected function open( SettingSelector $selector ) : bool
    {
        foreach( $selector->files as $files )
        {
            $dom = new \damix\engines\tools\xmlDocument();
            if( $dom->load( $files[ 'filename' ] ) )
            {
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
                    $this->_dom[] = array( 
                                'version' => $dom->getAttribute( $dom->documentElement, 'version', '1.0' ), 
                                'xml' => $dom
                                );
                }
            }
        }
        
        return count( $this->_dom ) > 0;
    }
	
	protected function compilefiles( \DOMElement $general, \DOMElement $dom ) : void
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
                        $this->completioncopy( $item[0], $node );
                    }
                }
            }
        }
    }
	
	protected function generatesetting() : void
	{
		$dom = $this->_document;
        $sections = $dom->xPath( '/config/section' );
        
        foreach( $sections as $section )
        {
			
			$content = array();
			$content[] = '$this->_config[\''. $dom->getAttribute( $section, 'name' ) .'\'] = array( ';
            foreach( $section->childNodes as $config )
            {
				if( ! empty( $dom->getAttribute( $config, 'name' ) ) )
				{
					$content[] = '\''. $dom->getAttribute( $config, 'name' ) . '\' => \'' . $dom->getAttribute( $config, 'value' ) . '\', ';
				}
				else
				{
					$content[] = '\'' . $dom->getAttribute( $config, 'value' ) . '\', ';
				}	
            }
			
			$content[] = ');';
			
			$this->appendFunction( 'propertyinit', array(), array( implode( '', $content )) , 'protected', 'void');
        }
		
	}
	
}