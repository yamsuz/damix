<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\orm\defines;


class OrmDefinesGenerator
    extends \damix\engines\tools\GeneratorContent
{
    public array $_dom = array();
    public $_document;
    
    public function generate( OrmDefinesSelector $selector ) : bool
    {
        if( $this->open( $selector ) )
        {
            $this->writeLine( '<?php' );
            $this->writeLine( 'namespace '. $selector->getNamespace() .';' );
            $this->writeLine( '' );


            $this->writeLine( 'class cxOrmDefines' );
            $this->tab( 1 );
            $this->writeLine( 'extends \damix\engines\orm\defines\OrmDefinesBase' );
            $this->tab( -1 );
            $this->writeLine( '{' );
            
            $this->tab( 1 );
            $this->writeLine(  'protected array $_compiled = array(' , 1);
            $this->tab( 1 );
            foreach( $this->_dom as $dom )
            {
                $defines = $dom['xml']->xPath( '/defines/define' );
                foreach( $defines as $define )
                {
                    $this->writeLine(  '\'' . $define->getAttribute( 'name' ) . '\' => array( \'selector\' => \'' . $define->getAttribute( 'value' ) . '\', \'class\' => \'' . $define->getAttribute( 'class' ) . '\'),' );
                }
            }
            $this->tab( -1 );
            $this->writeLine(  ');', 1 );
            
            
            $this->tab( -1 );
            $this->writeLine( '}' );
            
            \damix\engines\tools\xFile::write( $selector->getTempPath(), $this->getText() );
			
			return true;
        }
		
		return false;
    }
    
    
    protected function open( \damix\core\Selector $selector ) : bool
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
		
        return count( $this->_dom ) > 0;
    }
    
}