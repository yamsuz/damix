<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\drivers;


abstract class OrmDriversBase    
{
	protected \damix\engines\orm\request\OrmRequest $request;
	protected bool $bddDisplay = true;
	protected bool $alterAfter = true;
	protected string $symboleLike = '%';
	public ?\damix\engines\databases\DbConnection $_cnx = null;
	static protected $_singletonfunction=array();
	private \damix\engines\orm\method\OrmMethodSelector $selector;
	
	abstract public function isSchema() : bool;
	abstract public function isCaseManagement() : bool;
	abstract protected function getRequestSQLCreateProcedureHeader(\damix\engines\orm\request\OrmRequestStored $stored) : string;
	abstract protected function getRequestSQLCreateFunctionHeader(\damix\engines\orm\request\OrmRequestStored $stored) : string;
	abstract protected function getRequestSQLCreateTriggerHeader(\damix\engines\orm\request\OrmRequestStored $stored) : string;

	public bool $delimiter = true;
	
	public function __construct(string $driver)
	{
		$this->_cnx = \damix\engines\databases\Db::getConnection( $driver );
	}
	
	public function getSQL( \damix\engines\orm\request\OrmRequest $request ) : string
	{
		$this->request = $request;
		
		$sql = match ( $request->getSQLType() )
		{
			\damix\engines\orm\request\structure\OrmStructureType::SQL_SELECT => $this->getRequestSQLSelect(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_INSERT => $this->getRequestSQLInsert(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_UPDATE => $this->getRequestSQLUpdate(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_DELETE => $this->getRequestSQLDelete(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_CREATE => $this->getRequestSQLCreate(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_ALTER => $this->getRequestSQLAlter(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_DROP => $this->getRequestSQLDrop(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_FUNCTION_STORED_CREATE => $this->getRequestSQLCreateFunction(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_FUNCTION_STORED_DELETE => $this->getRequestSQLDeleteFunction(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_PROCEDURE_STORED_CREATE => $this->getRequestSQLCreateProcedure(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_PROCEDURE_STORED_DELETE => $this->getRequestSQLDeleteProcedure(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_EVENT_STORED_CREATE => $this->getRequestSQLCreateEvent(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_EVENT_STORED_DELETE => $this->getRequestSQLDeleteEvent(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_TRIGGER_STORED_CREATE => $this->getRequestSQLCreateTrigger(),
			\damix\engines\orm\request\structure\OrmStructureType::SQL_TRIGGER_STORED_DELETE => $this->getRequestSQLDeleteTrigger(),
		};
		
		// \damix\engines\logs\log::log( __LINE__ . ' ' . $sql );
		
		return $sql;
	}
	
	public function execute( \damix\engines\orm\request\OrmRequest $request ) : ?\damix\engines\databases\DbResultSet
	{
		$sql = $this->getSQL($request);
		
		if( ! empty( $sql ) )
		{
			return $this->_cnx->query( $sql );
		}
		
		return null;
	}
	
	public function executeNonQuery( \damix\engines\orm\request\OrmRequest $request ) : int|string
	{
		$sql = $this->getSQL($request);
		
		if( ! empty( $sql ) )
		{
			return $this->_cnx->executeNonQuery( $sql );
		}
		
		return 0;
	}
	
	public function getRequestSelect( \damix\engines\orm\method\OrmMethodSelector $selector, string $conditionname = 'default' ) : string
    {
		// \damix\engines\logs\log::dump( $selector );
		
		$this->selector = $selector;
        $_factory = $selector->getFactory();
        $this->request = $_factory->getRequestBase();
        $function = $selector->getPart( 'function' );
		// \damix\engines\logs\log::dump( $_factory->getRequestBase() );
		// \damix\engines\logs\log::dump( $_factory->getOrders( $function ) );
		        
        $out = array();
		
		$out[] = 'SELECT';
		$out[] = $this->getSelect();
		$out[] = 'FROM';
		$out[] = $this->getFrom();

		$where = $this->getConditionsTemp( $_factory->getConditions( $function ), $conditionname );
		if( ! empty( $where ) )
		{
			$out[] = 'WHERE';
			$out[] = $where;
		}
		$groupby = $this->getGroups();
		if( ! empty( $groupby ) )
		{
			$out[] = 'GROUP BY';
			$out[] = $groupby;
		}
		$having = $this->getConditionsTemp( $this->request->getHaving(), $conditionname );
		if( ! empty( $having ) )
		{
			$out[] = 'HAVING';
			$out[] = $having;
		}
		$orderby = $this->getOrderBy( $_factory->getOrders( $function ) );
		if( ! empty( $orderby ) )
		{
			$out[] = 'ORDER BY';
			$out[] = $orderby;
		}
		$limit = $this->getLimit( $_factory->getLimits($function));
		if( ! empty( $limit ) )
		{
			$out[] = 'LIMIT';
			$out[] = $limit;
		}
		
		return implode(' ', $out ) . $this->getDelimiter();
    }
	
	public function getRequestProcedure( string $schemaname, string $name, array $params ) : string
    {
        $out = 'CALL ' . $this->getRequestStorage( $schemaname, $name, $params ) . $this->getDelimiter();

        return $out;
    }
    
    public function getRequestFunction( string $schemaname, string $name, array $params, string $alias ) : string
    {
        $out = 'SELECT ' . $this->getRequestStorage( $schemaname, $name, $params ) . ' AS ' . $alias . $this->getDelimiter();

        return $out;
    }
    
	protected function getRequestStorage( string $schemaname, string $name, array $params ) : string
    {
        $out = array();
        $out[] = ($this->isSchema() && !empty($schemaname)? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . $name;
        $out[] = '(';
        $outparams = array();
        foreach( $params as $field )
        {
			$outparams[] = $this->getValue( $field['value'], $field['type']  );
        }
        $out[] = implode( ', ', $outparams );
        $out[] = ')';
        return implode( ' ', $out );
    }
	
	public function getRequestSQLDeleteFunction() : string
    {
		$stored = $this->request;
		
		$schemaname = $stored->getSchema()?->getRealname();
		
		return 'DROP FUNCTION IF EXISTS ' . ($this->isSchema() && !empty($schemaname)? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector() . $this->getDelimiter();
    }
	
	public function getRequestSQLCreateFunction() : string
    {
		$stored = $this->request;
		
        $sql = array();
        
		$sql[] = $this->getRequestSQLCreateFunctionHeader($stored);
        $sql[] = $stored->getContent();
        
        
        $sql = implode("\n", $sql );
        return $sql;
    }
	
	public function getRequestSQLDeleteProcedure() : string
    {
		$stored = $this->request;
		
		$schemaname = '';
		
		if( $this->isSchema() )
		{
			$schemaname = $stored->getSchema()?->getRealname();
		}
				
		return 'DROP PROCEDURE IF EXISTS ' . ($this->isSchema() && !empty($schemaname)? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector() . $this->getDelimiter();
    }
	
	public function getRequestSQLCreateProcedure() : string
    {
		$stored = $this->request;
		
        $sql = array();
        $sqlparams = array();
        
		$sql[] = $this->getRequestSQLCreateProcedureHeader($stored);
        
        $sql[] = $stored->getContent();
        
        $sql = implode("\n", $sql );
        return $sql;
    }
	
	public function getRequestSQLDeleteEvent() : string
    {
		$stored = $this->request;
		
		return 'DROP EVENT IF EXISTS ' . $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector() . $this->getDelimiter();
    }
	
	public function getRequestSQLCreateEvent() : string
    {
		$stored = $this->request;
		
        $sql = array();
        
        $intervalunite = '';
        
        switch( strtoupper( $stored->getIntervalUnite() ) )
        {
            case 'YEAR':
            case 'QUARTER':
            case 'MONTH':
            case 'DAY':
            case 'HOUR':
            case 'MINUTE':
            case 'WEEK':
            case 'SECOND':
            case 'MICROSECOND':
            case 'YEAR_MONTH':
            case 'DAY_HOUR':
            case 'DAY_MINUTE':
            case 'DAY_SECOND':
            case 'HOUR_MINUTE':
            case 'HOUR_SECOND':
            case 'MINUTE_SECOND':
            case 'DAY_MICROSECOND':
            case 'HOUR_MICROSECOND':
            case 'MINUTE_MICROSECOND':
            case 'SECOND_MICROSECOND':
                $intervalunite = strtoupper( $stored->getIntervalUnite() );
                break;
        }
        
        $sql[] = 'CREATE EVENT `'. $stored->getName() .'`';
        $sql[] = 'ON SCHEDULE EVERY '. $stored->getIntervalValeur() .' '. $intervalunite .' STARTS NOW()';
        $sql[] = 'ON COMPLETION NOT PRESERVE ENABLE DO ';
        $sql[] = 'BEGIN';
        $sql[] = $stored->getContent();
        $sql[] = 'END';
        
        $sql = implode("\n", $sql );
        return $sql;
    }
	
	public function getRequestSQLDeleteTrigger() : string
    {
		$stored = $this->request;
		
		$schemaname = '';
		
		if( $this->isSchema() )
		{
			$schemaname = $stored->getSchema()?->getRealname();
		}
		
		return 'DROP TRIGGER IF EXISTS ' . ($this->isSchema() && !empty($schemaname)? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector() . $this->getDelimiter();
    }
		
	public function getRequestSQLCreateTrigger() : string
    {
		$stored = $this->request;
		
		$sql[] = $this->getRequestSQLCreateTriggerHeader($stored);
        $sql[] = $stored->getContent();

        
        $sql = implode("\n", $sql );
        return $sql;
    }
	
	protected function getRequestSQLSelect() : string
	{
		$conditions = $this->request->getConditions();
		
        $out = array();
		
		$out[] = 'SELECT';
		$out[] = $this->getSelect();
		$out[] = 'FROM';
		$out[] = $this->getFrom();
		$where = $this->getConditions( $conditions );
		if( ! empty( $where ) )
		{
			$out[] = 'WHERE';
			$out[] = $where;
		}
		$groupby = $this->getGroups();
		if( ! empty( $groupby ) )
		{
			$out[] = 'GROUP BY';
			$out[] = $groupby;
		}
		$having = $this->getConditions( $this->request->getHaving() );
		if( ! empty( $having ) )
		{
			$out[] = 'HAVING';
			$out[] = $having;
		}
		$orderby = $this->getOrderBy($this->request->getOrderBy());
		if( ! empty( $orderby ) )
		{
			$out[] = 'ORDER BY';
			$out[] = $orderby;
		}
		
		$limit = $this->getLimit($this->request->getLimits());
		if( ! empty( $limit ) )
		{
			$out[] = 'LIMIT';
			$out[] = $limit;
		}
		
		return implode(' ', $out ) . $this->getDelimiter();
	}
	
	protected function getRequestSQLCreate() : string
	{
		$table = $this->request->getTable();
		$fields = $table->getFields();
		
		$out = array();
		$pk = array();
				
		$out[] = 'CREATE TABLE ' . ($this->request->getIgnore() ? 'IF NOT EXISTS ' : '') . $this->getTableName($table);
        $out[] = '(';
		
		$outField = array();

		foreach( $fields as $field )
		{
			$outField[] = $this->getRequestStructureField( $field );
			if( $field->getPrimaryKey() ) 
			{
				$pk[] =  $this->getFieldProtector() . $field->getRealname() . $this->getFieldProtector();
			}
		}
				// \damix\engines\logs\log::dump( $pk  );

		if( count( $pk ) > 0 )
		{
			$outField[] =  'PRIMARY KEY (' . implode( ', ', $pk) . ')';
		}
		$out[] =  implode( ', ', $outField );
        $out[] = ')';
		$out[] = $this->getRequestSQLCreateOption($table);
		
		
		return implode(' ', $out ) . $this->getDelimiter();
	}
	
	protected function getRequestSQLAlterFieldStructure(array $field) : string
	{
		$after = $field['after'];
		switch( $field['type'] )
		{
			case 'add':
				return 'ADD ' . $this->getRequestStructureField( $field['field'] ) . ( $this->alterAfter && $after ? ' AFTER ' . $this->getFieldProtector() . $after . $this->getFieldProtector() : '');
				break;
			case 'modify':
				return 'MODIFY ' . $this->getRequestStructureField( $field['field'] ) . ( $this->alterAfter && $after ? ' AFTER ' . $this->getFieldProtector() . $after . $this->getFieldProtector() : '');
				break;
			case 'delete':
				return 'DROP ' . $this->getFieldProtector() . $field['field']->getRealname() . $this->getFieldProtector();
				break;
		}
		
		return '';
	}
	
	protected function getRequestSQLAlterIndexStructure(array $index) : string
	{
		
		switch( $index['type'] )
		{
			case 'add':
				return 'ADD ' . $this->getIndexStructure( $index['index'] );
				break;
			case 'delete':
				return 'DROP INDEX ' . ( $index['index']->getIgnore() ? 'IF EXISTS ' : '') . $index['index']->getName();
				break;
		}
		
		return '';
	}
	
	protected function getRequestSQLAlterFields() : string
	{
		$table = $this->request->getTable();
		$fields = $this->request->getFields();
		
		$out = array();
		$outField = array();
		
		$out[] = 'ALTER TABLE ' . $this->getTableName($table);
    
		foreach( $fields as $field )
        {
			$sql = $this->getRequestSQLAlterFieldStructure($field);
			if( ! empty( $sql ) )
			{
				$outField[] = $sql;
			}
        }
		
		if( count( $outField ) == 0 )
		{
			return '';
		}
		$out[] =  implode( ', ', $outField );
		return implode(' ', $out ) . $this->getDelimiter();
	}
	
	protected function getRequestSQLAlterIndexes() : string
	{
		$table = $this->request->getTable();
		$indexes = $this->request->getIndexes();
		
		$out = array();
		$outField = array();
		
		$out[] = 'ALTER TABLE ' . $this->getTableName($table);
    
		foreach( $indexes as $index )
        {
			$sql = $this->getRequestSQLAlterIndexStructure($index);
			if( ! empty( $sql ) )
			{
				$outField[] = $sql;
			}
        }
		
		if( count( $outField ) == 0 )
		{
			return '';
		}
		$out[] =  implode( ', ', $outField );
		return implode(' ', $out ) . $this->getDelimiter();
	}
	
	protected function getRequestSQLAlterConstraints() : string
	{
		$table = $this->request->getTable();
		$constraints = $this->request->getConstraints();
		
		$out = array();
		$outField = array();
		
		$out[] = 'ALTER TABLE ' . $this->getTableName($table);
    
		foreach( $constraints as $constraint )
        {
            switch( $constraint['type'] )
            {
                case 'add':
                    $outField[] = 'ADD ' . $this->getConstraintsStructure( $constraint['constraint'] );
                    break;
                case 'delete':
                    $outField[] = 'DROP CONSTRAINT ' . ( $constraint['constraint']->getIgnore() ? 'IF EXISTS ' : '') . $this->getFieldProtector() . $constraint['constraint']->getName() . $this->getFieldProtector();
                    break;
            }
        }
		
		
		if( count( $outField ) == 0 )
		{
			return '';
		}
		$out[] =  implode( ', ', $outField );
		return implode(' ', $out ) . $this->getDelimiter();
	}
	
	protected function getRequestSQLAlter() : string
	{
		$out = array();
		
		$out[] = $this->getRequestSQLAlterFields();
		$out[] = $this->getRequestSQLAlterIndexes();
		$out[] = $this->getRequestSQLAlterConstraints();
		
		return implode('', $out );
	}
	
	protected function getRequestSQLDrop() : string
	{
		$table = $this->request->getTable();
		
		$out = array();
		
		$out[] = 'DROP TABLE IF EXISTS ' . $this->getTableName($table);
        
		return implode(' ', $out ) . $this->getDelimiter();
	}
	
	protected function getRequestSQLInsert() : string
	{
		$table = $this->request->getTable();
		$rows = $this->request->getRows();
		$fields = null;
		$out = array();
		$outfield = array();
		$values = array();
		
		$out[] = 'INSERT';
		if( $this->request->getIgnore() )
		{
			$out[] = 'IGNORE';
		}
		$out[] = 'INTO ' . $this->getTableName($table);
		
		foreach( $rows as $row )
		{
			if( ! $fields )
			{
				$fields = $row->getFields();
			}
			$outvalue = array();
			foreach( $fields as $field )
			{
				$data = $row->getValue( $field['name'] );
				$outvalue[] =  $this->getValue( ( $data['isPrimaryKey'] ? null : $data['value'] ), $field['datatype'] );
			}
			
			$values[] = '(' . implode(', ', $outvalue ) . ')';
		}
		
		foreach( $fields as $field )
		{
			$outfield[] = $this->getFieldName( $field['name'] );
		}
        
		$out[] = '(';
		$out[] = implode( ', ', $outfield );
		$out[] = ')';
		$out[] = 'VALUES';
		$out[] = implode( ', ', $values );
		
		return implode(' ', $out ) . $this->getDelimiter();
	}
	
	protected function getRequestSQLUpdate() : string
	{
		$table = $this->request->getTable();
		$row = $this->request->getRow();
		$conditions = $this->request->getConditions();
		
		$out = array();
		
		$values = array();
		
		$out[] = 'UPDATE';
		$out[] = $this->getTableName($table);
		$fields = $row->getFields();
		
		$outvalue = array();
		
		foreach( $fields as $field )
		{
			$data = $row->getValue( $field['name'] );
			$outvalue[] =  $this->getFieldName($field['name']) . ' = ' . $this->getValue( $data['value'], $data['datatype'], \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ );
		}
		
		$out[] = 'SET';
		if( count( $outvalue ) == 0)
		{
			return '';
		}
		$out[] = implode( ', ', $outvalue);
		$out[] = 'WHERE';
		$out[] = $this->getConditions( $conditions );
		return implode(' ', $out ) . $this->getDelimiter();
	}
		
	protected function getRequestSQLDelete() : string
	{
		$table = $this->request->getTable();
		$conditions = $this->request->getConditions();
		
		$out = array();
		
		$out[] = 'DELETE FROM';
		$out[] = $this->getTableName($table);
		
		$out[] = 'WHERE';
		$out[] = $this->getConditions( $conditions );
		return implode(' ', $out ) . $this->getDelimiter();
	}
	
	protected function getIndexStructure(\damix\engines\orm\request\structure\OrmIndex $index) : string
	{
		$out = array();
		$outindex = array();
		switch( $index->getIndexType() )
		{
			case \damix\engines\orm\request\structure\OrmIndexType::ORM_INDEX;
				$out[] = 'INDEX';
				break;
			case \damix\engines\orm\request\structure\OrmIndexType::ORM_UNIQUE;
				$out[] = 'UNIQUE';
				break;
			case \damix\engines\orm\request\structure\OrmIndexType::ORM_SPATIAL;
				$out[] = 'SPATIAL';
				break;
			case \damix\engines\orm\request\structure\OrmIndexType::ORM_FULLTEXT;
				$out[] = 'FULLTEXT';
				break;
		}
		
		if( $index->getIgnore() )
		{
			$out[] = 'IF NOT EXISTS';
		}
		$out[] = $this->getFieldProtector() . $index->getName() . $this->getFieldProtector();
		$out[] = '(';
		foreach( $index->getFields() as $field )
		{
			$way = match($field['way']) {
					\damix\engines\orm\request\structure\OrmOrderWay::WAY_ASC => 'asc',
					\damix\engines\orm\request\structure\OrmOrderWay::WAY_DESC => 'desc',
			};
			$outindex[] = $this->getFieldName($field['field']->getRealname()) . ( $field['length'] > 0 ? ' (' . $field['length'] . ')' : '' ) . ' ' . $way;
		}
		$out[] = implode( ', ', $outindex );
		$out[] = ')';
		
		return implode( ' ', $out );
	}
	
	protected function getConstraintsStructure(\damix\engines\orm\request\structure\OrmContraint $constraint) : string
	{
		$out = array();
		$outindex = array();
		
		$out[] = 'CONSTRAINT';
		$out[] = $this->getFieldProtector() . $constraint->getName() . $this->getFieldProtector();
		$out[] = 'FOREIGN KEY';
		// if( $constraint->getIgnore() )
		// {
			// $out[] = 'IF NOT EXISTS';
		// }
		$out[] = '(' . $this->getFieldProtector() . $constraint->getForeign()->getRealname() . $this->getFieldProtector() . ')';
		$out[] = 'REFERENCES';
		$out[] = $this->getTableName( $constraint->getReference()->getTable() );
		$out[] = '(' . $this->getFieldProtector() . $constraint->getReference()->getRealname() . $this->getFieldProtector() . ')';
		
		$update = match($constraint->getUpdate()) {
				\damix\engines\orm\request\structure\OrmContraintType::ORM_CASCADE => 'ON UPDATE CASCADE',
				\damix\engines\orm\request\structure\OrmContraintType::ORM_SETNULL => 'ON UPDATE SET NULL',
				\damix\engines\orm\request\structure\OrmContraintType::ORM_NOACTION => 'ON UPDATE NO ACTION',
				\damix\engines\orm\request\structure\OrmContraintType::ORM_RESTRICT => 'ON UPDATE RESTRICT',
				default => '',
		};

		$delete = match($constraint->getDelete()) {
				\damix\engines\orm\request\structure\OrmContraintType::ORM_CASCADE => 'ON DELETE CASCADE',
				\damix\engines\orm\request\structure\OrmContraintType::ORM_SETNULL => 'ON DELETE SET NULL',
				\damix\engines\orm\request\structure\OrmContraintType::ORM_NOACTION => 'ON DELETE NO ACTION',
				\damix\engines\orm\request\structure\OrmContraintType::ORM_RESTRICT => 'ON DELETE RESTRICT',
				default => '',
		};
		
		if( ! empty( $update ) )
		{
			$out[] = $update;
		}
		if( ! empty( $delete ) )
		{
			$out[] = $delete;
		}
		
		return implode( ' ', $out );
	}
	
    protected function getRequestSQLCreateOption(\damix\engines\orm\request\structure\OrmTable $table) : string
	{
		return '';
	}
	
	public function getFieldName(\damix\engines\orm\request\structure\OrmField|string|array $field)
	{
		if( is_string( $field ) )
		{
			if( $struct = \damix\engines\orm\Orm::getDefine( $field ))
			{
				$field = $struct['field'];

				if( $field )
				{
					return $this->getFieldProtector() . $struct['orm']->realname . $this->getFieldProtector() . '.' . $this->getFieldProtector()  . $field['realname'] . $this->getFieldProtector() ;
				}
			}
			return $this->getFieldProtector() . $field . $this->getFieldProtector();
		}
		elseif( is_array( $field ) )
		{
			return ( ! empty( $field['table'] ) ? $this->getFieldProtector() . $field['table'] . $this->getFieldProtector() . '.' : '' ) . $this->getFieldProtector() . $field['field'] . $this->getFieldProtector();
		}
		else
		{
			
			$tablename = '';
			$table = $field->getTable();
			if( $table )
			{
				$tablename = $this->getTableName( $table, false );
			}
			
			return ( ! empty( $tablename ) ? $tablename . '.' : '' ) . $this->getFieldProtector() . $field->getRealname() . $this->getFieldProtector();
		}
	}
	
	protected function getRequestStructureField(\damix\engines\orm\request\structure\OrmField $field) : string
	{
		$out = array();
		
		$out[] = $this->getFieldProtector() . $field->getRealname() . $this->getFieldProtector();
		$out[] = $this->getFieldDatatype($field);
		
		if( ! $field->getPrimaryKey() && ! $field->getAutoincrement())
		{
			$out[] = ($field->getNull() ? 'NULL' : 'NOT NULL');
			
			$default = $field->getDefault();
			if( empty( $default ) )
			{
				if( $default === null )
				{
					if( $field->getNull() )
					{
						$out[] = 'DEFAULT ' . $this->getValue( null );
					}
				}
				else
				{
					$out[] = 'DEFAULT ' . $this->getValue( '' );
				}
			}
			else
			{
				$out[] = 'DEFAULT ' . (strtolower( $default ) == 'null' || $default === null ? $this->getValue( null ) : $this->getValue( $default, $field->getDatatype() ) );
			}
		}
		
		if( $field->getAutoincrement() )
		{
			$out[] = 'AUTO_INCREMENT';
		}
		
		return implode(' ', $out );
	}
	
	public function getPropertyRealName( \damix\engines\orm\request\structure\OrmField $field ) : string
	{
		$tablename = $field->getTable()?->getRealname();
		$schemaname = $field->getTable()?->getSchema()?->getRealname();
		
		return ($this->isSchema() && !empty($schemaname)? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . (!empty($tablename)? $this->getFieldProtector() . $tablename . $this->getFieldProtector() . '.' : '' ) . $this->getFieldName($field);
	}
	
	protected function getTableName(\damix\engines\orm\request\structure\OrmTable $table, bool $bddDisplay = true) : string
	{
		$tablename = $table->getRealname();
		$schemaname = $table->getSchema()?->getRealname();
		
		if( empty( $tablename) )
		{
			return '';
		}
		
		if( $table->isInternal() )
		{
			return $this->getFieldProtector() . $table->getInternal() . $this->getFieldProtector() . '.' . $this->getFieldProtector() . $tablename . $this->getFieldProtector();
		}
		
		return ($this->bddDisplay && $table->isReference() && $bddDisplay ? $this->getFieldProtector() . $this->_cnx->getDatabase() . $this->getFieldProtector() . '.' : '') . ($this->isSchema() && !empty($schemaname)? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . $this->getFieldProtector() . $tablename . $this->getFieldProtector();
	}
	
	protected function getFieldProtector() : string
	{
		return '`';
	}
	
	protected function getDelimiter() : string
	{
		return ($this->delimiter ? ';' : '');
	}
	
	protected function getInstructionAlias() : string
	{
		return 'AS';
	}
	
	private function getSelect() : string
    {
        $out = array();

        $displays = $this->request->getDisplay();
        foreach( $displays as $display )
        {
            switch( $display->getColumnType() )
            {
                case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_FIELD :
					$field = $display->getField();
                    $out[] = $this->getFieldName( $field ) . ' ' . $this->getInstructionAlias() . ' ' . $this->getFieldProtector() . $display->getAlias() . $this->getFieldProtector();
                    break;
                case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_FORMULA :
					$formula = $display->getFormula();
                    $obj = OrmDriversBase::getDriverFunction( $this->_cnx->getDriver(), $formula->getName() );
                    if( $obj !== null )
                    {
						$obj->driver = $this;
                        $out[] = $obj->execute( $formula ) . ' ' . $this->getInstructionAlias() . ' ' . $this->getFieldProtector() . $display->getAlias() . $this->getFieldProtector();
                    }
                    break;
            }
        }
        
        return implode( ', ', $out) ;
    }
	
	private function getFrom() : string
	{
		$out = array();
		
		$joins = $this->request->getJoins();
		
		foreach( $joins as $join )
        {
            switch( $join->type )
            {
                case 'from':
                    $schemaname = $join->getTable()?->getSchema()?->getRealname();
                    $table = $join->getTable();
			
					$out[] = $this->getTableName($table) . ' ' . $this->getInstructionAlias() . ' ' . $this->getFieldProtector() . $join->getAlias() . $this->getFieldProtector();
					continue 2;
                    break;
                case 'join':
                    $out[] = 'JOIN';
					$out[] = $this->getTableName( $join->table ) . ' ' . $this->getInstructionAlias() . ' ' . $this->getFieldProtector() . $join->alias . $this->getFieldProtector() . ' ON';
                    break;
                case 'left':
                    $out[] = 'LEFT JOIN';
					$out[] = $this->getTableName( $join->table ) . ' ' . $this->getInstructionAlias() . ' ' . $this->getFieldProtector() . $join->alias . $this->getFieldProtector() . ' ON';
                    break;
                case 'inner':
                    $out[] = 'INNER JOIN';
					$out[] = $this->getTableName( $join->table ) . ' ' . $this->getInstructionAlias() . ' ' . $this->getFieldProtector() . $join->alias . $this->getFieldProtector() . ' ON';
                    break;
                case 'subfrom':
					$sql = $this->getFromSubrequest( $join );
					$out[] = '(' . $sql . ')' . ' ' . $this->getInstructionAlias() . ' ' . $this->getFieldProtector() . $join->alias . $this->getFieldProtector();
					break;
                case 'subjoin':
					$sql = $this->getFromSubrequest( $join );
					$out[] = 'JOIN';
					$out[] = '(' . $sql . ')' . ' ' . $this->getInstructionAlias() . ' ' . $this->getFieldProtector() . $join->alias . $this->getFieldProtector() . ' ON';
					break;
                case 'subleft':
					$sql = $this->getFromSubrequest( $join );
					$out[] = 'LEFT JOIN';
					$out[] = '(' . $sql . ')' . ' ' . $this->getInstructionAlias() . ' ' . $this->getFieldProtector() . $join->alias . $this->getFieldProtector() . ' ON';
                    break;
                case 'subinner':
					$sql = $this->getFromSubrequest( $join );
					$out[] = 'INNER JOIN';
					$out[] = '(' . $sql . ')' . ' ' . $this->getInstructionAlias() . ' ' . $this->getFieldProtector() . $join->alias . $this->getFieldProtector() . ' ON';
                    break;
            }
            
            foreach( $join->conditions as $cond )
            {
                switch( $cond['type'] )
                {
                    case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD:
                        switch( $cond['condition']->getDataType() )
                        {
                            case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_FIELD:
                                
                                $out[] = $this->getFieldName(array( 'table' => $cond['condition']->getRefTable(), 'field' => $cond['condition']->getRefField() ) );
								$out[] = $this->getOperator( $cond['condition']->getOperator() );
								$out[] = $this->getFieldName(array( 'table' => $cond['condition']->getWithTable(), 'field' => $cond['condition']->getWithField() ) );								
                                break;
                            case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_RAW:
                                $value = $cond['condition']->getValue();
                                
								$out[] = $this->getFieldName(array( 'table' => $cond['condition']->getRefTable(), 'field' => $cond['condition']->getRefField() ) );
								$out[] = $this->getOperator( $cond['condition']->getOperator() );
								$out[] = $this->getValue( $value->getValue(), $value->getDatatype() );
                                break;
                        }
                        break;
					case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_LOGIC:
						$out[] = match( $cond['logic'] )
						{
							\damix\engines\orm\conditions\OrmOperator::ORM_OP_AND => 'AND',
							\damix\engines\orm\conditions\OrmOperator::ORM_OP_OR => 'OR',
							default => throw new \damix\core\exception\OrmException( 'Opérateur non géré : ' . __FILE__ . ' ' . __LINE__ ),
						};
						break;
					case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPBEGIN:
						$out[] = '(';
						break;
					case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPEND:
						$out[] = ')';
						break;
                }
            }
           
        }
		
		return implode( ' ', $out) ;
	}
	
	protected function getFromSubrequest(\damix\engines\orm\request\structure\OrmJointure $join) : string
	{
		$sql = '';
		$sub = $join->getSubrequest();
		if( $sub === null )
		{
			$functionorigine = $this->selector->getPart( 'function' );
			$subrequest = $join->getSelector();
			$fonction = $join->getFunction();

			$orm = \damix\engines\orm\Orm::get( $subrequest );

			$method = \damix\engines\orm\method\OrmMethod::get( $subrequest . ':' . $fonction );
			$sel = new \damix\engines\orm\method\OrmMethodSelector( $subrequest . ':' . $functionorigine );
			$sel->setFactory( $orm );
			$orm->setRequestBase( $method->getRequest() );
			$this->delimiter = false;
			$sql = $this->getRequestSelect( $sel, $fonction );
			$this->delimiter = true;
		}
		else
		{
			$this->delimiter = false;
			$sql = $this->getSQL( $sub );
			$this->delimiter = true;
		}
		
		return $sql;
	}
	
	protected function getConditions( \damix\engines\orm\conditions\OrmCondition $conditions ) : string
    {
        $out = array();
        
        $liste = $conditions->getData();
        $last = '';
        foreach( $liste as $condition )
        {
            switch( $condition['type'] )
            {
                case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPBEGIN:
                    switch( $last )
                    {
                        case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD:
                        case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPEND:
                            $out[] = 'AND';
                            break;
                    }
                    $out[] = '(';
                    break;
                case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPEND:
                    $out[] = ')';
                    break;
                case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_LOGIC:
					$out[] = match( $condition['value'] )
					{
						\damix\engines\orm\conditions\OrmOperator::ORM_OP_AND => 'AND',
						\damix\engines\orm\conditions\OrmOperator::ORM_OP_OR => 'OR',
						default => throw new \damix\core\exception\OrmException( 'Opérateur non géré : ' . __FILE__ . ' ' . __LINE__ ),
					};
                    break;
                case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD:
					switch( $last )
                    {
                        case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPEND:
                        case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD:
                            $out[] = 'AND';
                            break;
                    }
					
                    $out[] = $this->getConditionLeft( $condition );
					
                    $out[] = $this->getOperator( $condition['operator'] );
                    
                    $out[] = $this->getConditionRight( $condition );
                    
                    break;
				default:
					throw new \damix\core\exception\OrmException( 'Type de condition non géré : ' . __FILE__ . ' ' . __LINE__ );
            }
            $last = $condition['type'];
        }
        if( count( $out ) == 0 )
        {
            return '';
        }
        
        return implode( ' ', $out) ;
    }
	
	private function getConditionsTemp( \damix\engines\orm\conditions\OrmCondition $condition, string $conditionname ) : string
    {
        $out = array();
        
        $liste = $condition->getHashData();
        $last = '';
		// \damix\engines\logs\log::dump( $liste );
        foreach( $liste as $critere )
        {
            switch( $critere['type'] )
            {
                case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPBEGIN:
                    switch( $last )
                    {
                        case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD:
                        case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPEND:
                            $out[] = 'AND';
                            break;
                    }
                    $out[] = '(';
                    break;
                case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPEND:
                    $out[] = ')';
                    break;
				 case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_LOGIC:
					$out[] = match( $critere['value'] )
					{
						\damix\engines\orm\conditions\OrmOperator::ORM_OP_AND => 'AND',
						\damix\engines\orm\conditions\OrmOperator::ORM_OP_OR => 'OR',
						default => throw new \damix\core\exception\OrmException( 'Opérateur non géré : ' . __FILE__ . ' ' . __LINE__ ),
					};
                    break;
                case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD:
                    switch( $last )
                    {
                        case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPEND:
                        case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD:
                            $out[] = 'AND';
                            break;
                    }
                    
					$out[] = $this->getConditionLeft( $critere );
					
                    $out[] = $this->getOperator( $critere['operator'] );
                    
					if( $critere['operator'] != \damix\engines\orm\conditions\OrmOperator::ORM_OP_ISNULL )
					{
						$sql = '\' . $conditions->getDataValue( \'' . $conditionname . '\', ' . $critere['number'] . ' ) . \'';
						
						$out[] = $sql;
					}

                    break;
				default:
					throw new \damix\core\exception\OrmException( 'Type de condition non géré : ' . __FILE__ . ' ' . __LINE__ );
            }
            $last = $critere['type'];
        }
		
		if( $last === \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_LOGIC )
		{
			array_pop( $out );
		}
		
		
        if( count( $out ) == 0 )
        {
            return '';
        }
        
        return implode( ' ', $out) ;
    }
	
	protected function getConditionCase(array $case) : ?\damix\engines\orm\request\structure\OrmFormula
	{
		if( $this->isCaseManagement() )
		{
			if( is_array( $case['property'] ) )
			{
				$name = $case['property']['name'] ?? $case['property']['field'];
			}
			else
			{
				$name = $case['property'];
			}
			
			// \damix\engines\logs\log::dump( $case );
			$ref = \damix\engines\orm\Orm::getDefine($name);
			if( $ref )
			{
				$datatype = $ref['field']['datatype'];
				
				switch( \damix\engines\orm\request\structure\OrmDataType::cast( $datatype ) )
				{
					case \damix\engines\orm\request\structure\OrmDataType::ORM_CHAR:
					case \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR:
					case \damix\engines\orm\request\structure\OrmDataType::ORM_TEXT:
					case \damix\engines\orm\request\structure\OrmDataType::ORM_LONGTEXT:
						
						$params = array( 'type'=> 'property', 'table' => $ref['orm']->name , 'property' => $ref['field']['name'], 'ref' => $case['property'] );
						
						$formula = new \damix\engines\orm\request\structure\OrmFormula();
						$formula->setName('upper');
						$formula->addParameterArray( array( $params ) );
						return $formula;
				}
				
			}
	
		}
		
		return null;
	}
	
	protected function getConditionLeft(array $condition) : string
	{
		
		$formula = $this->getConditionCase( array( 'type' => 'ref', 'property' => $condition['left'] ));
		
		if( $formula )
		{
			$condition['left'] = $formula;
			$condition['leftdatatype'] = \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FORMULA;
			$condition['type'] =  \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD;
		}
		
		switch( $condition['leftdatatype'] )
		{
			case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD:
				return $this->getFieldName( $condition['left'] );
				break;
			case \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FORMULA:
				$formula = $condition['left'];
				$obj = OrmDriversBase::getDriverFunction( $this->_cnx->getDriver(), $formula->getName() );
				if( $obj !== null )
				{
					$obj->driver = $this;
					return $obj->execute( $formula );
				}
				break;
			default:
				throw new \damix\core\exception\OrmException( 'Type de data non géré : ' . __FILE__ . ' ' . __LINE__ );
		}
	}
	
	protected function getConditionRight(array $condition) : string
	{
		
		if( $condition['operator'] == \damix\engines\orm\conditions\OrmOperator::ORM_OP_ISNULL )
		{
			return '';
		}
			
		
		switch( $condition['rightdatatype'] )
		{
			case \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_INT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_CHAR;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BIGINT;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_FLOAT;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DECIMAL;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DOUBLE;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DATE;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_TIME;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DATETIME;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BIT;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_JSON;
				return $this->getValue( $condition['right'], $condition['rightdatatype'], $condition['operator'] );
			case \damix\engines\orm\request\structure\OrmDataType::ORM_FORMULA;
				$formula = $condition['right'];
				$obj = OrmDriversBase::getDriverFunction( $this->_cnx->getDriver(), $formula->getName() );
				if( $obj !== null )
				{
					$obj->driver = $this;
					return $obj->execute( $formula );
				}
		}
		
		
		throw new \damix\core\exception\OrmException( 'Type de data non géré : ' . __FILE__ . ' ' . __LINE__ );
	}
	
	private function getGroups() : string
    {
        $out = array();
		
        $groups = $this->request->getGroupBy();
		
		foreach( $groups as $info )
		{
			switch( $info->getColumnType() )
            {
                case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_FIELD :
					$field = $info->getField();
                    $out[] = $this->getFieldName( $field );
                    break;
                case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_FORMULA :
					$formula = $info->getFormula();
                    $obj = OrmDriversBase::getDriverFunction( $this->_cnx->getDriver(), $formula->getName() );
                    if( $obj !== null )
                    {
						$obj->driver = $this;
                        $out[] = $obj->execute( $formula );
                    }
                    break;
            }
        }
        
        return implode( ', ', $out ) ;
    }
	
	private function getLimit(\damix\engines\orm\request\structure\OrmLimits $limits) : string
    {
        $sql = '';
        if( $limits )
        {
            $RowCount = $limits->RowCount;
            $Offset = $limits->Offset;
            if( $RowCount > 0 )
            {
                $sql = strval( $RowCount );
                if( $Offset > 0 )
                {
                    $sql .= ' OFFSET ' . $limits->Offset;
                }
            }
        }
        return $sql;
    }
	
	private function getOrderBy(\damix\engines\orm\request\structure\OrmOrders $orders) : string
    {
        $out = array();
		
		foreach( $orders as $info )
		{
			$way = match( $info->getWay() )
			{
				\damix\engines\orm\request\structure\OrmOrderWay::WAY_ASC => 'ASC',
				\damix\engines\orm\request\structure\OrmOrderWay::WAY_DESC => 'DESC',
			};
			$col = $info->getColumn();
			if( is_string( $col ) )
			{
				$out[] = $this->getFieldName( $col ) . ' ' . $way;
			}
			else
			{
				$out[] = $this->getFieldName( $col->getField() ) . ' ' . $way;
			}
        }
        
        return implode( ', ', $out ) ;
    }
	
	public function getOperator(\damix\engines\orm\conditions\OrmOperator $operator ) : string
    {
        switch( $operator )
        {
            case \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ:
                return '=';
			case \damix\engines\orm\conditions\OrmOperator::ORM_OP_NOTEQ:
                return '<>';
            case \damix\engines\orm\conditions\OrmOperator::ORM_OP_LTEQ:
                return '<=';
            case \damix\engines\orm\conditions\OrmOperator::ORM_OP_GTEQ:
                return '>=';
			case \damix\engines\orm\conditions\OrmOperator::ORM_OP_LT:
                return '<';
            case \damix\engines\orm\conditions\OrmOperator::ORM_OP_GT:
                return '>';
            case \damix\engines\orm\conditions\OrmOperator::ORM_OP_LIKE:
            case \damix\engines\orm\conditions\OrmOperator::ORM_OP_LIKE_BEGIN:
            case \damix\engines\orm\conditions\OrmOperator::ORM_OP_LIKE_END:
                return 'like';
            case \damix\engines\orm\conditions\OrmOperator::ORM_OP_NOTLIKE:
                return 'not like';
            case \damix\engines\orm\conditions\OrmOperator::ORM_OP_IN:
                return 'in';
            case \damix\engines\orm\conditions\OrmOperator::ORM_OP_ISNULL:
                return 'is null';
            default:
                throw new \Exception( 'Opérateur non géré : ' . __FILE__ . ' ' . __LINE__ );
                break;
        }
    }
	
	public function getValue(mixed $value, ?\damix\engines\orm\request\structure\OrmDataType $datatype = null, ?\damix\engines\orm\conditions\OrmOperator $operator = null) : string
	{
		if( $value === null )
		{
			return 'NULL';
		}

	
		switch( $datatype )
		{
			case \damix\engines\orm\request\structure\OrmDataType::ORM_CHAR:
				return $this->_cnx->quote(strval($value));
			case \damix\engines\orm\request\structure\OrmDataType::ORM_TINYINT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_SMALLINT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_INT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BIGINT:
				return $this->_cnx->quote(strval(intval($value)));
			case \damix\engines\orm\request\structure\OrmDataType::ORM_FLOAT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DOUBLE:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DECIMAL:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_NUMERIC:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_REAL:
				return $this->_cnx->quote(strval(floatval($value)));
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DATE:
				if( $value === '' )
				{
					return $this->_cnx->quote($this->getDateZero());
				}
				return $this->_cnx->quote(\damix\engines\tools\xDate::loadformat( $value, \damix\engines\tools\xDate::DB_DFORMAT ));
			case \damix\engines\orm\request\structure\OrmDataType::ORM_TIME:
				if( $value === '' )
				{
					return $this->_cnx->quote($this->getTimeZero());
				}
				return $this->_cnx->quote(\damix\engines\tools\xDate::loadformat( $value, \damix\engines\tools\xDate::DB_TFORMAT ));
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DATETIME:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_TIMESTAMP:
				if( $value === '' )
				{
					return $this->_cnx->quote($this->getDateTimeZero());
				}
				return $this->_cnx->quote(\damix\engines\tools\xDate::loadformat( $value, \damix\engines\tools\xDate::DB_DTFORMAT ));
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BOOL:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BIT:
				return $this->getValueBool( $value );
			case \damix\engines\orm\request\structure\OrmDataType::ORM_JSON:
				return $this->_cnx->quote( json_encode( $value ) );
			case \damix\engines\orm\request\structure\OrmDataType::ORM_SQL:
				return $value;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BINARY;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BLOB;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_LONGBLOB;
				if( $value === '' )
				{
					return $this->_cnx->quote('');
				}
				return $this->getEncodeBase64( $this->_cnx->quote( base64_encode($value)) );
				break;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_TEXT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_LONGTEXT:
			default:
				switch( $operator )
				{
					case \damix\engines\orm\conditions\OrmOperator::ORM_OP_LIKE:
						return $this->_cnx->quote(strval($this->symboleLike . $value . $this->symboleLike));
					case \damix\engines\orm\conditions\OrmOperator::ORM_OP_LIKE_BEGIN:
						return $this->_cnx->quote(strval($value . $this->symboleLike));
					case \damix\engines\orm\conditions\OrmOperator::ORM_OP_LIKE_END:
						return $this->_cnx->quote(strval($this->symboleLike. $value));
					default:
						return $this->_cnx->quote(strval($value));
				}
		};

		throw new \damix\core\exception\CoreException( 'Datatype not found' );
	}
	
	protected function getValueBool( string|bool $value ) : string
	{
		return tobool( $value ) ? 'b\'1\'' : 'b\'0\'';
	}
	
	protected function getEncodeBase64(string $base64 ) : string
	{
		return 'FROM_BASE64(' . $base64 . ')';
	}
	
	protected function getDateZero()
	{
		return '0000-00-00';
	}
	
	protected function getDateTimeZero()
	{
		return '0000-00-00 00:00:00';
	}
	
	protected function getTimeZero()
	{
		return '00:00:00';
	}
	
	public function getData( mixed $value, \damix\engines\orm\request\structure\OrmDataType $datatype )
    {
        switch( $datatype )
        {
            case \damix\engines\orm\request\structure\OrmDataType::ORM_INT:
                return intval( $value );
            case \damix\engines\orm\request\structure\OrmDataType::ORM_DECIMAL:
            case \damix\engines\orm\request\structure\OrmDataType::ORM_DOUBLE:
            case \damix\engines\orm\request\structure\OrmDataType::ORM_FLOAT:
                return floatval( $value );
            case \damix\engines\orm\request\structure\OrmDataType::ORM_BOOL:
                return intval( $value ) > 0 ? true : false;
            case \damix\engines\orm\request\structure\OrmDataType::ORM_DATE:
            case \damix\engines\orm\request\structure\OrmDataType::ORM_TIME:
            case \damix\engines\orm\request\structure\OrmDataType::ORM_DATETIME:
                if( ! $value instanceof \damix\engines\tools\xDate )
                {
                    $value = \damix\engines\tools\xDate::load( $value );
                }
                
                return $value;
            case \damix\engines\orm\request\structure\OrmDataType::ORM_JSON:
                return json_decode( $value );
            case \damix\engines\orm\request\structure\OrmDataType::ORM_TEXT:
            case \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR:
            default:
                return $value;
                break;
        }
        return $value;
    }
	
	public function getFieldDatatype(\damix\engines\orm\request\structure\OrmField $field) : string
	{
		$enumerate = array();
		foreach( $field->getEnumerate() as $enum )
		{
			$enumerate[] = $this->_cnx->quote( $enum );
		}
		
		return match( $field->getDatatype() )
		{
			\damix\engines\orm\request\structure\OrmDataType::ORM_BOOL => 'bit(1)',
			\damix\engines\orm\request\structure\OrmDataType::ORM_BIT => 'bit(' . $field->getSize() . ')',
			\damix\engines\orm\request\structure\OrmDataType::ORM_TINYINT => 'tinyint',
			\damix\engines\orm\request\structure\OrmDataType::ORM_SMALLINT => 'smallint',
			\damix\engines\orm\request\structure\OrmDataType::ORM_INT => 'int(10)' . ($field->getUnsigned() ? ' unsigned' : ''),
			\damix\engines\orm\request\structure\OrmDataType::ORM_BIGINT => 'bigint(20)' . ($field->getUnsigned() ? ' unsigned' : ''),
			\damix\engines\orm\request\structure\OrmDataType::ORM_DOUBLE => 'double'. ($field->getUnsigned() ? ' unsigned' : ''),
			\damix\engines\orm\request\structure\OrmDataType::ORM_DECIMAL => 'decimal(' . $field->getSize() . ',' . $field->getPrecision()  . ')'. ($field->getUnsigned() ? ' unsigned' : ''),
			\damix\engines\orm\request\structure\OrmDataType::ORM_NUMERIC => 'numeric' . ($field->getUnsigned() ? ' unsigned' : ''),
			\damix\engines\orm\request\structure\OrmDataType::ORM_FLOAT => 'float' . ($field->getUnsigned() ? ' unsigned' : ''),
			\damix\engines\orm\request\structure\OrmDataType::ORM_REAL => 'real' . ($field->getUnsigned() ? ' unsigned' : ''),
			\damix\engines\orm\request\structure\OrmDataType::ORM_DATE => 'date',
			\damix\engines\orm\request\structure\OrmDataType::ORM_TIME => 'time',
			\damix\engines\orm\request\structure\OrmDataType::ORM_DATETIME => 'datetime',
			\damix\engines\orm\request\structure\OrmDataType::ORM_TIMESTAMP => 'timestamp',
			\damix\engines\orm\request\structure\OrmDataType::ORM_CHAR => 'char(' . $field->getSize() . ')',
			\damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR => 'varchar(' . $field->getSize() . ')',
			\damix\engines\orm\request\structure\OrmDataType::ORM_TEXT => 'text',
			\damix\engines\orm\request\structure\OrmDataType::ORM_LONGTEXT => 'longtext',
			\damix\engines\orm\request\structure\OrmDataType::ORM_BINARY => 'binary',
			\damix\engines\orm\request\structure\OrmDataType::ORM_BLOB => 'blob',
			\damix\engines\orm\request\structure\OrmDataType::ORM_LONGBLOB => 'longblob',
			\damix\engines\orm\request\structure\OrmDataType::ORM_ENUM => 'enum(' . implode(',', $enumerate ) . ')',
			\damix\engines\orm\request\structure\OrmDataType::ORM_JSON => 'json',
			
			default => throw new \damix\core\exception\CoreException( 'Datatype not found : ' . $field->getDatatype()->toString() ),
		};
	}

	public function parseDatatypeTofield(\stdClass $property) : \damix\engines\orm\request\structure\OrmField
	{
		$field = new \damix\engines\orm\request\structure\OrmField();
		$field->setName( $property->column_name );
		$field->setNull( ($property->is_nullable === 'YES' ? true : false) );
		$field->setAutoincrement( preg_match( '/auto_increment/', $property->extra) ? true : false );
		$field->setPrimaryKey( preg_match( '/PRI/', $property->column_key) ? true : false );
		$field->setDefault( $property->column_default );
		$field->setUnsigned( preg_match( '/unsigned/', $property->column_type) ? true : false );
		
		$datatype = \damix\engines\orm\request\structure\OrmDataType::cast( $property->data_type );
		$field->setDatatype( $datatype );
		switch( $datatype )
		{
			case \damix\engines\orm\request\structure\OrmDataType::ORM_TEXT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_LONGTEXT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BOOL:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BIT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DATE:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DATETIME:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_TIMESTAMP:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_TIME:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_TINYINT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_INT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_SMALLINT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BIGINT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_FLOAT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_NUMERIC:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DOUBLE:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_REAL:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BINARY:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BLOB:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_LONGBLOB:
				break;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_CHAR:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR:
				$field->setSize( intval($property->character_maximum_length ));
				break;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DECIMAL:
				$field->setSize( intval($property->character_maximum_length ));
				$field->setPrecision( intval($property->numeric_precision ));
				break;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_ENUM:
				$enum = $property->column_type;

				if( preg_match( '/^enum\(\'(.*)\'\)$/', $enum, $out) )
				{
					$field->setEnumerate( explode( '\',\'', $out[1] ) );
				}
				break;
			default:
				throw new \damix\core\exception\CoreException( 'Datatype not found : ' . $property->data_type );
		}
		
		return $field;
	}

	public static function getDriverFunction( string $driver, string $function ) : ?\damix\engines\orm\drivers\OrmDriversFunctionBase
    {
        $driver = strtolower( $driver );
        
        if(! isset(OrmDriversBase::$_singletonfunction[ $driver ]))
        {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $driver . DIRECTORY_SEPARATOR . 'functions';

			OrmDriversBase::$_singletonfunction[$driver] = OrmDriversBase::loadfunction( $dir );
		}
         
        if( isset( OrmDriversBase::$_singletonfunction[$driver][ $function ] ) )
        {
            $obj = OrmDriversBase::$_singletonfunction[$driver][ $function ];
            if( ! $obj[ 'load' ] )
            {
                if( is_readable( $obj[ 'fullpath' ] ) )
                {
                    require_once( $obj[ 'fullpath' ] );
                    $classname = '\damix\orm\drivers\OrmDrivers'. ucfirst($driver) .'Function' . ucfirst( strtolower( $function ) );
                    $obj[ 'load' ] = $classname;
                }
            }

            $classname = $obj[ 'load' ];
            return new $classname();
        }
        
        return null;
    }
    
    private static function loadfunction( $dir ) : array
    {
        $directories = scandir( $dir );
        $out = array();
        foreach( $directories as $elt )
        {
            if( $elt != '.' && $elt != '..' )
            {
                if( is_dir( $dir . DIRECTORY_SEPARATOR . $elt ) )
                {
                    $out = array_merge( $out, OrmDriversBase::loadfunction( $dir . DIRECTORY_SEPARATOR . $elt ) );
                }
                else
                {
                    if( preg_match( '/^([a-zA-Z0-9]*)\.function\.php$/', $elt, $match ) )
                    {
                        $name = $match[1];
                        $out[ $name ] = array( 
                            'name' => $name, 
                            'load' => false, 
                            'fullpath' => $dir . DIRECTORY_SEPARATOR . $elt,
                            );
                    }
                }
            }
        }
        
        return $out;
    }
	
	public function SchemaBase() : \damix\engines\orm\request\OrmRequest
	{
		$OrmRequest = new \damix\engines\orm\request\OrmRequest();
        $request = $OrmRequest->createSelect();
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TABLES', 'TABLE_CATALOG', 'TABLE_CATALOG' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TABLES', 'TABLE_SCHEMA', 'TABLE_SCHEMA' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TABLES', 'TABLE_NAME', 'TABLE_NAME' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TABLES', 'TABLE_TYPE', 'TABLE_TYPE' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TABLES', 'ENGINE', 'ENGINE' );
        $request->addDisplay($column);
        
        $table = \damix\engines\orm\request\structure\OrmTable::newTable( 'TABLES' );
        $table->setInternal( 'information_schema' );
        $join = $request->addJoin( 'from', $table, 'TABLES' );
		
		$c = $request->getConditions();
		$c->addString( 'TABLE_SCHEMA', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $this->_cnx->getDatabase());
		
        return $request;
	}
	
	public function SchemaTable(\damix\engines\orm\request\structure\OrmTable $ormtable) : \damix\engines\orm\request\OrmRequest
	{
		$OrmRequest = new \damix\engines\orm\request\OrmRequest();
        $request = $OrmRequest->createSelect();
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TABLES', 'TABLE_CATALOG', 'TABLE_CATALOG' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TABLES', 'TABLE_SCHEMA', 'TABLE_SCHEMA' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TABLES', 'TABLE_NAME', 'TABLE_NAME' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TABLES', 'TABLE_TYPE', 'TABLE_TYPE' );
        $request->addDisplay($column);
        
        $table = \damix\engines\orm\request\structure\OrmTable::newTable( 'TABLES' );
        $table->setInternal( 'information_schema' );
        $join = $request->addJoin( 'from', $table, 'TABLES' );
		
		$schemaname = $ormtable->getSchema()?->getRealname();
		
		
		$c = $request->getConditions();
		$c->addString( 'TABLE_SCHEMA', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, ($this->isSchema() && !empty($schemaname)? $schemaname : $this->_cnx->getDatabase() ));
		$c->addString( 'TABLE_NAME', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $ormtable->getRealname());
		
        return $request;
	}
	
	public function SchemaIndex(\damix\engines\orm\request\structure\OrmTable $tablename) : \damix\engines\orm\request\OrmRequest
	{
		$OrmRequest = new \damix\engines\orm\request\OrmRequest();
        $request = $OrmRequest->createSelect();
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'STATISTICS', 'TABLE_SCHEMA', 'TABLE_SCHEMA' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'STATISTICS', 'TABLE_NAME', 'TABLE_NAME' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'STATISTICS', 'NON_UNIQUE', 'NON_UNIQUE' );
        $request->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'STATISTICS', 'INDEX_NAME', 'INDEX_NAME' );
        $request->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'STATISTICS', 'SEQ_IN_INDEX', 'SEQ_IN_INDEX' );
        $request->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'STATISTICS', 'COLUMN_NAME', 'COLUMN_NAME' );
        $request->addDisplay($column);
        
        $table = \damix\engines\orm\request\structure\OrmTable::newTable( 'STATISTICS' );
        $table->setInternal( 'information_schema' );
        $join = $request->addJoin( 'from', $table, 'STATISTICS' );
		
		$c = $request->getConditions();
		$c->addString( 'TABLE_SCHEMA', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $this->_cnx->getDatabase());
		$c->addString( 'TABLE_NAME', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $tablename->getRealname());
		
        return $request;
	}
	
	public function SchemaTrigger(\damix\engines\orm\request\structure\OrmTable $tablename) : \damix\engines\orm\request\OrmRequest
	{

		$OrmRequest = new \damix\engines\orm\request\OrmRequest();
        $request = $OrmRequest->createSelect();
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TRIGGERS', 'TRIGGER_SCHEMA', 'TRIGGER_SCHEMA' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TRIGGERS', 'TRIGGER_NAME', 'TRIGGER_NAME' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TRIGGERS', 'EVENT_OBJECT_TABLE', 'EVENT_OBJECT_TABLE' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TRIGGERS', 'EVENT_MANIPULATION', 'EVENT_MANIPULATION' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TRIGGERS', 'ACTION_TIMING', 'ACTION_TIMING' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TRIGGERS', 'ACTION_STATEMENT', 'ACTION_STATEMENT' );
        $request->addDisplay($column);
        
        $table = \damix\engines\orm\request\structure\OrmTable::newTable( 'TRIGGERS' );
        $table->setInternal( 'information_schema' );
        $join = $request->addJoin( 'from', $table, 'TRIGGERS' );
		
		$c = $request->getConditions();
		$c->addString( 'TRIGGER_SCHEMA', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $this->_cnx->getDatabase());
		$c->addString( 'EVENT_OBJECT_TABLE', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $tablename->getRealname());
		
        return $request;
	}
	
	public function SchemaEvent() : \damix\engines\orm\request\OrmRequest
	{

		$OrmRequest = new \damix\engines\orm\request\OrmRequest();
        $request = $OrmRequest->createSelect();
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'EVENTS', 'EVENT_SCHEMA', 'EVENT_SCHEMA' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'EVENTS', 'EVENT_NAME', 'EVENT_NAME' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'EVENTS', 'EVENT_DEFINITION', 'EVENT_DEFINITION' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'EVENTS', 'EVENT_TYPE', 'EVENT_TYPE' );
        $request->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'EVENTS', 'INTERVAL_VALUE', 'INTERVAL_VALUE' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'EVENTS', 'INTERVAL_FIELD', 'INTERVAL_FIELD' );
        $request->addDisplay($column);

                
        $table = \damix\engines\orm\request\structure\OrmTable::newTable( 'EVENTS' );
        $table->setInternal( 'information_schema' );
        $join = $request->addJoin( 'from', $table, 'EVENTS' );
		
		$c = $request->getConditions();
		$c->addString( 'EVENT_SCHEMA', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $this->_cnx->getDatabase());
		
        return $request;
	}
	
	public function SchemaStored() : \damix\engines\orm\request\OrmRequest
	{

		$OrmRequest = new \damix\engines\orm\request\OrmRequest();
        $request = $OrmRequest->createSelect();
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'ROUTINES', 'ROUTINE_SCHEMA', 'ROUTINE_SCHEMA' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'ROUTINES', 'ROUTINE_NAME', 'ROUTINE_NAME' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'ROUTINES', 'ROUTINE_TYPE', 'ROUTINE_TYPE' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'ROUTINES', 'DATA_TYPE', 'DATA_TYPE' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'ROUTINES', 'CHARACTER_MAXIMUM_LENGTH', 'CHARACTER_MAXIMUM_LENGTH' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'ROUTINES', 'NUMERIC_PRECISION', 'NUMERIC_PRECISION' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'ROUTINES', 'ROUTINE_DEFINITION', 'ROUTINE_DEFINITION' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'ROUTINES', 'IS_DETERMINISTIC', 'IS_DETERMINISTIC' );
        $request->addDisplay($column);
                
        $table = \damix\engines\orm\request\structure\OrmTable::newTable( 'ROUTINES' );
        $table->setInternal( 'information_schema' );
        $join = $request->addJoin( 'from', $table, 'ROUTINES' );
		
		$c = $request->getConditions();
		$c->addString( 'ROUTINE_SCHEMA', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $this->_cnx->getDatabase());
		
        return $request;
	}
	
	public function SchemaStoredParameter(string $storedname) : \damix\engines\orm\request\OrmRequest
	{
		$OrmRequest = new \damix\engines\orm\request\OrmRequest();
        $request = $OrmRequest->createSelect();
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'PARAMETERS', 'SPECIFIC_SCHEMA', 'SPECIFIC_SCHEMA' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'PARAMETERS', 'SPECIFIC_NAME', 'SPECIFIC_NAME' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'PARAMETERS', 'ORDINAL_POSITION', 'ORDINAL_POSITION' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'PARAMETERS', 'PARAMETER_MODE', 'PARAMETER_MODE' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'PARAMETERS', 'PARAMETER_NAME', 'PARAMETER_NAME' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'PARAMETERS', 'DATA_TYPE', 'DATA_TYPE' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'PARAMETERS', 'CHARACTER_MAXIMUM_LENGTH', 'CHARACTER_MAXIMUM_LENGTH' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'PARAMETERS', 'NUMERIC_PRECISION', 'NUMERIC_PRECISION' );
        $request->addDisplay($column);
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'PARAMETERS', 'NUMERIC_SCALE', 'NUMERIC_SCALE' );
        $request->addDisplay($column);
        
	
        $table = \damix\engines\orm\request\structure\OrmTable::newTable( 'PARAMETERS' );
        $table->setInternal( 'information_schema' );
        $join = $request->addJoin( 'from', $table, 'PARAMETERS' );
		
		$c = $request->getConditions();
		$c->addString( 'SPECIFIC_SCHEMA', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $this->_cnx->getDatabase());
		$c->addString( 'SPECIFIC_NAME', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $storedname);
		
		
		$order = new \damix\engines\orm\request\structure\OrmOrder();
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$column->setColumnField( '', 'PARAMETERS', 'ORDINAL_POSITION', 'ORDINAL_POSITION' );
		$order->setColumn($column);
		$order->setWay(\damix\engines\orm\request\structure\OrmOrderWay::WAY_ASC);
		$request->addOrderBy($order);
		
        return $request;
	}
	
	public function SchemaForeignKey(\damix\engines\orm\request\structure\OrmTable $tablename) : \damix\engines\orm\request\OrmRequest
	{
		$OrmRequest = new \damix\engines\orm\request\OrmRequest();
        $request = $OrmRequest->createSelect();
        
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'KEY_COLUMN_USAGE', 'CONSTRAINT_NAME', 'CONSTRAINT_NAME' );
        $request->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'KEY_COLUMN_USAGE', 'TABLE_SCHEMA', 'TABLE_SCHEMA' );
        $request->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'KEY_COLUMN_USAGE', 'TABLE_NAME', 'TABLE_NAME' );
        $request->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'KEY_COLUMN_USAGE', 'COLUMN_NAME', 'COLUMN_NAME' );
        $request->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'KEY_COLUMN_USAGE', 'REFERENCED_TABLE_SCHEMA', 'REFERENCED_TABLE_SCHEMA' );
        $request->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'KEY_COLUMN_USAGE', 'REFERENCED_TABLE_NAME', 'REFERENCED_TABLE_NAME' );
        $request->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'KEY_COLUMN_USAGE', 'REFERENCED_COLUMN_NAME', 'REFERENCED_COLUMN_NAME' );
        $request->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'REFERENTIAL_CONSTRAINTS', 'UPDATE_RULE', 'UPDATE_RULE' );
        $request->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'REFERENTIAL_CONSTRAINTS', 'DELETE_RULE', 'DELETE_RULE' );
        $request->addDisplay($column);
                
