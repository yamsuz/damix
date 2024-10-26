<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm;

abstract class OrmBaseFactory
{
    protected \damix\engines\orm\request\OrmRequest $_request;
    public \damix\engines\orm\conditions\OrmConditions $_conditions;
    protected array $_groups = array();
    protected array $_orders = array();
    protected array $_limit = array();
    protected array $_properties = array();
    protected array $_table = array();
    protected array $_primarykeys = array();
    protected array $_method = array();
    protected array $_events = array();
    protected string $_schema = '';
    protected \damix\engines\databases\DbConnection $cnx;
		
	public string $module;
    public string $resource;
	
	public function __construct()
    {
		$this->_conditions = new \damix\engines\orm\conditions\OrmConditions();
    }
	
	public function setRequestBase( \damix\engines\orm\request\OrmRequest $request )
    {
        $this->_request = $request;
    }
    
    public function getRequestBase() : \damix\engines\orm\request\OrmRequest
    {
        return $this->_request;
    }
	
    protected function query( string $sql ): ?\damix\engines\databases\DbResultSet
    {
        return $this->cnx->query( $sql );
    }
	
	public function setProfile( string $name ) : void
	{
		$this->cnx = \damix\engines\databases\Db::getConnection($name);
	}
	
	public function getConnection() : \damix\engines\databases\DbConnection
	{
		return $this->cnx;
	}
	
	public function getProperties() : array
	{
		return $this->_properties;
	}
	
	public function getProperty(string $name) : array
	{
		return $this->_properties[$name];
	}
	
	public function getPrimaryKey() : string
	{
		return $this->_primarykeys['name'];
	}
	
	public function getTable() : string
	{
		return $this->_table['name'];
	}
	
	public function getConditions( string $method, string $name = 'default' ) : \damix\engines\orm\conditions\OrmCondition
    {
        $opt = $this->_conditions[ $method ][ $name ] ?? null;
        
        if( $opt === null )
        {
            $opt = new \damix\engines\orm\conditions\OrmCondition( $this->cnx->getDriverName() );
			$this->getEventsMehod( $method, $opt );
        
			if( ! isset( $this->_conditions[ $method ] ) )
			{
				$this->_conditions[ $method ] = new \damix\engines\orm\conditions\OrmConditions();
			}
			
			$this->_conditions[ $method ][ $name ] = $opt;
        }
		
        return $opt;
    }
	
	public function getConditionsAll( string $method ) : \damix\engines\orm\conditions\OrmConditions
    {
        return $this->_conditions[ $method ] ?? new \damix\engines\orm\conditions\OrmConditions();
    }
	
	public function getConditionsClear( string $method, string $name = 'default' ) : \damix\engines\orm\conditions\OrmCondition
    {
        // \damix\engines\logs\log::log( $method . ' ' . $name );
        $c = $this->_conditions[ $method ][ $name ] ?? null;
        if( $c === null )
        {
            $c = new \damix\engines\orm\conditions\OrmCondition();
        }
        
		if( ! isset( $this->_conditions[ $method ] ) )
		{
			$this->_conditions[ $method ] = new \damix\engines\orm\conditions\OrmConditions();
		}
		
        $this->_conditions[ $method ][ $name ] = $c;
		
        $c->clear();
		$this->getEventsMehod( $method, $c );
        return $c;
    }
		
	private function getEventsMehod( string $method, \damix\engines\orm\conditions\OrmCondition $opt ) : void
	{
		if( isset( $this->_method[ $method ][ 'events' ] ) )
		{
			foreach( $this->_method[ $method ][ 'events' ] as $events )
			{
				if( isset( $events['name'] ) )
				{
					\damix\engines\events\Event::notify( 'orm' . $events['name'], array('conditions' => $opt, 'params' => $events));
				}
			}	
		}
	}
	
	public function getGroups(string $method = '' ) : \damix\engines\orm\request\structure\OrmGroups
    {
        $opt = $this->_groups[ $method ] ?? null;
        
        if( $opt === null )
        {
            $opt = new \damix\engines\orm\request\structure\OrmGroups();
        }
        
        $this->_groups[ $method ] = $opt;
        
        return $opt;
    }
	
