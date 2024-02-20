<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\iconfont;


class IconFontGenerator
    extends \damix\engines\tools\GeneratorContent
{   
	private \damix\engines\tools\xmlDocument $_document;
	private array $_dom = array();

    public function generate( IconFontSelector $selector ) : bool
    {
       if( $this->open( $selector ) )
        {
            $this->_document = \damix\engines\tools\xmlDocument::createDocument('iconfont');
            
            foreach( $this->_dom as $dom )
            {
                $this->compilefiles( $this->_document->childNodes[0], $dom['xml']->childNodes[0] );
            }
            
            $this->generateicon();
			
			
            $this->writeLine( '<?php' );
            $this->writeLine( 'namespace '. $selector->getNamespace() .';' );
            $this->writeLine( '' );
			$this->writeLine( 'class ' . $selector->getClassName() );
			$this->tab( 1 );
			$this->writeLine( 'extends \damix\engines\iconfont\IconFontBase' );
			$this->tab( -1 );
			$this->writeLine( '{' );
            $this->writecontent();
			$this->writeLine( '}' );
			
			
            \damix\engines\tools\xfile::write( $selector->getTempPath(), $this->getText() );
            return true;
        }
		\damix\engines\logs\log::dump( $selector );
		return false;
    }
	
	protected function open( IconFontSelector $selector ) : bool
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
					$new->nodeValue = $node->nodeValue;
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
	
	protected function generateicon() : void
	{
		$dom = $this->_document;
        $icons = $dom->xPath( '/iconfont/icon' );
        
		$iconcontent = array();
		foreach( $icons as $icon )
		{
			$name = $icon->getAttribute( 'name' );

			$content = array();
			$content[] = '$this->_icon[ \'' . $name .'\' ] = array(';
			$content[] = '\'name\' => '. $this->quote( $icon->getAttribute( 'name' ) ) .', ';
			$content[] = '\'class\' => '. $this->quote( $icon->getAttribute( 'class' ) ) .', ';
			$content[] = '\'title\' => '. $this->quote( $icon->getAttribute( 'title' ) ) .', ';
			$content[] = '\'label\' => '. $this->quote( $icon->getAttribute( 'label' ) ) .', ';
			$content[] = '\'fontclass\' => '. $this->quote( $icon->getAttribute( 'fontclass' ) ) .', ';
			
			$content[] = ');';
			$iconcontent[] = implode( '', $content );
		}
		$this->appendFunction( 'propertyinit', array(), $iconcontent, 'protected', 'void');
		
		$this->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
        
	}
	
    
}