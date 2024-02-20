<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\stored;

class OrmStoredGenerator
    extends \damix\engines\tools\GeneratorContent
{
    public array $_dom = array();
	public \damix\engines\tools\xmlDocument $_document;
    
    public function generate( OrmStoredSelector $selector ) : bool
    {
        if( $this->open( $selector ) )
        {
            $this->_document = \damix\engines\tools\xmlDocument::createDocument('storages');
            
            foreach( $this->_dom as $dom )
            {
                $this->compilefiles( $this->_document->childNodes[0], $dom['xml']->childNodes[0] );
            }
            
            $this->writeLine( '<?php' );
            $this->writeLine( 'namespace '.$selector->getNamespace().';' );
            $this->writeLine( '' );

            
            $this->generatefile( $selector );
            $this->writefile( $selector );
            
            
            \damix\engines\tools\xfile::write( $selector->getTempPath(), $this->getText() );
            return true;
        }
        return false;
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
    
    protected function open( OrmStoredSelector $selector )
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
    
    private function generatefile( OrmStoredSelector $selector )
    {
        $dom = $this->_document;
        $liste = $dom->xPath( '/storages/procedure' );
        foreach( $liste as $procedure )
        {
            $content = array();
            $params = array();
            $parameters = $dom->xPath( 'parameters/parameter', $procedure );
            
            $content[] = '$params = array();';
            
            foreach( $parameters as $parameter )
            {
                $params[] = '$' . $parameter->getAttribute( 'name' );
				
                $content[] = '$params[ \'' . $parameter->getAttribute( 'name' ) . '\' ] = array(\'type\' => ' . \damix\engines\orm\request\structure\OrmDataType::castToGenerate( $parameter->getAttribute( 'type' ) ) . ', \'unsigned\' => ' . ( tobool( $parameter->getAttribute( 'unsigned' ) ) ? 'true' : 'false' )  . ', \'size\' => ' . intval( $parameter->getAttribute( 'size' ) ) . ', \'null\' => ' . ( tobool( $parameter->getAttribute( 'null' ) ) ? 'true' : 'false' ) . ', \'precision\' => ' . intval( $parameter->getAttribute( 'precision' ) ) . ', \'value\' => $' . $parameter->getAttribute( 'name' ) . ');';
            }
            $content[] = '$this->execute( \'' . $procedure->getAttribute( 'schema' ) .'\', \'' . $procedure->getAttribute( 'name' ) .'\', $params );';
            $content[] = 'return true;';
            
            $this->appendFunction( $procedure->getAttribute( 'name' ), $params, $content, 'public', 'bool');
        }
        
        $liste = $dom->xPath( '/storages/function' );
        foreach( $liste as $function )
        {
            $content = array();
            $params = array();
            $parameters = $dom->xPath( 'parameters/parameter', $function );
            $return = $dom->xPath( 'return/parameter', $function )->item( 0 );
            
            $content[] = '$params = array();';
            
            foreach( $parameters as $parameter )
            {
                $params[] = '$' . $parameter->getAttribute( 'name' );
                $content[] = '$params[ \'' . $parameter->getAttribute( 'name' ) . '\' ] = array(\'type\' => ' . \damix\engines\orm\request\structure\OrmDataType::castToGenerate( $parameter->getAttribute( 'type' ) ) . ', \'unsigned\' => ' . ( tobool( $parameter->getAttribute( 'unsigned' ) ) ? 'true' : 'false' )  . ', \'size\' => ' . intval( $parameter->getAttribute( 'size' ) ) . ', \'null\' => ' . ( tobool( $parameter->getAttribute( 'null' ) ) ? 'true' : 'false' ) . ', \'precision\' => ' . intval( $parameter->getAttribute( 'precision' ) ) . ', \'value\' => $' . $parameter->getAttribute( 'name' ) . ');';
            }
            $content[] = '$out = $this->query( \'' . $function->getAttribute( 'schema' ) .'\', \'' . $function->getAttribute( 'name' ) .'\', $params, '. \damix\engines\orm\request\structure\OrmDataType::castToGenerate( $return->getAttribute( 'type' ) ) .' );';
            $content[] = 'return $out;';
            $this->appendFunction( $function->getAttribute( 'name' ), $params, $content, 'public', 'mixed');
        }
        
    }
    
    private function writefile( OrmStoredSelector $selector )
    {
        $this->writeLine( 'class ' . $selector->getClassName() );
        $this->tab( 1 );
        $this->writeLine( 'extends \damix\engines\orm\stored\OrmStoredBase' );
        $this->tab( -1 );
        $this->writeLine( '{' );
        
        $this->writecontent();
        
        
        $this->writeLine( '}' );
    }
    
}