	public function getOrders( string $method = '' ) : \damix\engines\orm\request\structure\OrmOrders
    {
        $opt = $this->_orders[ $method ] ?? null;
        
        if( $opt === null )
        {
            $opt = new \damix\engines\orm\request\structure\OrmOrders();
        }
        
        $this->_orders[ $method ] = $opt;
        
        return $opt;
    }
	
	public function getOrdersClear( string $method, string $name = 'default' ) : \damix\engines\orm\request\structure\OrmOrders
    {
		$opt = $this->_orders[ $method ] ?? null;
        
        if( $opt === null )
        {
            $opt = new \damix\engines\orm\request\structure\OrmOrders();
        }
        
        $this->_orders[ $method ] = $opt;
        
		$opt->clear();
		
        return $opt;
	}
	
	public function getLimits( string $method = '' ) : \damix\engines\orm\request\structure\OrmLimits
    {
        $opt = $this->_limit[ $method ] ?? null;
        
        if( $opt === null )
        {
            $opt = new \damix\engines\orm\request\structure\OrmLimits();
        }
        
        $this->_limit[ $method ] = $opt;
        
        return $opt;
    }

	public function insert( \damix\engines\orm\OrmBaseRecord $record, bool $ignore = true ) : int
    {
        $insert = new \damix\engines\orm\request\OrmRequestInsert();
		$insert->setSchema( $this->_schema );
		$insert->setTable( $this->_table['name'] );
		$insert->setIgnore( $ignore );
		
		foreach( $this->getEvents('insert', 'before') as $events )
		{
			\damix\engines\events\Event::notify( $events['name'], array( 'factory' => $this, 'record' => $record ));
		}
		
		$val = $insert->newRow();
        foreach( $this->_properties as $name => $property )
        {
            if( $property['insertpattern'] !== null )
            {
                $prop = $insert->getPattern( $property['insertpattern'] );
                $val->addValue( $name, $prop['value'], $prop['datatype'], ($this->_primarykeys['name'] === $name) );
            }
            elseif( $record->isUpdate( $name ) )
            {
                $prop = $record->getProperty( $name );
                if( $prop !== null )
                {
                    $val->addValue( $prop['name'], $prop['value'], \damix\engines\orm\request\structure\OrmDataType::cast( $property['datatype'] ), ($this->_primarykeys['name'] === $name && $property['autoincrement']) );
                }
            }
        }
		$insert->addRow($val);
        
        $out = $insert->executeNonQuery();
        $id = $insert->lastInsertId();
        
        $record->{$this->_primarykeys['name']} = $id;
        
		$record->clearUpdate();
		
		foreach( $this->getEvents('insert', 'after') as $events )
		{
			\damix\engines\events\Event::notify( $events['name'], array( 'factory' => $this, 'record' => $record ));
		}
		
        return $out;
    }
    
	public function getEvents(string $event, string $action) : array
	{
		return $this->_events[$event][$action] ?? array();
	}

