<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm;

class OrmGeneratorStructure
    extends \damix\engines\tools\GeneratorContent

{
	public array $_dom = array();
    public \damix\engines\tools\xmlDocument $_document;
	
	public function generate( OrmStructureSelector $selector ) : bool
	{
		if( $this->open( $selector ) )
        {
            $this->_document = \damix\engines\tools\xmlDocument::createDocument('orm');
            
            foreach( $this->_dom as $dom )
            {
                $this->compilefiles( $this->_document->childNodes[0], $dom['xml']->childNodes[0] );
            }
            
			$defaultprofile = \damix\engines\settings\Setting::getValue('profile', 'database', 'default');
			$schemaname = \damix\engines\settings\Setting::getValue('profile', $defaultprofile, 'schema');
		
			$dom = $this->_document;
			$liste = $dom->xPath( '/orm/table' );
			foreach( $liste as $tables )
			{
				$this->addProperty( 'name', '\'' . $tables->getAttribute('name') . '\'', 'string', false, 'public' );
				$this->addProperty( 'realname', '\'' . $tables->getAttribute('realname') . '\'', 'string', false, 'public' );
				$this->addProperty( 'versionning', tobool($tables->getAttribute('versionning')) ? 'true' : 'false', 'bool', false, 'public' );
				$this->addProperty( 'schema', '\'' . $dom->getAttribute($tables, 'schema', $schemaname) . '\'', 'string', false, 'public' );
				
				foreach( $tables->childNodes as $table )
				{
					switch( $table->nodeName )
					{
						case 'record' :
							$this->generateRecord( $table );
							break;
						case 'primarykeys' :
							$this->generatePk( $table );
							break;
						case 'foreignkeys' :
							$this->generateFk( $table );
							break;
						case 'indexes' :
							$this->generateIndex( $table );
							break;
						case 'triggers' :
							$this->generateTrigger( $table );
							break;
						case 'options' :
							$this->generateOptions( $table );
							break;
						case 'events' :
							$this->generateEvents( $table );
							break;
					}
				}
			}
			
			
            $this->writeLine( '<?php' );
            $this->writeLine( 'namespace ' . $selector->getNamespace() . ';' );
            $this->writeLine( '' );
            $this->writeLine( 'class ' . $selector->getClassname());
			$this->tab( 1 );
            $this->writeLine( 'extends \damix\engines\orm\OrmBaseStructure');
			$this->tab( -1 );
            $this->writeLine( '{' );
			
			$this->writecontent();
			
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
	
	protected function compilefiles( $general, $dom )
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

	protected function generateRecord( \DOMElement $record ) : void
	{
		$content = array();

		foreach( $record->childNodes as $property )
		{
			$content[] = '$this->_properties[ '. $this->quote( $this->_document->getAttribute( $property, 'name' )) .' ] = array(';
			$content[] = "\t\t" . $this->quote('name') . ' => '. $this->quote( $property->getAttribute( 'name' )) .',';
			$content[] = "\t\t" . $this->quote('realname') . ' => '. $this->quote( $property->getAttribute( 'realname' )) .',';
			$content[] = "\t\t" . $this->quote('datatype') . ' => '. $this->quote( $property->getAttribute( 'datatype' )) .',';
			$content[] = "\t\t" . $this->quote('format') . ' => '. $this->quote( $property->getAttribute( 'format' )) .',';
			$content[] = "\t\t" . $this->quote('size') . ' => '. intval( $property->getAttribute( 'size' )) .',';
			$content[] = "\t\t" . $this->quote('precision') . ' => '. intval( $property->getAttribute( 'precision' )) .',';
			$content[] = "\t\t" . $this->quote('locale') . ' => '. $this->quote( $property->getAttribute( 'locale' )) .',';
			$content[] = "\t\t" . $this->quote('enumerate') . ' => '. $this->quote( $property->getAttribute( 'enumerate' )) .',';
			$content[] = "\t\t" . $this->quote('default') . ' => '. (strtolower($property->getAttribute( 'default' )) === 'null' ? 'null' : $this->quote( $property->getAttribute( 'default' ))) .',';
			$content[] = "\t\t" . $this->quote('combo') . ' => '. $this->quote( $property->getAttribute( 'combo' )) .',';
			$content[] = "\t\t" . $this->quote('null') . ' => '. (tobool( $property->getAttribute( 'null' )) ? 'true' : 'false') .',';
			$content[] = "\t\t" . $this->quote('unsigned') . ' => '. (tobool( $property->getAttribute( 'unsigned' )) ? 'true' : 'false') .',';
			$content[] = "\t\t" . $this->quote('autoincrement') . ' => '. (tobool( $property->getAttribute( 'autoincrement' )) ? 'true' : 'false') .',';
			$content[] = "\t" . ');';
		}
		
        $this->appendFunction( 'propertyinit', array(), $content, 'protected');
        
        $this->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
	}
	
	protected function generateOptions( \DOMElement $record ) : void
	{
		$content = array();

		foreach( $record->childNodes as $property )
		{
			$content[] = '$this->_options[ '. $this->quote( $this->_document->getAttribute( $property, 'driver', '*' )) .' ][ '. $this->quote( $this->_document->getAttribute( $property, 'name' )) .' ] = array( ';
			$content[] = $this->quote('name') . ' => '. $this->quote( $property->getAttribute( 'name' )) .',';
			$content[] = $this->quote('value') . ' => '. $this->quote( $property->getAttribute( 'value' )) .',';
			$content[] = ');' . "\r\n";
		}
		
        $this->appendFunction( 'propertyinit', array(), $content, 'protected');
        
        $this->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
	}
	
	protected function generateEvents( \DOMElement $record ) : void
	{
		$content = array();

		foreach( $record->childNodes as $property )
		{
			$content[] = '$this->_events[] = array( '. $this->quote('name') . ' => '. $this->quote( $this->_document->getAttribute( $property, 'name' )) .', \'event\' => '. $this->quote( $this->_document->getAttribute( $property, 'event' )) .', \'action\' => '. $this->quote( $this->_document->getAttribute( $property, 'action' )) .' );';
		}
		
        $this->appendFunction( 'events', array(), $content, 'protected');
        
        $this->addConstructInit( 'events', array( '$this->events();' ) );
	}
	
	protected function generatePk( \DOMElement $record ) : void
	{
		$content = array();


		$content[] = 'array(';
		foreach( $record->childNodes as $node )
		{
			$content[] = $this->quote( $node->getAttribute( 'name' ) ) . ', ';
		}
		
		$content[] = ')';
		
        $this->addProperty( 'primarykey', implode("", $content ), 'array', false, 'public' );
        
	}
	
	protected function generateFk( \DOMElement $record ) : void
	{
		$content = array();
        
		foreach( $record->childNodes as $property )
		{
			$reference = \damix\engines\orm\Orm::getDefine( $this->_document->getAttribute( $property, 'ref' ) );
			
			if( $reference )
			{
				if(  $reference['field'] === null )
				{
					throw new \damix\core\exception\OrmException('Reference is not exist : ' . $this->_document->getAttribute( $property, 'ref' ));
				}
				
				$content[] = '$this->_foreignkeys[] = array(';
				$content[] = "\t\t" . $this->quote('realname') . ' => '. $this->quote( $this->_document->getAttribute( $property, 'name' )) .', \'property\' => '. $this->quote( $this->_document->getAttribute( $property, 'property' )) .', \'ref\' => '. $this->quote( $this->_document->getAttribute( $property, 'ref' )) .',\'reference\' => array( \'orm\' => '. $this->quote(  $reference['orm']->selector->_selector ) .', \'property\' => '. $this->quote( $reference['field']['name'] ) .', ), \'update\' => '. $this->quote( $this->_document->getAttribute( $property, 'update' )) . ', \'delete\' => '. $this->quote( $this->_document->getAttribute( $property, 'delete' )) .' );';
			}
		}
		$this->appendFunction( 'foreignkeys', array(), $content, 'protected');
		
		$this->addConstructInit( 'foreignkeys', array( '$this->foreignkeys();' ) );
	}
	
	protected function generateIndex( \DOMElement $record ) : void
	{
		$content = array();
        foreach( $record->childNodes as $node )
        {
            $index = $node->childNodes;
            $field = 'array(';
            foreach( $index as $prop )
            {
                $field .= 'array( '. $this->quote('name') . ' => ' . $this->quote( $this->_document->getAttribute( $prop, 'name' )) . ', '. $this->quote('way') . ' => ' . $this->quote( $this->_document->getAttribute( $prop, 'way', 'asc' )) . '),';
            }
            $field .= ')';
            $content[] = '$this->_indexes[] = array( ' . $this->quote('realname') . ' => '. $this->quote( $this->_document->getAttribute( $node, 'realname' ) ) .', '. $this->quote('type') . ' => '. $this->quote( $this->_document->getAttribute( $node, 'type' ) ) .', '. $this->quote('field') . ' => '. $field .' );';
        }

        $this->appendFunction( 'indexes', array(), $content, 'protected');
        
        
        $this->addConstructInit( 'indexes', array( '$this->indexes();' ) );
	}
	
	protected function generateTrigger( \DOMElement $record ) : void
	{
		$content = array();
        foreach( $record->childNodes as $node )
        {
            $index = $node->childNodes;
			
			$event = match(strtoupper($this->_document->getAttribute( $node, 'event' )))
			{
				'BEFORE' => '\damix\engines\orm\request\structure\OrmTriggerEvent::ORM_BEFORE',
				'AFTER' => '\damix\engines\orm\request\structure\OrmTriggerEvent::ORM_AFTER',
			};
		
			$action = match(strtoupper($this->_document->getAttribute( $node, 'action' )))
			{
				'INSERT' => '\damix\engines\orm\request\structure\OrmTriggerAction::ORM_INSERT',
				'UPDATE' => '\damix\engines\orm\request\structure\OrmTriggerAction::ORM_UPDATE',
				'DELETE' => '\damix\engines\orm\request\structure\OrmTriggerAction::ORM_DELETE',
			};
			
            $content[] = '$this->_triggers[] = array(' . $this->quote('name') . ' => '. $this->quote( $this->_document->getAttribute( $node, 'name' ) ) .', ' . $this->quote('event') . ' => '. $event .', ' . $this->quote('action') . ' => '. $action .' );';
        }

        $this->appendFunction( 'triggers', array(), $content, 'protected');
        
        
        $this->addConstructInit( 'triggers', array( '$this->triggers();' ) );
	}
	
}