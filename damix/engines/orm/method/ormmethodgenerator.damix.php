<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\orm\method;

class OrmMethodGenerator
    extends \damix\engines\tools\GeneratorContent
{
    private array $requestcontent = array();
	public array $_dom = array();
    public \damix\engines\tools\xmlDocument $_document;
    protected \damix\engines\orm\method\OrmMethodSelector $_selector;
	
    public function generate( \damix\engines\orm\method\OrmMethodSelector $selector ) : bool
    {
		$this->_selector = $selector;

        if( $this->open( $selector ) )
        {
            $this->_document = \damix\engines\tools\xmlDocument::createDocument('orm');
            
            foreach( $this->_dom as $dom )
            {
                $this->compilefiles( $this->_document->childNodes[0], $dom['xml']->childNodes[0] );
            }
            
            
            $this->writeLine( '<?php' );
            $this->writeLine( 'namespace orm\method;' );
            $this->writeLine( '' );

            $this->requestcontent[] = '$OrmRequest = new \damix\engines\orm\request\OrmRequest();';
            $this->requestcontent[] = '$request = $OrmRequest->createSelect();';
                        
            $this->generateDisplay( $selector->getPart( 'function' ) );
            $this->generateFrom( $selector->getPart( 'function' ) );
            $this->generateGroup( $selector->getPart( 'function' ) );
            
            $this->generateautomatique();
            
            
            $this->requestcontent[] = 'return $request;';
            
            $this->appendFunction( 'getRequest', array(), $this->requestcontent, 'public', '\damix\engines\orm\request\OrmRequest');
            
            $this->writefileproperties( $selector );
           
            \damix\engines\tools\xfile::write( $selector->getTempPath(), $this->getText() );
            return true;
        }
        return false;
    }
 
	public function generateExecute( \damix\engines\orm\method\OrmMethodSelector $selector ) : bool
    {
        $this->writeLine( '<?php' );
        $this->writeLine( 'namespace orm\method;' );
        $this->writeLine( '' );

        $this->writeLine( 'class ' . $selector->getExecuteClassName() );
        $this->writeLine( '{' );
        $this->tab( 1 );
        
		$factory = $selector->getFactory();
		$drivername = $factory->getConnection()->getDriverName();
		
        $driver = \damix\engines\orm\drivers\OrmDrivers::getDriver( $drivername );
        // \damix\engines\logs\log::dump( $selector );
        $sql = $driver->getRequestSelect( $selector );

        $this->writeLine( 'public function execute( \damix\engines\orm\conditions\OrmConditions $conditions )' );
        $this->writeLine( '{' );
        $this->tab( 1 );
        $this->writeLine( '$sql = \'' . $sql . '\';' );
        $this->writeLine( 'return $sql;' );
        $this->tab( -1 );
        $this->writeLine( '}' );
        
        $this->tab( -1 );
        $this->writeLine( '}' );
        
        \damix\engines\tools\xfile::write( $selector->getTempPathExecute(), $this->getText() );
        return true;
        
    }
	
	protected function open( \damix\engines\orm\method\OrmMethodSelector $selector ) : bool
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
	 
    private function writefileproperties( $selector ) : void
    {
        $this->writeLine( 'class ' . $selector->getClassName() );
        $this->tab( 1 );
        $this->writeLine( 'extends \damix\engines\orm\OrmBaseProperties' );        
        $this->tab( -1 );
        $this->writeLine( '{' );
        
        
        $this->writecontent();
        
        
        $this->writeLine( '}' );
        
    }
    
    private function generateautomatique() : void
    {
        $dom = $this->_document;
        $liste = $dom->xPath( '/orm/table' );
        
		$defaultprofile = \damix\engines\settings\Setting::getValue('profile', 'database', 'default');
        $schemaname = \damix\engines\settings\Setting::getValue('profile', $defaultprofile, 'schema');
		
        $tablecontent = array();
        foreach( $liste as $table )
        {
            $tablename = $table->getAttribute( 'name' );
            $schema = $dom->getAttribute( $table, 'schema', $schemaname );
            
            $listerecords = $dom->xPath( 'record/property', $table );
            foreach( $listerecords as $property )
            {
                $name = $dom->getAttribute( $property, 'name' );
                $realname = $dom->getAttribute( $property, 'realname' );
                $datatype = $dom->getAttribute( $property, 'datatype' );
                $format = $dom->getAttribute( $property, 'format' );
                $size = $dom->getAttribute( $property, 'size' );
                $precision = $dom->getAttribute( $property, 'precision' );
                $locale = $dom->getAttribute( $property, 'locale' );
                $enumerate = $dom->getAttribute( $property, 'enumerate' );
                $default = $dom->getAttribute( $property, 'default' );
                $select = $dom->getAttribute( $property, 'select' );
                $null = $dom->getAttribute( $property, 'null' );
                $unsigned = $dom->getAttribute( $property, 'unsigned' );
                $alias = $tablename . '_' . $name;
                $ref = $this->_selector->getPart( 'module' ) . '~' . $this->_selector->getPart( 'resource' );
				
                if( $name != '' )
                {
                    $tablecontent[] = '$this->_properties[\''. $alias .'\'] = array(\'table\' => \''. $tablename .'\', \'name\' => \''. $name .'\', \'alias\' => \''. $alias .'\', \'datatype\' => \''. $datatype .'\', \'format\' => \''. $format .'\', \'size\' => \''. $size .'\', \'precision\' => \''. $precision .'\', \'locale\' => \''. $locale .'\', \'enumerate\' => \''. $enumerate .'\', \'default\' => \''. $default .'\', \'select\' => \''. $select .'\', \'null\' => \''. $null .'\', \'unsigned\' => \''. $unsigned .'\', );';

                    $this->requestcontent[] = '$column = new \damix\engines\orm\request\structure\OrmColumn();';
					$this->requestcontent[] = '$column->setColumnField( ' . $this->quote( $ref ) . ', ' . $this->quote( $tablename ) . ', ' . $this->quote( $realname ) . ', ' . $this->quote( $alias ) . ' );';
					$this->requestcontent[] = '$request->addDisplay($column);';
                }
            }
			$this->requestcontent[] = '$table = \damix\engines\orm\request\structure\OrmTable::newTable( ' . $this->quote( $tablename ) . ' );';
			$this->requestcontent[] = '$table->setReference( ' . $this->quote( $ref ) . ');';
            $this->requestcontent[] = '$schema = \damix\engines\orm\request\structure\OrmSchema::newSchema(\''. $schema .'\');';
			$this->requestcontent[] = '$schema->addTable( $table );';
			$this->requestcontent[] = '$join = $request->addJoin( \'from\', $table, ' . $this->quote( $tablename ) . ' );';
            // $this->requestcontent[] = '$from = new \damix\engines\orm\request\structure\OrmFrom();';
            // $this->requestcontent[] = '$from->setName( ' . $this->quote( $tablename ) . ' );';
            // $this->requestcontent[] = '$from->setAlias( ' . $this->quote( $tablename ) . ' );';
            // $this->requestcontent[] = '$from->setSchema( ' . $this->quote( $schema ) . ' );';
            // $this->requestcontent[] = '$request->setFrom( $from );';
        }
        
        $this->appendFunction( 'propertyinit', array(), $tablecontent, 'protected');
        
        
        $this->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
    }
    
    
    private function generateDisplay( $function ) : void
    {
        $dom = $this->_document;
        $liste = $dom->xPath( '/orm/factory/method[@name="'. $function .'"]/display' )->item(0);
        
        $tablecontent = array();
        
        if( $liste )
        {
            foreach( $liste->childNodes as $property )
            {
                switch( $property->nodeName )
                {
                    case 'property':
                        $obj = $this->generateDisplayPropery( $property );
                        if( $obj->name != '' )
                        {
                            if( $obj->isindex )
                            {
                                $indexcontent = array();
                                $indexcontent[] = '$this->_index[\''. $obj->alias .'\'] = array(\'alias\' => \''. $obj->alias .'\', \'name\' => \''. $obj->name .'\', );';
                                
                                $this->appendFunction( 'indexinit', array(), $indexcontent, 'protected');        
                                $this->addConstructInit( 'indexinit', array( '$this->indexinit();' ) );
                            }
                            
                            $tablecontent[] = '$this->_properties[\''. $obj->alias .'\'] = array(\'ref\' => \''. $obj->ref .'\', \'table\' => \''. $obj->table .'\', \'name\' => \''. $obj->name .'\', \'alias\' => \''. $obj->alias .'\', \'datatype\' => \''. $obj->datatype .'\', \'format\' => \''. $obj->format .'\', \'size\' => \''. $obj->size .'\', \'precision\' => \''. $obj->precision .'\', \'locale\' => \''. $obj->locale .'\', \'enumerate\' => \''. $obj->enumerate .'\', \'default\' => \''. $obj->default .'\', \'combo\' => \''. $obj->combo .'\', \'null\' => \''. $obj->null .'\', \'unsigned\' => \''. $obj->unsigned .'\', \'type\' => \'property\', );';

							if( $obj->orm ) 
							{
								$reffieldrealname = $obj->orm->getProperty($obj->name);
								$name = $reffieldrealname['realname'];
							}
							else
							{
								$name = $obj->name;
							}

							$this->requestcontent[] = '$column = new \damix\engines\orm\request\structure\OrmColumn();';
							$this->requestcontent[] = '$column->setColumnField( ' . $this->quote( $obj->ref ) . ', ' . $this->quote( $obj->table ) . ', ' . $this->quote( $name ) . ', ' . $this->quote( $obj->alias ) . ' );';
							$this->requestcontent[] = '$request->addDisplay($column);';
						}
                        break;
                    case 'function':
                        $obj = $this->generateDisplayFunction( $property );
                        $tablecontent[] = '$this->_properties[\''. $obj->alias .'\'] = array( \'name\' => \''. $obj->name .'\', \'alias\' => \''. $obj->alias .'\', \'datatype\' => \''. $obj->datatype .'\', \'format\' => \''. $obj->format .'\', \'locale\' => \''. $obj->locale .'\', \'type\' => \'function\', );';

						$this->requestcontent[] = '$column = new \damix\engines\orm\request\structure\OrmColumn();';
						$this->requestcontent[] = '$formula = $column->setColumnFormula( ' . $this->quote( $obj->name ) . ', ' . $this->quote( $obj->alias ) . ' );';
						$this->requestcontent[] = '$formula->addParameterArray( ' . $obj->parameters . ' );';
						$this->requestcontent[] = '$request->addDisplay($column);';
						
                        break;
                }
                
            }
        }
        
        $this->appendFunction( 'propertyinit', array(), $tablecontent, 'protected');
        
        
        $this->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
    }
    
    private function generateDisplayFunction( \DOMElement $function ) : \StdClass
    {
        $dom = $this->_document;
        $name = '';
        $alias = '';
        $datatype = '';
        $format = '';
        $locale = '';
        $out = new \StdClass();
        $out->name = $dom->getAttribute( $function, 'name', $name );
        $out->alias = $dom->getAttribute( $function, 'alias', $alias );
        $out->datatype = $dom->getAttribute(  $function, 'datatype', $datatype );
        $out->format = $dom->getAttribute(  $function, 'format', $format );
        $out->locale = $dom->getAttribute(  $function, 'locale', $locale );
        $out->parameters = 'array(';
        
        foreach( $function->childNodes as $property )
        {
            switch( $property->nodeName )
            {
                case 'property':
                    $obj = $this->generateDisplayPropery( $property );
                    $out->parameters .= 'array(\'type\' => \'property\', \'ref\' => \'' . $obj->ref . '\', \'table\' => \'' . $obj->table . '\', \'property\' => \'' . $obj->name . '\'),';
                    break;
                case 'raw':
                    $out->parameters  .= 'array(\'type\' => \'raw\', \'value\' => \'' . $dom->getAttribute( $property, 'value' ) . '\'),';
                    break;
                case 'string':
                    $out->parameters  .= 'array(\'type\' => \'string\', \'value\' => \'' . $dom->getAttribute( $property, 'value' ) . '\'),';
                    break;
                case 'comma':
                    $out->parameters .= 'array(\'type\' => \'comma\'),';
                    break;
                case 'function':
                    $obj = $this->generateDisplayFunction( $property );
                    
                    $out->parameters .= 'array(\'type\' => \'function\', \'name\' => \'' . $dom->getAttribute( $property, 'name' ) . '\', \'params\' => ' . $obj->parameters .'),';                 
                    break;
            }
        }
        $out->parameters .= ')';
        return $out;
    }
    
    private function generateDisplayPropery( \DOMElement $property ) : \StdClass
    {
        $dom = $this->_document;
        $name = '';
        $datatype = '';
        $format = '';
        $size = '';
        $precision = '';
        $locale = '';
        $enumerate = '';
        $default = '';
        $combo = '';
        $null = false;
        $unsigned = false;
        $alias = '' ;
        $table = '' ;
        
		$orm = null;
        $isindex = false;
        $ref = '';
        $define = '';
        if( $dom->hasAttribute( $property, 'ref' ) )
        {
            $ref = $dom->getAttribute( $property, 'ref' );
            if( $struct = $this->getDefine( $ref ) )
            {
				$orm = $struct['orm'];
                $table = $struct['orm']->name;
                $field = $struct['field'];
                
                if( $field )
                {
                    $name = $field['name'];
                    $datatype = $field['datatype'];
                    $format = $field['format'];
                    $size = $field['size'];
                    $precision = $field['precision'];
                    $locale = $field['locale'];
                    $enumerate = $field['enumerate'];
                    $default = $field['default'];
                    $combo = $field['combo'];
                    $null = $field['null'];
                    $unsigned = $field['unsigned'] ?? false;
                    
                    $indexes = $struct['orm']->getIndexes();
                    
                    foreach( $indexes as $index )
                    {
                        foreach( $index['field'] as $field )
                        {
                            if( $field['name'] == $name )
                            {
                                $isindex = true;
                                continue;
                            }
                        }
                    }
                }
            }
        }
        $out = new \StdClass();
		if( $orm )
		{
			$out->orm = $orm;
		}
        $out->ref = $dom->getAttribute( $property, 'ref', $ref );
        $out->table = $dom->getAttribute( $property, 'table', $table );
        $out->name = $dom->getAttribute( $property, 'name', $name );
        $out->datatype = $dom->getAttribute( $property, 'datatype', $datatype );
        $out->format = $dom->getAttribute( $property, 'format', $format );
        $out->size = $dom->getAttribute( $property, 'size', $size );
        $out->precision = $dom->getAttribute( $property, 'precision', $precision );
        $out->locale = $dom->getAttribute( $property, 'locale', $locale );
        $out->enumerate = $dom->getAttribute( $property, 'enumerate', $enumerate );
        $out->default = $dom->getAttribute( $property, 'default', ( $default ? $default : 'null'));
        $out->combo = $dom->getAttribute( $property, 'combo', $combo );
        $out->null = $dom->getAttribute( $property, 'null', $null );
        $out->unsigned = $dom->getAttribute( $property, 'unsigned', $unsigned );
        $out->alias = $dom->getAttribute( $property, 'alias', $out->table . '_' . $name );
        $out->isindex = $isindex;
        
        return $out;
    }
       
    private function generateFrom( string $function ) : void
    {
        $dom = $this->_document;
        $liste = $dom->xPath( '/orm/factory/method[@name="'. $function .'"]/joins' )->item(0);
        
        if( $liste )
		{
			foreach( $liste->childNodes as $join )
			{
				switch( $join->nodeName ) 
				{
					case 'join':
						$this->generateJoin( $join );
						break;
					case 'subrequest':
						$this->generateSubrequest( $join );
						break;
				}
			}
		}
    }

	private function generateJoin( \DOMNode $join ) : void
    {
        $dom = $this->_document;
       
		$table = '';
		$alias = '';
		$schema = '';
		$ref = '';
		if( $dom->hasAttribute( $join, 'ref' ) )
		{
			$ref = $dom->getAttribute( $join, 'ref' );
			$structable = $this->getDefine( $ref );

			$table = $structable['orm']->name;
			$alias = $structable['orm']->name;
			$schema = $structable['orm']->schema;			
		}
		$table = $dom->getAttribute( $join, 'table', $table );
		$alias = $dom->getAttribute( $join, 'alias', $alias );
		$schema = $dom->getAttribute( $join, 'schema', $schema );
		switch( $join->getAttribute( 'type' ) )
		{
			case 'from':
				$jointype = $join->getAttribute( 'type' );
				$reftable = $dom->getAttribute( $join, 'alias', $table );
				$this->requestcontent[] = '$table = \damix\engines\orm\request\structure\OrmTable::newTable( \''. $table .'\' );';
				$this->requestcontent[] = '$schema = \damix\engines\orm\request\structure\OrmSchema::newSchema(\''. $schema .'\');';
				$this->requestcontent[] = '$schema->addTable( $table );';
				$this->requestcontent[] = '$table->setReference( ' . $this->quote( $ref ) . ');';
				$this->requestcontent[] = '$join = $request->addJoin( \''. $jointype .'\', $table, \''. $reftable .'\' );';
				break;
			case 'join':
				$jointype = $dom->getAttribute( $join, 'join' );
				$reftable = $dom->getAttribute( $join, 'alias', $table );
				$this->requestcontent[] = '$table = \damix\engines\orm\request\structure\OrmTable::newTable( \''. $table .'\' );';
				$this->requestcontent[] = '$schema = \damix\engines\orm\request\structure\OrmSchema::newSchema(\''. $schema .'\');';
				$this->requestcontent[] = '$schema->addTable( $table );';
				$this->requestcontent[] = '$table->setReference( ' . $this->quote( $ref ) . ');';
				$this->requestcontent[] = '$join = $request->addJoin( \''. $jointype .'\', $table, \''. $reftable .'\' );';
				foreach( $join->childNodes as $cond)
				{
					$structable = $this->getDefine( $dom->getAttribute( $cond, 'ref' ) );
					$strucwith = $this->getDefine( $dom->getAttribute( $cond, 'with' ) );
					$operator = \damix\engines\orm\conditions\OrmOperator::cast($dom->getAttribute( $cond, 'operator', 'eq' ));
				   
					$reftable = $structable['orm']->name;
					$reffield = $structable['field']['name'];
					
					$reffieldrealname = $structable['orm']->getProperty($reffield);
		
					$withtable = $strucwith['orm']->name;
					$withfield = $strucwith['field']['name'];
					
					$reftable = $dom->getAttribute( $cond, 'ref_alias', $reftable );
					$withtable = $dom->getAttribute( $cond, 'with_alias', $withtable );
					
					$this->requestcontent[] = '$join->addConditionField( \'' . $reftable . '\', \'' . $reffieldrealname[ 'realname' ] . '\', ' . \damix\engines\orm\conditions\OrmOperator::toString($operator) . ', \'' . $withtable . '\', \'' . $withfield . '\' );';
				}
				break;
			default:
				throw new \damix\core\exception\CoreException('ORM : Type of request is unknown');
		}
    }
	
	private function generateSubrequest( \DOMNode $join ) : void
    {
        $dom = $this->_document;
       
		$table = '';
		$alias = '';
		$schema = '';
		$ref = '';
		if( $dom->hasAttribute( $join, 'ref' ) )
		{
			$ref = $dom->getAttribute( $join, 'ref' );
			$structable = $this->getDefine( $ref );

			$table = $structable['orm']->name;
			$alias = $structable['orm']->name;
			$schema = $structable['orm']->schema;			
		}
		$table = $dom->getAttribute( $join, 'table', $table );
		$alias = $dom->getAttribute( $join, 'alias', $alias );
		$schema = $dom->getAttribute( $join, 'schema', $schema );

		$selector = $dom->getAttribute( $join, 'selector' );
		$reftable = $dom->getAttribute( $join, 'alias', $table );
		$function = $dom->getAttribute( $join, 'function' );
		switch( $join->getAttribute( 'type' ) )
		{
			case 'from':
				$this->requestcontent[] = '$join = $request->addJoinSubrequestSelector( \'subfrom\', \''. $selector .'\', \''. $function .'\', \''. $reftable .'\' );';
				break;
			case 'join':
				$this->requestcontent[] = '$join = $request->addJoinSubrequestSelector( \'subjoin\', \''. $selector .'\', \''. $function .'\', \''. $reftable .'\' );';
				break;
			case 'left':
				$this->requestcontent[] = '$join = $request->addJoinSubrequestSelector( \'subleft\', \''. $selector .'\', \''. $function .'\', \''. $reftable .'\' );';
				break;
			case 'inner':
				$this->requestcontent[] = '$join = $request->addJoinSubrequestSelector( \'subinner\', \''. $selector .'\', \''. $function .'\', \''. $reftable .'\' );';
				break;
			default:
				throw new \damix\core\exception\CoreException('ORM : Type of request is unknown');
		}
    }
	
	private function generateGroup( string $function )
    {
        $dom = $this->_document;
        $liste = $dom->xPath( '/orm/factory/method[@name="'. $function .'"]/groupby/property' );
        
        foreach( $liste as $group )
        {
            $table = '';
            $alias = '';
            $schema = '';
            $ref = '';
            if( $dom->hasAttribute( $group, 'ref' ) )
            {
                $ref = $dom->getAttribute( $group, 'ref' );
                
                $structable = $this->getDefine( $dom->getAttribute( $group, 'ref' ) );

				$table = $structable['orm']->name;
				$field = $structable['field']['name'];
				$schema = $structable['orm']->schema;
                
            }
            $table = $dom->getAttribute( $group, 'table', $table );
            $field = $dom->getAttribute( $group, 'field', $field );
            

			$this->requestcontent[] = '$column = new \damix\engines\orm\request\structure\OrmGroup();';
			$this->requestcontent[] = '$column->setColumnField( ' . $this->quote( $ref ) . ', ' . $this->quote( $table ) . ', ' . $this->quote( $field ) . ' );';
			$this->requestcontent[] = '$request->addGroupBy($column);';
        }
    }
	
	protected function getDefine( $value )
    {
		$define = \damix\engines\orm\defines\OrmDefines::get();
        if( preg_match( '/^{([a-z0-9A-Z_]*)}:([a-z0-9A-Z_]*$)/', $value, $out ) )
        {
            $orm = \damix\engines\orm\Orm::getStructure( $define->get( $out[1] ) );
            
            $field = $orm->getProperty( $out[2] );
            
            return array( 
                'orm' => $orm,
                'field' => $field,
            );
        }
		elseif( preg_match( '/^{([a-z0-9A-Z_]*)}$/', $value, $out ) )
        {            
            $orm = \damix\engines\orm\Orm::getStructure( $define->get( $out[1] ) );
            
            return array( 
                'orm' => $orm,
                'field' => null,
            );
        }
		elseif( preg_match( '/^([a-z0-9A-Z_]*~[a-z0-9A-Z_]*)$/', $value, $out ) )
        {
            $orm = \damix\engines\orm\Orm::getStructure( $out[1]  );
            
            return array( 
                'orm' => $orm,
                'field' => null,
            );
        }
		elseif( preg_match( '/^([a-z0-9A-Z_]*~[a-z0-9A-Z_]*):([a-z0-9A-Z_]*)$/', $value, $out ) )
        {
            $orm = \damix\engines\orm\Orm::getStructure( $out[1]  );
            
            $field = $orm->getProperty( $out[2] );
            
            return array( 
                'orm' => $orm,
                'field' => $field,
            );
        }
        
        return null;
    }
    
}