        $table = \damix\engines\orm\request\structure\OrmTable::newTable( 'REFERENTIAL_CONSTRAINTS' );
        $table->setInternal( 'information_schema' );
        $join = $request->addJoin( 'from', $table, 'REFERENTIAL_CONSTRAINTS' );
		
		$table = \damix\engines\orm\request\structure\OrmTable::newTable( 'KEY_COLUMN_USAGE' );
        $table->setInternal( 'information_schema' );
		$join = $request->addJoin( 'join', $table, 'KEY_COLUMN_USAGE' );
        $join->addConditionField( 'KEY_COLUMN_USAGE', 'TABLE_SCHEMA', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'REFERENTIAL_CONSTRAINTS', 'CONSTRAINT_SCHEMA' );
        $join->addLogic(\damix\engines\orm\conditions\OrmOperator::ORM_OP_AND );
        $join->addConditionField( 'KEY_COLUMN_USAGE', 'TABLE_NAME', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'REFERENTIAL_CONSTRAINTS', 'TABLE_NAME' );
        $join->addLogic(\damix\engines\orm\conditions\OrmOperator::ORM_OP_AND );
        $join->addConditionField( 'KEY_COLUMN_USAGE', 'CONSTRAINT_NAME', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'REFERENTIAL_CONSTRAINTS', 'CONSTRAINT_NAME' );
		