    public function update( \damix\engines\orm\OrmBaseRecord $record ) : int
    {
        $obj = new \damix\engines\orm\request\OrmRequestUpdate();
		$obj->setSchema( $this->_schema );
        $obj->setTable( $this->_table['name'] );
       	
		foreach( $this->getEvents('update', 'before') as $events )
		{
			\damix\engines\events\Event::notify( $events['name'], array( 'factory' => $this, 'record' => $record ));
		}
		
        foreach( $this->_properties as $name => $property )
        {
            if( $property['updatepattern'] !== null )
            {
                $prop = $obj->getPattern( $property['updatepattern'] );
                $obj->addValue( $name, $prop['value'], $prop['datatype'], ($this->_primarykeys['name'] === $name) );
            }
            elseif( $record->isUpdate( $name ) )
            {
                $prop = $record->getProperty( $name );
                if( $prop !== null )
                {
                    $obj->addValue( $prop['name'], $prop['value'], \damix\engines\orm\request\structure\OrmDataType::cast( $property['datatype'] ), ($this->_primarykeys['name'] === $name) );
                }
            }
        }
        
        if( isset( $this->_conditions['update'][ 'default' ] ) )
        {
            $obj->setConditions( $this->_conditions['update'][ 'default' ] );
        }
        else
        {
            $c = $obj->getConditions();
            $c->addCondition( $this->_primarykeys['realname'], \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $record->getValue($this->_primarykeys['name']), 'g1', \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR );
        }
		
        $out = $obj->executeNonQuery();
		
		$record->clearUpdate();
		
		foreach( $this->getEvents('update', 'after') as $events )
		{
			\damix\engines\events\Event::notify( $events['name'], array( 'factory' => $this, 'record' => $record ));
		}
        
        return $out;
    }
    
    public function delete( mixed $pk = null ) : int
    {
		$obj = new \damix\engines\orm\request\OrmRequestDelete();
		$obj->setSchema( $this->_schema );
		$obj->setTable( $this->_table['name'] );
		
		foreach( $this->getEvents('delete', 'before') as $events )
		{
			\damix\engines\events\Event::notify( $events['name'], array( 'factory' => $this, 'primarykeys' => $pk ));
		}
		
		if( isset( $this->_conditions['delete'][ 'default' ] ) )
		{
			$obj->setConditions( $this->_conditions['delete'][ 'default' ] );
		}
		else
		{
			$c = $obj->getConditions();
			$c->addCondition( $this->_primarykeys['realname'], \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $pk, 'g1', \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR );
		}
		
		$nb = $obj->executeNonQuery();
		
		foreach( $this->getEvents('delete', 'after') as $events )
		{
			\damix\engines\events\Event::notify( $events['name'], array( 'factory' => $this, 'primarykeys' => $pk ));
		}
		
		return $nb;
    }

	public function createTable(bool $ignore) : bool
	{
		$orm = \damix\engines\orm\Orm::getStructure( $this->module . '~' . $this->resource );
		$table = $orm->getTable();
		$create = new \damix\engines\orm\request\OrmRequestCreate();
		$create->setTable( $table );
		$create->setIgnore( $ignore );
		$create->executeNonQuery();
		foreach( $this->getEvents('create', 'after') as $events )
		{
			\damix\engines\events\Event::notify( $events['name'], array( 'factory' => $this ));
		}
		return true;
	}
	
	public function alterTable() : bool
	{
		$orm = \damix\engines\orm\Orm::getStructure( $this->module . '~' . $this->resource );
		$table = $orm->getTable();
		$alter = new \damix\engines\orm\request\OrmRequestAlter();
		$alter->setTable( $table );
		$liste = $alter->SchemaColonne();
		// \damix\engines\logs\log::dump( $table );
		$OrmProperties = array();
		$BddProperties = array();
		$last = null;
		$i = 1;
		
		foreach( $table->getFields() as $field )
		{
			$OrmProperties[$field->getRealname()] = array(
				'field' => $field,
				'last' => $last,
				'position' => $i,
			);
			$i ++;
			$last = $field->getRealname();
		}
		
		foreach( $liste as $cols )
		{
			$BddProperties[$cols->column_name] = array(
				'cols' => $cols,
			);
		}
		
		foreach( $OrmProperties as $name => $prop )
		{
			$property = $prop['field'];
			
			$update = false;
			
		// \damix\engines\logs\log::dump( $property );
			
			if( isset( $BddProperties[ $name ] ) )
			{
				$update |= $this->checkPrimaryKey($property, $BddProperties[ $name ]['cols']);
				$update |= $this->checkDatatype($property, $BddProperties[ $name ]['cols']);
				$update |= $this->checkPosition($property, $BddProperties[ $name ]['cols']);
				$update |= $this->checkNull($property, $BddProperties[ $name ]['cols']);
				$update |= $this->checkAutoIncrement($property, $BddProperties[ $name ]['cols']);
				
				if( $update )
				{
					$alter->fieldModify($property, $prop['last']);
				}
			}
			else
			{
				$alter->fieldAdd($property, $prop['last']);
			}
			
		}
		
		$alter->executeNonQuery();
		
		
		return true;
	}
		
