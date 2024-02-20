<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\boutons;

class BoutonGenerator
   extends \damix\engines\tools\GeneratorContent
{
	private array $_dom = array();
	private string $name;
	private \damix\engines\orm\OrmBaseProperties $orm;
	private \damix\engines\tools\xmlDocument $_document;
    
	public function generate( BoutonSelector $selector ) : bool
    {
        if( $this->open( $selector ) )
        {
            $this->_document = \damix\engines\tools\xmlDocument::createDocument('boutons');
            
            foreach( $this->_dom as $dom )
            {
                $this->compilefiles( $this->_document->childNodes[0], $dom['xml']->childNodes[0] );
            }
            
            $this->generatebouton();
            
			
            $this->writeLine( '<?php' );
            $this->writeLine( 'namespace '. $selector->getNamespace() .';' );
            $this->writeLine( '' );
			$this->writeLine( 'class ' . $selector->getClassName() );
			$this->tab( 1 );
			$this->writeLine( 'extends \damix\engines\boutons\BoutonBase' );
			$this->tab( -1 );
			$this->writeLine( '{' );
            $this->writecontent();
			$this->writeLine( '}' );
			
            return \damix\engines\tools\xfile::write( $selector->getTempPath(), $this->getText() );
        }
		
		return false;
    }
    
    protected function open( BoutonSelector $selector ) : bool
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
	
	
	protected function generatebouton() : void
	{
		$dom = $this->_document;
        $boutons = $dom->xPath( '/boutons/bouton' );
        
		foreach( $boutons as $bouton )
		{
			$ref = $dom->getAttribute( $bouton, 'ref' );
			$onclick = $dom->getAttribute( $bouton,  'onclick' );
			$class = $dom->getAttribute( $bouton,  'class' );
			$couleur = $dom->getAttribute( $bouton,  'couleur' );
			
			$icon = \damix\engines\iconfont\IconFont::get();
			$htmlicon = $icon->getHtml( $ref );
			$prop = $icon->getProperty( $ref );

			if( $couleur == '' )
			{
				$couleur = 'bleu1';
			}
			$couleur = 'btn-damix-' . $couleur;
			
			$html = '<a class="btn '. $couleur . ' ' . $class .' damix-dt_btn-action" href="javascript:'. $onclick .'" title="'. \damix\engines\locales\Locale::get( $prop['title'] ) .'">'. $htmlicon . \damix\engines\locales\Locale::get( $prop['label'] ) .'</a>';
			
			$content = array();
			$content[] = '$this->_boutons[] = array( ';
			$content[] = '\'name\' => \'' . $dom->getAttribute( $bouton,  'name' ) . '\', ';
			$content[] = '\'class\' => \'' . $dom->getAttribute( $bouton,  'class' ) . '\', ';
			$content[] = '\'couleur\' => \'' . $dom->getAttribute( $bouton,  'couleur' ) . '\', ';
			$content[] = '\'acl\' => ' . $this->quote( $dom->getAttribute( $bouton,  'acl' )) . ', ';
			$content[] = '\'ref\' => \'' . $dom->getAttribute( $bouton,  'ref' ) . '\', ';
			$content[] = '\'visible\' => ' . ( tobool($dom->getAttribute( $bouton,  'visible' )) ? 'true' : 'false' ) . ', ';
			$content[] = '\'html\' => ' . $this->quote( $html ) . ', ';
			$content[] = ');';
			
			
			$this->appendFunction( 'propertyinit', array(), array( implode( '', $content )) , 'protected');
			
			$this->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
		}
	}
	
	
}