		$c = $request->getConditions();
		$c->addString( array( 'table' => 'REFERENTIAL_CONSTRAINTS', 'field' => 'CONSTRAINT_SCHEMA'), \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $this->_cnx->getDatabase());
		$c->addString( array( 'table' => 'REFERENTIAL_CONSTRAINTS', 'field' => 'TABLE_NAME'), \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $tablename->getRealname());
		
        return $request;
	}
	
	public function SchemaColonne(\damix\engines\orm\request\structure\OrmTable $ormtable) : \damix\engines\orm\request\OrmRequest
	{
		$OrmRequest = new \damix\engines\orm\request\OrmRequest();
        $request = $OrmRequest->createSelect();
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'TABLE_CATALOG', 'table_catalog' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'TABLE_SCHEMA', 'table_schema' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'TABLE_NAME', 'table_name' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'COLUMN_NAME', 'column_name' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'ORDINAL_POSITION', 'ordinal_position' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'COLUMN_DEFAULT', 'column_default' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'IS_NULLABLE', 'is_nullable' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'DATA_TYPE', 'data_type' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'CHARACTER_MAXIMUM_LENGTH', 'character_maximum_length' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'CHARACTER_OCTET_LENGTH', 'character_octet_length' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'NUMERIC_PRECISION', 'numeric_precision' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'NUMERIC_SCALE', 'numeric_scale' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'DATETIME_PRECISION', 'datetime_precision' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'CHARACTER_SET_NAME', 'character_set_name' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'COLUMN_TYPE', 'column_type' );
        $request->addDisplay($column);
				
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'COLUMN_KEY', 'column_key' );
        $request->addDisplay($column);
				
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'EXTRA', 'extra' );
        $request->addDisplay($column);
		
		
        
        $table = \damix\engines\orm\request\structure\OrmTable::newTable( 'COLUMNS' );
        $table->setInternal( 'information_schema' );
        $join = $request->addJoin( 'from', $table, 'COLUMNS' );
		
		$c = $request->getConditions();
		
		$schemaname = $ormtable->getSchema()?->getRealname();
		
		$c->addString( 'TABLE_SCHEMA', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, ($this->isSchema() && !empty($schemaname)? $schemaname : $this->_cnx->getDatabase() ));
		$c->addString( 'TABLE_NAME', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $ormtable->getRealname());

        return $request;
	}

	public function DataTypeCast( string $value ) : \damix\engines\orm\request\structure\OrmDataType
	{
		$datatype = \damix\engines\orm\request\structure\OrmDataType::tryFrom( $value );
		
		if( $datatype === null)
		{
			throw new \Exception( 'Datatype not found ' . $value );
		}
		
		return $datatype;
	}
}