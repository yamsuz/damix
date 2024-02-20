<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm;

class OrmGenerator
   extends \damix\engines\tools\GeneratorContent
{
    public array $_dom = array();
    public \damix\engines\tools\xmlDocument $_document;
    
    public function generate( OrmSelector $selector  ) : bool
    {
        if( $this->open( $selector ) )
        {
            $this->_document = \damix\engines\tools\xmlDocument::createDocument('orm');
            
            foreach( $this->_dom as $dom )
            {
                $this->compilefiles( $this->_document->childNodes[0], $dom['xml']->childNodes[0] );
            }
            
            $this->writeLine( '<?php' );
            $this->writeLine( 'namespace '. $selector->getNamespace() .';' );
            $this->writeLine( '' );


            $this->generaterecord();
            $this->writefilerecord( $selector );
			$this->clear();
            
			$this->generateproperties();
            $this->writefileproperties( $selector );
			$this->clear();

            $this->generatefactory();
            $this->writefilefactory( $selector );
			
            
            \damix\engines\tools\xfile::write( $selector->getTempPath(), $this->getText() );
            return true;
        }
		
		return false;
    }
    
    protected function open( OrmSelector $selector ) : bool
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
    
    private function generateproperties() : void
    {
        $dom = $this->_document;
        $liste = $dom->xPath( '/orm/table' );
        
        $tablecontent = array();
        foreach( $liste as $tables )
        {
            $tablename = $dom->getAttribute( $tables, 'name' );
            $tablecontent[] = '$this->_table = array(\'name\' => \''. $dom->getAttribute( $tables, 'name' ) .'\', \'realname\' => \''. $dom->getAttribute( $tables, 'realname' ) .'\', \'engine\' => \''. $dom->getAttribute( $tables, 'engine' ) .'\' );';
            
            foreach( $tables->childNodes as $table )
            {
                switch( $table->nodeName )
                {
                    case 'record' :
                        $this->generatetablerecord( $table );
                        break;
                    case 'primarykeys' :
                        $this->generatetableprimary( $table );
                        break;
                }
            }
        }
        $this->appendFunction( 'propertyinit', array(), $tablecontent, 'protected', 'void');
        
        $this->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
    }
    
    private function generatetablerecord( \DomElement $record ) : void
    {
        $dom = $this->_document;
		$tablecontent = array();
        foreach( $record->childNodes as $property )
        {            
            $tablecontent[] = '$this->_properties[\''. $dom->getAttribute( $property, 'name' ) .'\'] = array(\'name\' => \''. $dom->getAttribute( $property, 'name' ) .'\', \'realname\' => \''. $dom->getAttribute( $property, 'realname' ) .'\', \'datatype\' => \''. $dom->getAttribute( $property, 'datatype' ) .'\', \'format\' => \''. $dom->getAttribute( $property, 'format' ) .'\', \'value\' => \''. $dom->getAttribute( $property, 'value' ) .'\', \'default\' => \''. $dom->getAttribute( $property, 'default' ) .'\', \'insertpattern\' => '. ( $dom->hasAttribute( $property, 'insertpattern' ) ? '\'' . $dom->getAttribute( $property, 'insertpattern' ) . '\'' : 'null' ) .', \'updatepattern\' => '. ( $dom->hasAttribute( $property, 'updatepattern' ) ? '\'' . $dom->getAttribute( $property, 'updatepattern' ) . '\'' : 'null' ) .', \'autoincrement\' => '. ( $dom->hasAttribute( $property, 'autoincrement' ) ? (tobool( $dom->getAttribute( $property, 'autoincrement' )) ? 'true' : 'false') : 'false' ) .' );';
        }
        $this->appendFunction( 'propertyinit', array(), $tablecontent, 'protected', 'void');
    }
    
    private function generatetableprimary( \DomElement $record ) : void
    {
        $dom = $this->_document;
        $tablecontent = array();
        foreach( $record->childNodes as $property )
        {
			$name = $dom->getAttribute( $property, 'name' );
			if( ! empty( $name ) )
			{
				$dom = $this->_document;
				$prop = $dom->xPath( '/orm/table/record/property[@name="' . $name . '"]' )->item(0);
				
				$realname = $dom->getAttribute( $prop, 'realname' );
				
				$tablecontent[] = '$this->_primarykeys = array(\'name\' => \''. $name .'\', \'realname\' => \''. $realname .'\' );';
			}
        }
        
        $this->appendFunction( 'propertyinit', array(), $tablecontent, 'protected', 'void');
    }
    
    private function generatetableevents( \DomElement $record ) : void
    {
		$dom = $this->_document;
		$tablecontent = array();
        foreach( $record->childNodes as $property )
        {            
			$tablecontent[] = '$this->_events[' . $this->quote( $dom->getAttribute( $property, 'event' )) . ']['. $this->quote( $dom->getAttribute( $property, 'action' )) .'][] = array( '. $this->quote('name') . ' => '. $this->quote( $dom->getAttribute( $property, 'name' )) .', \'event\' => '. $this->quote( $dom->getAttribute( $property, 'event' )) .', \'action\' => '. $this->quote( $dom->getAttribute( $property, 'action' )) .' );';
        }
        $this->appendFunction( 'propertyinit', array(), $tablecontent, 'protected', 'void');
    }
    
    
    private function generatefactory() : void
    {
        $dom = $this->_document;
        $liste = $dom->xPath( '/orm/factory' );
        
        
        foreach( $liste as $factory )
        {
            foreach( $factory->childNodes as $method )
            {
                switch( $method->getAttribute( 'type' ) )
                {
                    case 'select' :
                        $this->generatemethodselectfactory( $method , '?\damix\engines\databases\DbResultSet');
                        break;
                }
            }
        }
        
        
        $liste = $dom->xPath( '/orm/table' );
        $methodcontent = array();
        foreach( $liste as $tables )
        {
            $this->generatemethodselect( $tables, 'select', '?\damix\engines\databases\DbResultSet');
            $this->generatemethodselect( $tables, 'get', '\damix\engines\orm\OrmBaseRecord' );
			
			foreach( $tables->childNodes as $table )
            {
                switch( $table->nodeName )
                {
                    case 'primarykeys' :
                        $this->generatetableprimary( $table );
                        break;
					case 'events' :
                        $this->generatetableevents( $table );
                        break;
                }
            }
			
			$this->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
        }
        
    }
   
    private function generatemethodselectfactory( \DOMElement $method, string $return ) : void
    {
		$name = $method->getAttribute( 'name' );
		
		$dom = $this->_document;
        $liste = $dom->xPath( 'joins/join[@type="from"]', $method );
		
        $methodcontent = array();
        $methodcontent[] = '$this->_method[\''. $name .'\'] = array(\'name\' => \''. $name .'\', ';
		$methodcontent[] = '\'events\' => array(';
		foreach( $liste as $elt )
		{
			$ref = \damix\engines\orm\Orm::getDefine( $elt->getAttribute('ref') );
			if( $ref )
			{
				$events = $ref['orm']->getEvents();
				if( count( $events ) > 0) 
				{
					$methodcontent[] = 'array(';
					foreach( $events as $event )
					{
						$methodcontent[] = '\'name\' => \'' . $event['name'] . '\', ';
						$methodcontent[] = '\'table\' => \'' . $ref['orm']->name . '\', ';
					}
					$methodcontent[] = '), ';
				}
			}
		}
		$methodcontent[] = '), ';
        $methodcontent[] = ');';
		
        $this->appendFunction( 'propertyinit', array(), array(implode('', $methodcontent)), 'protected', 'void' );
    }
   
    private function generatemethodselect( \DOMElement $table, string $name, string $return ) : void
    {
        $methodcontent = array();
		
        $dom = $this->_document;
        $events = $dom->xPath( 'events/event', $table );
		
		$methodcontent[] = '$this->_method[\''. $name .'\'] = array(\'name\' => \''. $name .'\', ';
		$methodcontent[] = '\'events\' => array(';
					
		$methodcontent[] = 'array(';
		foreach( $events as $event )
		{
			$methodcontent[] = '\'name\' => \'' . $event->getAttribute('name') . '\', ';
			$methodcontent[] = '\'table\' => \'' . $table->getAttribute('name') . '\', ';
		}
		$methodcontent[] = '), ';
	
		$methodcontent[] = '), ';
        $methodcontent[] = ');';
		
        $line = implode('', $methodcontent);
        
        $this->appendFunction( 'propertyinit', array(), array( $line ), 'protected', 'void');
    }
    
    protected function writecontent( ) : void
    {
        $properties = $this->getProperties();
        
        foreach( $properties as $name => $info )
        {
            $this->tab( 1 );
            $this->writeLine( $info['visibility'] . ' ' . ($info['null']?'?':'') . $info['type'] . ' $' . $name . ($info['value'] ? ' = ' . $info['value'] : '' ) . ';' );
            
            $this->tab( -1 );
        }

        $this->tab( 1 );

        $this->writeLine( '' );
        $construct = $this->getConstruct();
        $this->writeLine( 'public function __construct()' );
        $this->writeLine( '{' );
        $this->tab( 1 );
        $this->writeLine( 'parent::__construct();' );
        foreach( $construct as $name => $info )
        {
            $this->writeLine( implode( ';', $info ) );
        }
        $this->tab( -1 );
        $this->writeLine( '}' );
        $this->tab( -1 );
        
        
        $functions = $this->getFunction();
        foreach( $functions as $name => $info )
        {
            $this->tab( 1 );
            $this->writeLine( $info['visibility'] . ' function ' . $name . '(' . implode( ',', $info['params'] ). ')' . (!empty($info['return']) ? ':' . $info['return'] : ''));
            $this->writeLine( '{' );
            $this->tab( 1 );
            foreach( $info['content'] as $content )
            {
                if( is_array( $content ) )
                {
                    foreach( $content as $elt )
					{
						$this->writeLine( $elt );
					}
                }
				else
				{
					$this->writeLine( $content );
				}
            }
            $this->tab( -1 );
            $this->writeLine( '}' );
            
            $this->tab( -1 );
        }
    }
    
    private function writefileproperties( OrmSelector $selector ) : void
    {
        $this->writeLine( 'class ' . $selector->getPropertiesClassName() );
        $this->tab( 1 );
        $this->writeLine( 'extends \damix\engines\orm\OrmBaseProperties' );
        $this->tab( -1 );
        $this->writeLine( '{' );
        // $this->tab( 1 );
        // $this->writeLine( 'protected array $_table = array();' );
        // $this->writeLine( 'protected array $_properties = array();' );
        // $this->writeLine( 'protected string $_selector = \'' . $selector-> _selector .'\';' );
        // $this->tab( -1 );
		
		$this->addProperty( '_table', 'array()', 'array', false, 'protected' );
		$this->addProperty( '_properties', 'array()', 'array', false, 'protected' );
		$this->addProperty( '_selector', $this->quote($selector-> _selector), 'string', false, 'protected' );
        
        
        $this->writecontent();
        
        
        $this->writeLine( '}' );
        
    }
    
    private function writefilefactory( OrmSelector $selector ) : void
    {
        
        $this->writeLine( '' );
        $this->writeLine( 'class ' . $selector->getFactoryClassName() );
        $this->tab( 1 );
        $this->writeLine( 'extends \damix\engines\orm\OrmBaseFactory' );
        $this->tab( -1 );
        $this->writeLine( '{' );
        
        $this->tab( 1 );
        
        $this->writeLine( 'public string $module = \''. $selector->getPart( 'module' ) . '\';' );
        $this->writeLine( 'public string $resource = \''. $selector->getPart( 'resource' ) . '\';' );
        
        
        $dom = $this->_document;
        
        $defaultprofile = \damix\engines\settings\Setting::getValue('profile', 'database', 'default');
        $schemaname = \damix\engines\settings\Setting::getValue('profile', $defaultprofile, 'schema');
		
        $liste = $dom->xPath( '/orm/table' );
        foreach( $liste as $tables )
        {
			
			$schema = $dom->getAttribute( $tables, 'schema', $schemaname );
			
			
            $tablecontent = array();
			
			$tablecontent[] = '$this->_table = array(\'name\' => \''. $dom->getAttribute( $tables, 'name' ) .'\', \'realname\' => \''. $dom->getAttribute( $tables, 'realname' ) .'\', \'engine\' => \''. $dom->getAttribute( $tables, 'engine' ) .'\' );';
            $tablecontent[] = '$this->_schema = \''. $schema . '\';';
			
			foreach( $tables->childNodes as $table )
            {
                switch( $table->nodeName )
                {
                    case 'record' :
                        
                        foreach( $table->childNodes as $property )
                        {
                            $tablecontent[] = '$this->_properties[\''. $dom->getAttribute( $property, 'name' ) .'\'] = array(\'name\' => \''. $dom->getAttribute( $property, 'name' ) .'\', \'datatype\' => \''. $dom->getAttribute( $property, 'datatype' ) .'\', \'insertpattern\' => '. ( $dom->hasAttribute( $property, 'insertpattern' ) ? '\'' . $dom->getAttribute( $property, 'insertpattern' ) . '\'' : 'null' ) .', \'updatepattern\' => '. ( $dom->hasAttribute( $property, 'updatepattern' ) ? '\'' . $dom->getAttribute( $property, 'updatepattern' ) . '\'' : 'null' ) .', \'autoincrement\' => '. ( $dom->hasAttribute( $property, 'autoincrement' ) ? (tobool( $dom->getAttribute( $property, 'autoincrement' )) ? 'true' : 'false') : 'false' ) .' );';
                        }
                        
                        break;
                }
            }
			
			
            $this->appendFunction( 'propertyinit', array(), $tablecontent, 'protected', 'void');
			
			$tablecontent = array();
            $tablecontent[] = '$record = new ' . $selector->getRecordClassName() . ';';
            $tablecontent[] = '$record->__default();';
            $tablecontent[] = 'return $record;';
            $this->appendFunction( 'createRecord', array(), $tablecontent, 'public', '\damix\engines\orm\OrmBaseRecord');
            
            
            
            $tablecontent = array();
            $tablecontent[] = '$obj = \damix\engines\orm\method\OrmMethod::execute( \''. $selector->_selector .':select\', $this );';

            $tablecontent[] = '$sql = $obj->execute( $this->getConditionsAll( \'select\' ) );';
            $tablecontent[] = 'return $this->query( $sql );';
            
            $this->appendFunction( 'select', array(), $tablecontent, 'public', '?\damix\engines\databases\DbResultSet');
            
            $tablecontent = array();
            $tablecontent[] = '$obj = \damix\engines\orm\method\OrmMethod::execute( \''. $selector->_selector .':select\', $this );';

            $tablecontent[] = 'return $obj;';
            
            $this->appendFunction( 'selectRequest', array(), $tablecontent, 'public');
            
            
            
            
            $tablecontent = array();

            $tablecontent[] = '$c = $this->getConditionsClear( \'get\' );';
            $tablecontent[] = '$c->addString( $this->_primarykeys[\'realname\'], \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $id );';
            $tablecontent[] = '$obj = \damix\engines\orm\method\OrmMethod::execute( \''. $selector->_selector .':get\', $this );';
            $tablecontent[] = '$cs = new \damix\engines\orm\conditions\OrmConditions();';
            $tablecontent[] = '$cs->add( $c );';
            $tablecontent[] = '$sql = $obj->execute( $cs );';
            $tablecontent[] = '$liste = $this->query( $sql );';
            $tablecontent[] = '$fetch = $liste->fetch();';
            $tablecontent[] = '$record = new '. $selector->getRecordClassName() .'();';
            $tablecontent[] = '$record->loadrecord( $fetch );';
            $tablecontent[] = 'return $record;';
            
            $this->appendFunction( 'get', array('string $id'), $tablecontent, 'public', '\damix\engines\orm\OrmBaseRecord');
            
            $tablecontent = array();
            $tablecontent[] = '$obj = \damix\engines\orm\method\OrmMethod::execute( \''. $selector->_selector .':get\', $this );';

            $tablecontent[] = 'return $obj;';
            
            $this->appendFunction( 'getRequest', array(), $tablecontent, 'public');
        }
        
        
        $liste = $dom->xPath( '/orm/factory/method' );
        
        foreach( $liste as $method )
        {
            $tablecontent = array();

			$events = $dom->xPath( 'events/event', $method);
            $tablecontent[] = '$c = $this->getConditionsAll( \''. $method->getAttribute( 'name' ) .'\' );';
			foreach( $events as $event)
			{
				$tablecontent[] = '\damix\engines\events\Event::notify( \'orm' . $event->getAttribute('name') . '\', array(\'condition\' => $c));';
			}
            $tablecontent[] = '$obj = \damix\engines\orm\method\OrmMethod::execute( \''. $selector->getPart( 'module') . '~' . $selector->getPart( 'resource') .':'. $method->getAttribute( 'name' ) .'\', $this );';
            $tablecontent[] = '$sql = $obj->execute( $c );';
            $tablecontent[] = 'return $this->query( $sql );';
            
            $this->appendFunction( $method->getAttribute( 'name' ), array(), $tablecontent, 'public');
            
            $tablecontent = array();
            $tablecontent[] = '$obj = \damix\engines\orm\method\OrmMethod::execute( \''. $selector->getPart( 'module') . '~' . $selector->getPart( 'resource') .':'. $method->getAttribute( 'name' ) .'\', $this );';

            $tablecontent[] = 'return $obj;';
            
            $this->appendFunction( $method->getAttribute( 'name' ) . 'Request', array(), $tablecontent, 'public');
            
        }
        
        
        $this->tab( -1 );
        
        $this->writecontent();
        
        $this->tab( -1 );
        $this->writeLine( '}' );
    }
    
    private function generaterecord() : void
    {
        $dom = $this->_document;
        $liste = $dom->xPath( '/orm/table' );
        $record = false;
        foreach( $liste as $tables )
        {
            $tablename = $dom->getAttribute( $tables, 'name' );
            $tablecontent[] = '$this->_table = array(\'name\' => \''. $dom->getAttribute( $tables, 'name' ) .'\', \'realname\' => \''. $dom->getAttribute( $tables, 'realname' ) .'\', \'engine\' => \''. $dom->getAttribute( $tables, 'engine' ) .'\' );';
            
            $this->appendFunction( 'propertyinit', array(), $tablecontent, 'protected', 'void');
        
            $this->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
            
            foreach( $tables->childNodes as $table )
            {
                switch( $table->nodeName )
                {
                    case 'record' :
                        $tablecontent = array();
                        foreach( $table->childNodes as $property )
                        {
                            // $tablecontent[] = '$this->_properties[\''. $dom->getAttribute( $property, 'name' ) .'\'] = array(\'name\' => \''. $dom->getAttribute( $property, 'name' ) .'\', \'realname\' => \''. $dom->getAttribute( $property, 'realname' ) .'\', \'datatype\' => \''. $dom->getAttribute( $property, 'datatype' ) .'\', \'format\' => \''. $dom->getAttribute( $property, 'format' ) .'\', \'default\' => \''. $dom->getAttribute( $property, 'default' ) .'\' , \'null\' => '. $dom->getAttribute( $property, 'null', 'false' ) .', \'insertpattern\' => '. ( $dom->hasAttribute( $property, 'insertpattern' ) ? '\'' . $dom->getAttribute( $property, 'insertpattern' ) . '\'' : 'null' ) .', \'updatepattern\' => '. ( $dom->hasAttribute( $property, 'updatepattern' ) ? '\'' . $dom->getAttribute( $property, 'updatepattern' ) . '\'' : 'null' ) .' );';
                            $tablecontent[] = '$this->_properties[\''. $dom->getAttribute( $property, 'name' ) .'\'] = array(\'name\' => \''. $dom->getAttribute( $property, 'name' ) .'\' );';
                        }
               
                        if( count( $tablecontent ) > 0 )
                        {
                            $this->appendFunction( 'recordinit', array(), $tablecontent, 'protected', 'void');
                            $record = true;
                        }
                        
                        
                        break;
                }
            }
        }
		
        if( $record )
        {
			$tablecontent = array();
			$tablecontent[] = '$this->recordinit();';
			$this->appendFunction( 'propertyinit', array(), $tablecontent, 'protected', 'void');
            // $this->addConstructInit( 'recordinit', array( '$this->recordinit();' ) );
        }
    }
    
    private function writefilerecord( OrmSelector $selector ) : void
    {
        
        $this->writeLine( '' );
        $this->writeLine( 'class ' . $selector->getRecordClassName() );
        $this->tab( 1 );
        $this->writeLine( 'extends \damix\engines\orm\OrmBaseRecord' );
        $this->tab( -1 );
        $this->writeLine( '{' );
        
        $this->writecontent();
        
        $this->writeLine( '}' );
    }
}