	private function checkPosition(\damix\engines\orm\request\structure\OrmField $ormfield, object $obj) : bool
	{
		if( intval($ormfield->getPosition() + 1) !== intval($obj->ordinal_position) )
		{
			return true;
		}
		return false;
	}
	
	private function checkPrimaryKey(\damix\engines\orm\request\structure\OrmField $ormfield, object $obj) : bool
	{
		if( $ormfield->getPrimaryKey() !== ($obj->column_key === 'PRI'))
		{
			\damix\engines\logs\log::log( __LINE__ . ' ' . $ormfield->getName() );
			return true;
		}
		return false;
	}
	
	private function checkAutoIncrement(\damix\engines\orm\request\structure\OrmField $ormfield, object $obj) : bool
	{
		if( $ormfield->getAutoincrement() !== ($obj->extra === 'auto_increment'))
		{
			\damix\engines\logs\log::log( __LINE__ . ' ' . $ormfield->getName() );
			return true;
		}
		return false;
	}
	
	private function checkDatatype(\damix\engines\orm\request\structure\OrmField $ormfield, object $obj) : bool
	{
		$driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();
		$datatype = $driver->getFieldDatatype($ormfield);
		
		if( $datatype !== $obj->column_type )
		{
			return true;
		}
		return false;
	}

	private function checkNull(\damix\engines\orm\request\structure\OrmField $ormfield, object $obj) : bool
	{
		$isnull = match( $obj->is_nullable ){
						'YES' => true,
						'NO' => false,
					};
		if( $ormfield->getNull() !== $isnull )
		{
			\damix\engines\logs\log::log( __LINE__ . ' ' . $ormfield->getName() );
			return true;
		}
		return false;
	}
	
	public function alterIndexTable(bool $ignore) : bool
	{
		$orm = \damix\engines\orm\Orm::getStructure( $this->module . '~' . $this->resource );
		$table = $orm->getTable();
		$alter = new \damix\engines\orm\request\ormrequestalter();
		$alter->setTable( $table );

		$indexes = $orm->getIndexes();
		
		foreach( $indexes as $index )
		{
			$ormindex = new \damix\engines\orm\request\structure\OrmIndex();
			$ormindex->setName( $index['realname'] );
			$ormindex->setIgnore($ignore);
			$ormindex->setIndexType( \damix\engines\orm\request\structure\OrmIndexType::cast($index['type']) );
			foreach( $index['field'] as $field)
			{
				$ormfield = new \damix\engines\orm\request\structure\OrmField();
				$ormfield->setName($field[ 'name' ]);
				$ormindex->addField( $ormfield, \damix\engines\orm\request\structure\OrmOrderWay::cast($field[ 'way' ]) );
			}
			
			$alter->IndexAdd( $ormindex );
			
		}
		$alter->executeNonQuery();
		
		return true;
	}
	
