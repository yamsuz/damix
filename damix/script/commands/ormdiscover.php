<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandOrmdiscover
	extends DamixCommands
{
	private string $application;
	private string $module;
	private \damix\engines\tools\xmlDocument $xml;
	private \damix\engines\tools\xmlDocument $xmldefine;
	private \damix\engines\tools\xmlDocument $xmllocale;
	private \damix\engines\orm\defines\OrmDefinesSelector $ormdefinesselector;
	private \damix\engines\locales\LocaleSelector $localeselector;
	
	public function execute(array $params)
	{
		$this->application = $params['a'] ?? null;
		
		if( $this->application === null )
		{
			self::display( 'Le nom de l\'application est obligatoire.' );
			return;
		}
		
		$this->module = $params['m'] ?? '';
		
		if( $this->module === '' )
		{
			self::display( 'Le nom du module est obligatoire.' );
			return;
		}
		
		$alter = new \damix\engines\orm\request\OrmRequestAlter();
		$liste = $alter->SchemaBase();
		
		$this->loadXML();
		
		$defines = $this->xmldefine->firstChild;
		
		foreach( $liste as $info )
		{
			$tablename = $info->TABLE_NAME;
			
			$this->createdefine($defines, $tablename);
			
			$this->xml = \damix\engines\tools\xmlDocument::createDocument( 'orm' );
			$orm = $this->xml->firstChild;
			$xmltable = $this->xml->addElement( 'table', $orm, array( 'name' => $tablename, 'realname' => $tablename ) );
			
			$schema = \damix\engines\orm\request\structure\OrmSchema::newSchema( $info->TABLE_SCHEMA );
			$table = \damix\engines\orm\request\structure\OrmTable::newTable( $info->TABLE_NAME );
			$schema->addTable( $table );
			
			$alter->setTable( $table );
			
			
			$this->createrecord( $alter, $xmltable );
			$this->createindexes( $alter, $xmltable );
			$this->createtriggers( $alter, $xmltable );
			$this->createclass( $this->module, $tablename );
			
			$this->xml->saveDocument( $this->directory . $this->application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->module . DIRECTORY_SEPARATOR . 'orm' . DIRECTORY_SEPARATOR . 'torm' . DIRECTORY_SEPARATOR . 'torm' . $tablename . '.orm.xml');
		}
		
		
		\damix\engines\tools\xFile::createDir($this->directory . $this->application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->module . DIRECTORY_SEPARATOR . 'orm' . DIRECTORY_SEPARATOR . 'torm' . DIRECTORY_SEPARATOR . 'torm');
		
		$this->createstored( $alter );
		$this->createevent( $alter );
		
		$this->xmldefine->saveDocument( $this->ormdefinesselector->getFileDefault() );
		$this->xmllocale->saveDocument( $this->localeselector->getFileDefault() );
	}
	
	private function loadXML()
	{
		$this->ormdefinesselector = new \damix\engines\orm\defines\OrmDefinesSelector();
		
		if( file_exists( $this->ormdefinesselector->getFileDefault() ) )
		{
			$this->xmldefine = new \damix\engines\tools\xmlDocument();
			$this->xmldefine->load( $this->ormdefinesselector->getFileDefault() );
		}
		else
		{
			$this->xmldefine = \damix\engines\tools\xmlDocument::createDocument( 'defines' );
		}
		
		$this->localeselector = new \damix\engines\locales\LocaleSelector($this->module . '~lclorm.torm');
		
		if( file_exists( $this->localeselector->getFileDefault() ) )
		{
			$this->xmllocale = new \damix\engines\tools\xmlDocument();
			$this->xmllocale->load( $this->localeselector->getFileDefault() );
		}
		else
		{
			$this->xmllocale = \damix\engines\tools\xmlDocument::createDocument( 'locales' );
		}
	}
	
	private function createdefine(\DOMNode $defines, string $tablename)
	{
		$liste = $this->xmldefine->xPath('/defines/define[@name="' . strtoupper($tablename) . '"]');
		if( $liste->count() == 0)
		{
			$this->xmldefine->addElement( 'define', $defines, array( 'name' => strtoupper($tablename), 'value' => $this->module . '~torm' . $tablename, 'class' => $this->module . '~cls' . $tablename ) );
		}
	}
	
	private function createlocale(string $table, string $field)
	{
		$liste = $this->xmllocale->xPath('/locales/section[@name="torm' . $table . '"]/locale[@name="torm' . $table . '.' . $field . '"]');
		if( $liste->count() > 0)
		{
			return;
		}
		
		$liste = $this->xmllocale->xPath('/locales/section[@name="torm' . $table . '"]');
		if( $liste->count() == 0)
		{
			$locales = $this->xmllocale->firstChild;
			$section = $this->xmllocale->addElement( 'section', $locales, array( 'name' => 'torm' . $table ) );
		}
		else
		{
			$section = $liste->item(0);
		}
		$this->xmllocale->addElement( 'locale', $section, array( 'name' => 'torm' . $table . '.' . $field ), $field );
	}
	
	private function createindexes(\damix\engines\orm\request\OrmRequestAlter $alter, \DOMNode $table) : void
	{
		$foreignkeys = $this->xml->addElement( 'foreignkeys', $table, array() );
		$indexes = $this->xml->addElement( 'indexes', $table, array() );
		
		$listeind = array();
		
		$liste = $alter->SchemaIndex();
		foreach( $liste as $info )
		{
			if( $info->INDEX_NAME !== 'PRIMARY' )
			{
				if( ! isset( $listeind[$info->INDEX_NAME]  ) )
				{
					$attr = array();
					$attr['realname'] = $info->INDEX_NAME;
					$attr['type'] = 'index';
					$index = $this->xml->addElement( 'index', $indexes, $attr );
					
					$listeind[$info->INDEX_NAME] = $index;
				}
				else
				{
					$index = $listeind[$info->INDEX_NAME];
				}
				
				$this->xml->addElement( 'property', $index, array( 'name' => $info->COLUMN_NAME ) );
			}
		}
		
		$liste = $alter->SchemaForeignKey();
		
		foreach( $liste as $info )
		{
			$attr = array();
			$attr['name'] = $info->CONSTRAINT_NAME;
			$attr['property'] = $info->COLUMN_NAME;
			$attr['ref'] = '{' . strtoupper( $info->REFERENCED_TABLE_NAME ) . '}:' . $info->REFERENCED_COLUMN_NAME;
			$this->xml->addElement( 'foreignkey', $foreignkeys, $attr );
		}
		
	}
	
	private function createtriggers(\damix\engines\orm\request\OrmRequestAlter $alter, \DOMNode $table) : void
	{
		$triggers = $this->xml->addElement( 'triggers', $table, array() );
		
		$liste = $alter->SchemaTrigger();
		
		
		$selector = \damix\engines\orm\stored\OrmStored::getTriggersSelector();
		
		foreach( $liste as $info )
		{
			$attr = array();
			$attr['name'] = $info->TRIGGER_NAME;
			$attr['event'] = $info->ACTION_TIMING;
			$attr['action'] = $info->EVENT_MANIPULATION;
			$this->xml->addElement( 'trigger', $triggers, $attr );
			
			
			$content = $info->ACTION_STATEMENT;
			$filename = $selector->getPathContent( $info->TRIGGER_NAME );
			
			\damix\engines\tools\xFile::write($filename, $content);
		}

	}
	
	private function createstored(\damix\engines\orm\request\OrmRequestAlter $alter) : void
	{
		$liste = $alter->SchemaStored();
		
		
		$procedure = \damix\engines\orm\stored\OrmStored::getProceduresSelector();
		$function = \damix\engines\orm\stored\OrmStored::getFunctionsSelector();
		$fileproc = $procedure->getFileDefault();
		$filefunct = $function->getFileDefault();
		
		
		if( file_exists( $fileproc ) )
		{
			$xmlproc = new \damix\engines\tools\xmlDocument();
			$xmlproc->load( $fileproc );
		}
		else
		{
			$xmlproc = \damix\engines\tools\xmlDocument::createDocument( 'storages' );
		}
		if( file_exists( $filefunct ) )
		{
			$xmlfunc = new \damix\engines\tools\xmlDocument();
			$xmlfunc->load( $filefunct );
		}
		else
		{
			$xmlfunc = \damix\engines\tools\xmlDocument::createDocument( 'storages' );
		}
		
		
		$storagesfunct = $xmlfunc->firstChild;
		$storagesproc = $xmlproc->firstChild;
		foreach( $liste as $info )
		{
			
			$listeparameters = $alter->SchemaStoredParameter($info->ROUTINE_NAME);
			
			switch( $info->ROUTINE_TYPE )
			{
				case 'FUNCTION':
					$selector = $function;
					$liste = $xmlfunc->xPath('/storages/function[@name="' . $info->ROUTINE_NAME . '"]');
					if( $liste->count() == 0)
					{
						$attr = array();
						$attr['name'] = $info->ROUTINE_NAME;
						$attr['deterministic'] = strval($info->IS_DETERMINISTIC === 'YES' ? 1 : 0);
						$stored = $xmlfunc->addElement( 'function', $storagesfunct, $attr );
						
						$attr = array();
						$parameters = $xmlfunc->addElement( 'parameters', $stored, $attr );
						foreach( $listeparameters as $param )
						{
							if( $param->PARAMETER_NAME !== null )
							{
								$attr = array();
								$attr['name'] = $param->PARAMETER_NAME;
								$attr['type'] = $param->DATA_TYPE;
								$xmlfunc->addElement( 'parameter', $parameters, $attr );
							}
						}

						$return = $xmlfunc->addElement( 'return', $stored, array() );
						$xmlfunc->addElement( 'parameter', $return, array('type' => $info->DATA_TYPE) );
					}
					
					
					break;
				case 'PROCEDURE':
					$selector = $procedure;
					$liste = $xmlproc->xPath('/storages/procedure[@name="' . $info->ROUTINE_NAME . '"]');
					if( $liste->count() == 0)
					{
						$attr = array();
						$attr['name'] = $info->ROUTINE_NAME;
						$attr['deterministic'] = strval($info->IS_DETERMINISTIC === 'YES' ? 1 : 0);
							
						$stored = $xmlproc->addElement( 'procedure', $storagesproc, $attr );
						
						$attr = array();
						$parameters = $xmlproc->addElement( 'parameters', $stored, $attr );
						foreach( $listeparameters as $param )
						{
							if( $param->PARAMETER_NAME !== null )
							{
								$attr = array();
								$attr['name'] = $param->PARAMETER_NAME;
								$attr['type'] = $param->DATA_TYPE;
								$xmlproc->addElement( 'parameter', $parameters, $attr );
							}
						}
					}
					break;
			}
			
			
			
			$content = $info->ROUTINE_DEFINITION ?? '';
			$filename = $selector->getPathContent( $info->ROUTINE_NAME );
			
			\damix\engines\tools\xFile::write($filename, $content);
		}
		
		$xmlfunc->saveDocument( $filefunct );
		$xmlproc->saveDocument( $fileproc );
	}
	
	private function createevent(\damix\engines\orm\request\OrmRequestAlter $alter) : void
	{
		$liste = $alter->SchemaEvent();
		
		
		$event = \damix\engines\orm\stored\OrmStored::geEventsSelector();
		$fileevent = $event->getFileDefault();

		
		
		if( file_exists( $fileevent ) )
		{
			$xmlevent = new \damix\engines\tools\xmlDocument();
			$xmlevent->load( $fileevent );
		}
		else
		{
			$xmlevent = \damix\engines\tools\xmlDocument::createDocument( 'storages' );
		}
		
		$storagesproc = $xmlevent->firstChild;
		foreach( $liste as $info )
		{
			$selector = $event;
			$liste = $xmlevent->xPath('/storages/event[@name="' . $info->EVENT_NAME . '"]');
			if( $liste->count() == 0)
			{
				$attr = array();
				$attr['name'] = $info->EVENT_NAME;
				$attr['intervalvaleur' ] = $info->INTERVAL_VALUE;
				$attr['intervalunite' ] = $info->INTERVAL_FIELD;
				$attr['type' ] = $info->EVENT_TYPE;
				
				$stored = $xmlevent->addElement( 'event', $storagesproc, $attr );
			}
			
			$content = $info->EVENT_DEFINITION;
			$filename = $selector->getPathContent( $info->EVENT_NAME );
			
			\damix\engines\tools\xFile::write($filename, $content);
		}
		
		$xmlevent->saveDocument( $fileevent );
	}
	
	private function createrecord(\damix\engines\orm\request\OrmRequestAlter $alter, \DOMNode $table) : void
	{
		$liste = $alter->SchemaColonne();
			
		$properties = array();
		foreach( $liste as $cols )
		{
			$properties[] = $cols;
		}
			
		$driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();
		
		$tablename = $alter->getTable()->getRealName();
		
		
		$record = $this->xml->addElement( 'record', $table, array() );
		$primarykeys = $this->xml->addElement( 'primarykeys', $table, array() );
		
		foreach( $properties as $property )
		{
			$field = $driver->parseDatatypeTofield( $property );
			
			$attr = array( );
			$attr['name'] = $field->getName();
			$attr['realname'] = $field->getRealName();
			$attr['datatype'] = $field->getDatatype()->toString();
			if( $field->getSize() > 0 )
			{
				$attr['size'] = strval($field->getSize());
			}
			if( $field->getPrecision() > 0 )
			{
				$attr['precision'] = strval($field->getPrecision());
			}
			if( $field->getDatatype() == \damix\engines\orm\request\structure\OrmDataType::ORM_ENUM )
			{
				$attr['enumerate'] = implode(';', $field->getEnumerate());
			}
			$attr['null'] = ($field->getNull() ? 'true' : 'false');
			$attr['default'] = ($field->getDefault() === null ? 'null' : $field->getDefault());
			$attr['locale'] = $this->module . '~lclorm.torm' . $tablename . '.' . $field->getName();
			$attr['unsigned'] = ($field->getUnsigned() ? 'true' : 'false');
			if( $field->getAutoincrement() )
			{
				$attr['autoincrement'] = 'true' ;
			}
			elseif( $field->getPrimaryKey() )
			{
				$attr['autoincrement'] = 'false' ;
			}
			$this->xml->addElement( 'property', $record, $attr );
			
			if( $field->getPrimaryKey() )
			{
				$this->xml->addElement( 'primarykey', $primarykeys, array( 'name' =>$field->getName()) );
			}
			
			$this->createlocale( $tablename, $field->getName() );
			
		}
		
	
	}

	private function createclass(string $module, string $table)
	{
		$content = array();
		
		$content[] = '<?php';
		$content[] = '/**';
		$content[] = '* @package      ' . $this->application;
		$content[] = '* @Module       ' . $module;
		$content[] = '* @Ressource    cls' . $table;
		$content[] = '* @author       PANIEN Vincent';
		$content[] = '* @copyright    Damix';
		$content[] = '*/';
		$content[] = '';
		$content[] = 'declare(strict_types=1);';
		$content[] = '';
		$content[] = 'namespace ' . $this->application . '\\' . $module .';';
		$content[] = '';
		$content[] = '\damix\engines\orm\Orm::inc( \'' . $module . '~' . 'torm'. $table .'\' );';
		$content[] = '';
		$content[] = 'class cls' . $table;
		$content[] = "\t" . 'extends \''.$this->application.'\\' . $module. '\\cOrmProperties_' . $module . '_torm'. $table .'';
		$content[] = '{';
		$content[] = '';
		$content[] = '}';
		
		\damix\engines\tools\xFile::write($this->directory . $this->application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->module . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'cls' . $table . '.class.php', implode("\r\n", $content));
	}
}