	public function alterConstraintTable(bool $ignore) : bool
	{
		$orm = \damix\engines\orm\Orm::getStructure( $this->module . '~' . $this->resource );
		$table = $orm->getTable();
		$alter = new \damix\engines\orm\request\ormrequestalter();
		$alter->setTable( $table );

		$foreignkeys = $orm->getForeignKeys();
		
		foreach( $foreignkeys as $foreignkey )
		{
			$contraint = new \damix\engines\orm\request\structure\OrmContraint();
			$contraint->setName( $foreignkey['realname'] );
			$contraint->setIgnore($ignore);

			$fieldforeign = new \damix\engines\orm\request\structure\OrmField();
			$fieldforeign->setTable( $table );
			$fieldforeign->setName( $foreignkey[ 'property' ]);
			$contraint->setForeign( $fieldforeign );
			
			$fieldreference = new \damix\engines\orm\request\structure\OrmField();
			$ormReference = \damix\engines\orm\Orm::getStructure( $foreignkey[ 'reference' ]['orm'] );
			$prop = $ormReference->getProperty($foreignkey[ 'reference' ]['property']);
			
			$fieldreference->setTable( $ormReference->getTable() );
			$fieldreference->setName( $prop[ 'realname' ]);
			$contraint->setReference( $fieldreference );
			
			$contraint->setDelete( \damix\engines\orm\request\structure\OrmContraintType::cast( $foreignkey['delete'] ?? '' ) );
			$contraint->setUpdate( \damix\engines\orm\request\structure\OrmContraintType::cast( $foreignkey['update'] ?? '' ) );
			$alter->ContraintAdd( $contraint );
		
		}
		$alter->executeNonQuery();
		
		return true;
	}
	
	public function dropTable() : bool
	{		
		$orm = \damix\engines\orm\Orm::getStructure( $this->module . '~' . $this->resource );
		$table = $orm->getTable();
		
		$drop = new \damix\engines\orm\request\OrmRequestDrop();
		$drop->setTable($table);
		$drop->executeNonQuery();
		
		return true;
	}
	
	public function dropForeignKey(bool $ignore) : bool
	{
		$orm = \damix\engines\orm\Orm::getStructure( $this->module . '~' . $this->resource );
		$table = $orm->getTable();
		
		$alter = new \damix\engines\orm\request\ormrequestalter();
		$alter->setTable( $table );
		$foreignkeys = $orm->getForeignKeys();
		foreach( $foreignkeys as $foreignkey )
		{
			$contraint = new \damix\engines\orm\request\structure\OrmContraint();
			$contraint->setName( $foreignkey['realname'] );
			$contraint->setIgnore($ignore);
			$alter->ContraintRemove( $contraint );
		}
		$alter->executeNonQuery();
		
		return true;
	}
	
	public function dropIndex(bool $ignore) : bool
	{
		$orm = \damix\engines\orm\Orm::getStructure( $this->module . '~' . $this->resource );
		$table = $orm->getTable();
		
		$alter = new \damix\engines\orm\request\ormrequestalter();
		$alter->setTable( $table );
		$indexes = $orm->getIndexes();
		foreach( $indexes as $index )
		{
			$ormindex = new \damix\engines\orm\request\structure\OrmIndex();
			$ormindex->setName( $index['realname'] );
			$ormindex->setIgnore($ignore);
			$alter->IndexRemove( $ormindex );
		}
		$alter->executeNonQuery();
		
		return true;
	}

	public function createTrigger() : bool
	{
		$orm = \damix\engines\orm\Orm::getStructure( $this->module . '~' . $this->resource );
		$table = $orm->getTable();
		$triggers = $orm->getTriggers();
		
		$sel = new \damix\engines\orm\stored\OrmStoredSelector(\damix\engines\orm\stored\OrmStoredSelector::STORAGE_TRIGGER);
		
		foreach( $triggers as $trigger )
		{
			$obj = new \damix\engines\orm\request\OrmRequestStored();
			$obj->setName( $trigger['name'] );
			$obj->setAction( $trigger['action'] );
			$obj->setEvent( $trigger['event'] );
			$obj->setTable( $table );
			
			$obj->setContent( file_get_contents( $sel->getPathContent( $trigger['name'] ) ) );

			$obj->setSqlType(\damix\engines\orm\request\structure\OrmStructureType::SQL_TRIGGER_STORED_DELETE);
			$obj->executeNonQuery();
			$obj->setSqlType(\damix\engines\orm\request\structure\OrmStructureType::SQL_TRIGGER_STORED_CREATE);
			$obj->executeNonQuery();
			// \damix\engines\logs\log::dump( $obj );
		}
		
		return true;
	}
	
	public function createRecord() : \damix\engines\orm\OrmBaseRecord
	{
		throw new \damix\core\exception\CoreException( 'Record unknow' ); 
	}
}