<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\drivers;


class OrmDriversPgsql
    extends OrmDriversBase
{
	protected bool $alterAfter = false;

	public function isSchema() : bool 
	{
		return true;
	}
	
	protected function getFieldProtector() : string
	{
		return '';
	}
	
	protected function getRequestSQLCreateOption(\damix\engines\orm\request\structure\OrmTable $table) : string
	{
		$out = array();
		
        $out[] = 'TABLESPACE PG_DEFAULT';
		
		return trim(implode(' ', $out ));
	}
	
	protected function getRequestSQLCreateBefore() : string
	{
		$table = $this->request->getTable();
		$fields = $table->getFields();
		$schemaname = $table->getSchema()?->getRealname();
		
		foreach( $fields as $field )
		{
			switch( $field->getDatatype() )
			{
				case \damix\engines\orm\request\structure\OrmDataType::ORM_ENUM:
					$enumerate = array();
					foreach( $field->getEnumerate() as $enum )
					{
						$enumerate[] = $this->_cnx->quote( $enum );
					}
					
					
					return 'DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = \'' . $table->getRealname() .'_'. $field->getRealname() . '\') THEN CREATE TYPE ' . ($this->isSchema() ? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . $table->getRealname() .'_'. $field->getRealname() . ' AS enum(' . implode(',', $enumerate ) . '); END IF; END $$;';
					
					break;
			}
		}
		
		return '';
	}
	
    protected function getRequestSQLCreate() : string
	{
		$table = $this->request->getTable();
		$fields = $table->getFields();
		
		$out = array();
		$pk = array();
		
		$before = $this->getRequestSQLCreateBefore();
		if( ! empty( $before ) )
		{
			$out[] = $before;
		}
		
		$out[] = 'CREATE TABLE ' . ($this->request->getIgnore() ? 'IF NOT EXISTS ' : '') . $this->getTableName($table);
        $out[] = '(';
		
		$outField = array();

		foreach( $fields as $field )
		{
			if( $field->getAutoincrement() )
			{
				$outField[] = $this->getFieldProtector() . $field->getRealname() . $this->getFieldProtector() . ' SERIAL NOT NULL';
			}
			else
			{
				$outField[] = $this->getRequestStructureField( $field );
			}
			if( $field->getPrimaryKey() ) 
			{
				$pk[] =  $this->getFieldProtector() . $field->getRealname() . $this->getFieldProtector();
			}
		}
		

		if( count( $pk ) > 0 )
		{
			$outField[] =  'CONSTRAINT ' . $table->getRealname() . '_pkey PRIMARY KEY (' . implode( ', ', $pk) . ')';
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
				if( $field['field']->getAutoincrement() )
				{
					$sql = 'ADD ' . $this->getFieldProtector() . $field['field']->getRealname() . $this->getFieldProtector() . ' SERIAL NOT NULL';
				}
				else
				{
					$sql = 'ADD ' . $this->getRequestStructureField( $field['field'] ) . ( $this->alterAfter && $after ? ' AFTER ' . $this->getFieldProtector() . $after . $this->getFieldProtector() : '');
				}
				return $sql;
				break;
			case 'modify':
				return 'ALTER COLUMN ' . $this->getFieldProtector() . $field['field']->getRealname() . $this->getFieldProtector() . ' TYPE ' . $this->getFieldDatatype($field['field']);
				break;
			case 'delete':
				return 'DROP ' . $this->getFieldProtector() . $field['field']->getRealname() . $this->getFieldProtector();
				break;
		}
		
		return '';
	}
	
	protected function getRequestSQLCreateProcedureHeader(\damix\engines\orm\request\OrmRequestStored $stored ) : string
	{
		$schemaname = $stored->getSchema()?->getRealname();
		
		$sql = array();
		$sql[] = 'CREATE PROCEDURE ' . ($this->isSchema() && !empty($schemaname)? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . $this->getFieldProtector() . $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector() . '(';
        foreach( $stored->getParameters() as $field )
        {
			$sqlparams[] = $this->getFieldProtector() . $field->getRealname() . $this->getFieldProtector() . ' ' . $this->getFieldDatatype($field);
        }
        
        $sql[] = implode(', ', $sqlparams );
        
        $sql[] = ')';
		$sql[] = 'LANGUAGE SQL';
		
		$sql = implode("\n", $sql );
        return $sql;
	}
	
	protected function getRequestSQLCreateFunctionHeader(\damix\engines\orm\request\OrmRequestStored $stored) : string
	{
		$schemaname = $stored->getSchema()?->getRealname();
		
		$sql = array();
		$sql[] = 'CREATE FUNCTION ' . ($this->isSchema() && !empty($schemaname)? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . $this->getFieldProtector() . $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector() . '(';
        foreach( $stored->getParameters() as $field )
        {
			$sqlparams[] = $this->getFieldProtector() . $field->getRealname() . $this->getFieldProtector() . ' ' . $this->getFieldDatatype($field);
        }
        
        $sql[] = implode(', ', $sqlparams );
        
        $sql[] = ')';
		$sql[] = 'RETURNS ' . $this->getFieldDatatype( $stored->getReturn() ) . ' AS $$';
		
		$sql = implode("\n", $sql );
        return $sql;
	}
	
	public function getRequestSQLDeleteTrigger() : string
    {
		$stored = $this->request;
		$table = $stored->getTable();
		
		$schemaname = $stored->getSchema()?->getRealname();
		$sql = array();
		$sql[] = 'DROP TRIGGER IF EXISTS ' . $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector() . ' ON ' . $this->getTableName( $table ). $this->getDelimiter();
		$sql[] = 'DROP FUNCTION IF EXISTS ' . ($this->isSchema() && !empty($schemaname)? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector() . '_stamp()'. $this->getDelimiter();
		
		$sql = implode("\n", $sql );
        return $sql;
    }
	
	public function getRequestSQLCreateTriggerHeader(\damix\engines\orm\request\OrmRequestStored $stored) : string
    {
		$stored = $this->request;
		
        $table = $stored->getTable();
		
		$event = match($stored->getEvent())		
			{
				\damix\engines\orm\request\structure\OrmTriggerEvent::ORM_BEFORE => 'BEFORE',
				\damix\engines\orm\request\structure\OrmTriggerEvent::ORM_AFTER => 'AFTER',
			};
		
		$action = match($stored->getAction())	
			{
				\damix\engines\orm\request\structure\OrmTriggerAction::ORM_INSERT => 'INSERT',
				\damix\engines\orm\request\structure\OrmTriggerAction::ORM_UPDATE => 'UPDATE',
				\damix\engines\orm\request\structure\OrmTriggerAction::ORM_DELETE => 'DELETE',
			};
		
		$schemaname = $stored->getSchema()?->getRealname();
		
        $sql = array();
        $sql[] = 'CREATE FUNCTION ' . ($this->isSchema() && !empty($schemaname)? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector() . '_stamp()  RETURNS trigger AS $'.$stored->getName().'_stamp$';
        $sql[] = 'BEGIN';
        $sql[] =$stored->getContent();
        $sql[] = 'END;';
        $sql[] = '$'.$stored->getName().'_stamp$ LANGUAGE plpgsql;';
        $sql[] = 'CREATE TRIGGER ' . $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector();
        $sql[] = $event . ' '. $action .' ON '. $this->getTableName( $table );
        $sql[] = 'FOR EACH ROW EXECUTE FUNCTION ' . ($this->isSchema() && !empty($schemaname)? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector() . '_stamp();';
      
        $sql = implode("\n", $sql );
        return $sql;
    }
	
	public function getRequestSQLCreateTrigger() : string
    {
		$stored = $this->request;
		
		$sql[] = $this->getRequestSQLCreateTriggerHeader($stored);
        
        $sql = implode("\n", $sql );
        return $sql;
    }
	
	public function getRequestSQLCreateEvent() : string
    {
		throw new \damix\core\exception\OrmException('Events not exists with pgsql');
	}
	
	public function getRequestSQLDeleteEvent() : string
    {
		throw new \damix\core\exception\OrmException('Events not exists with pgsql');
	}
	
	protected function getRequestSQLAlterIndexes() : string
	{
		$table = $this->request->getTable();
		$indexes = $this->request->getIndexes();
		$schemaname = $table->getSchema()?->getRealname();
		$out = array();
		$outField = array();
		
		foreach( $indexes as $index )
        {
			switch( $index['type'] )
			{
				case 'add':
				
					$type = match( $index['index']->getIndexType() ){
						\damix\engines\orm\request\structure\OrmIndexType::ORM_UNIQUE => 'UNIQUE ',
						\damix\engines\orm\request\structure\OrmIndexType::ORM_SPATIAL => 'SPATIAL ',
						\damix\engines\orm\request\structure\OrmIndexType::ORM_FULLTEXT => 'FULLTEXT ',
						default => '',
					};
									
					$outField[] = 'CREATE ' . $type . 'INDEX  IF NOT EXISTS ' . $this->getFieldProtector() . $index['index']->getName() . $this->getFieldProtector();
					$outField[] = 'ON';
					$outField[] = $this->getTableName($table);
					$outField[] = '(';
					$outindex = array();
					foreach( $index['index']->getFields() as $field )
					{
						$way = match($field['way']) {
								\damix\engines\orm\request\structure\OrmOrderWay::WAY_ASC => 'asc',
								\damix\engines\orm\request\structure\OrmOrderWay::WAY_DESC => 'desc',
						};
						$outindex[] = $this->getFieldName($field['field']->getRealname()) . ' ' . $way;
					}
					$outField[] = implode( ', ', $outindex );
					$outField[] = ')' . $this->getDelimiter();
					
					break;
				case 'delete':
					$outField[] = 'DROP INDEX ' . ( $index['index']->getIgnore() ? 'IF EXISTS ' : '') . ($this->isSchema() && !empty($schemaname)? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . $index['index']->getName() . $this->getDelimiter();
					break;
			}
		
        }
		
		if( count( $outField ) == 0 )
		{
			return '';
		}
		return implode( ' ', $outField );
	}
	
	protected function getDateZero()
	{
		return 'infinity';
	}
	
	protected function getDateTimeZero()
	{
		return 'infinity';
	}
	
	protected function getTimeZero()
	{
		return 'infinity';
	}
	
	protected function getValueBool( string|bool $value ) : string
	{
		return tobool( $value ) ? 'CAST(1 AS bit)' : 'CAST(0 AS bit)';
	}
	
	protected function getEncodeBase64(string $value ) : string
	{
		return 'DECODE(' . $value . ', \'base64\')';
	}
	
	public function getFieldDatatype(\damix\engines\orm\request\structure\OrmField $field) : string
	{
		$table = $field->getTable();
		$schemaname = '';
		if( $table )
		{
			$schemaname = $table->getSchema()?->getRealname();
		}
		
		return match( $field->getDatatype() )
		{
			\damix\engines\orm\request\structure\OrmDataType::ORM_BOOL => 'bit(1)',
			\damix\engines\orm\request\structure\OrmDataType::ORM_BIT => 'bit(' . $field->getSize() . ')',
			\damix\engines\orm\request\structure\OrmDataType::ORM_TINYINT => 'smallint',
			\damix\engines\orm\request\structure\OrmDataType::ORM_SMALLINT => 'smallint',
			\damix\engines\orm\request\structure\OrmDataType::ORM_INT => 'integer',
			\damix\engines\orm\request\structure\OrmDataType::ORM_BIGINT => 'bigint',
			\damix\engines\orm\request\structure\OrmDataType::ORM_DOUBLE => 'double',
			\damix\engines\orm\request\structure\OrmDataType::ORM_DECIMAL => 'decimal(' . $field->getSize() . ',' . $field->getPrecision()  . ')',
			\damix\engines\orm\request\structure\OrmDataType::ORM_NUMERIC => 'numeric',
			\damix\engines\orm\request\structure\OrmDataType::ORM_FLOAT => 'float',
			\damix\engines\orm\request\structure\OrmDataType::ORM_REAL => 'real',
			\damix\engines\orm\request\structure\OrmDataType::ORM_DATE => 'date',
			\damix\engines\orm\request\structure\OrmDataType::ORM_TIME => 'time',
			\damix\engines\orm\request\structure\OrmDataType::ORM_DATETIME => 'timestamp',
			\damix\engines\orm\request\structure\OrmDataType::ORM_TIMESTAMP => 'timestamp',
			\damix\engines\orm\request\structure\OrmDataType::ORM_CHAR => 'char(' . $field->getSize() . ')',
			\damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR => 'varchar(' . $field->getSize() . ')',
			\damix\engines\orm\request\structure\OrmDataType::ORM_TEXT => 'text',
			\damix\engines\orm\request\structure\OrmDataType::ORM_LONGTEXT => 'longtext',
			\damix\engines\orm\request\structure\OrmDataType::ORM_BINARY => 'BYTEA',
			\damix\engines\orm\request\structure\OrmDataType::ORM_BLOB => 'BYTEA',
			\damix\engines\orm\request\structure\OrmDataType::ORM_LONGBLOB => 'BYTEA',
			\damix\engines\orm\request\structure\OrmDataType::ORM_ENUM => ($this->isSchema() ? $this->getFieldProtector() . $schemaname . $this->getFieldProtector() . '.' : '' ) . ( $table ? $table->getRealname() .'_' : '' ) . $field->getRealname(),
			\damix\engines\orm\request\structure\OrmDataType::ORM_JSON => 'json',
			
			default => parent::getFieldDatatype( $field ),
		};
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
				if( ! $data['isPrimaryKey'] )
				{
					$outvalue[] =  $this->getValue( $data['value'], $field['datatype'] );
				}
			}
			
			$values[] = '(' . implode(', ', $outvalue ) . ')';
		}
		
		foreach( $fields as $field )
		{
			$data = $row->getValue( $field['name'] );
			if( ! $data['isPrimaryKey'] )
			{
				$outfield[] = $this->getFieldName( $field['name'] );
			}
		}
        
		$out[] = '(';
		$out[] = implode( ', ', $outfield );
		$out[] = ')';
		$out[] = 'VALUES';
		$out[] = implode( ', ', $values );
		if( $this->request->getIgnore() )
		{
			$out[] = 'ON CONFLICT DO NOTHING';
		}
		
		return implode(' ', $out ) . $this->getDelimiter();
	}

	public function SchemaColonne(\damix\engines\orm\request\structure\OrmTable $ormtable) : \damix\engines\orm\request\OrmRequest
	{
		
		$OrmSubRequest = new \damix\engines\orm\request\OrmRequest();
        $subrequest = $OrmSubRequest->createSelect();
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'TCO', 'CONSTRAINT_TYPE' );
        $subrequest->addDisplay($column);
		
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'KCU', 'TABLE_SCHEMA', 'TABLE_SCHEMA' );
        $subrequest->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'KCU', 'TABLE_NAME', 'TABLE_NAME' );
        $subrequest->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'KCU', 'COLUMN_NAME', 'COLUMN_NAME' );
        $subrequest->addDisplay($column);


		$table = \damix\engines\orm\request\structure\OrmTable::newTable( 'KEY_COLUMN_USAGE' );
        $table->setInternal( 'information_schema' );
        $join = $subrequest->addJoin( 'from', $table, 'KCU' );

		$table = \damix\engines\orm\request\structure\OrmTable::newTable( 'TABLE_CONSTRAINTS' );
        $table->setInternal( 'information_schema' );
		$join = $subrequest->addJoin( 'join', $table, 'TCO' );
        $join->addConditionField( 'KCU', 'constraint_schema', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'TCO', 'constraint_schema' );
        $join->addLogic( \damix\engines\orm\conditions\OrmOperator::ORM_OP_AND );
        $join->addConditionField( 'KCU', 'constraint_name', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'TCO', 'constraint_name' );
        $join->addLogic( \damix\engines\orm\conditions\OrmOperator::ORM_OP_AND );
        $join->addConditionString( 'TCO', 'constraint_type', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'PRIMARY KEY' );
		
		
		
		$OrmRequest = new \damix\engines\orm\request\OrmRequest();
        $request = $OrmRequest->createSelect();
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'TABLE_CATALOG', 'TABLE_CATALOG' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'TABLE_SCHEMA', 'TABLE_SCHEMA' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'TABLE_NAME', 'TABLE_NAME' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'COLUMN_NAME', 'COLUMN_NAME' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'ORDINAL_POSITION', 'ORDINAL_POSITION' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'COLUMN_DEFAULT', 'COLUMN_DEFAULT' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'IS_NULLABLE', 'IS_NULLABLE' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'DATA_TYPE', 'DATA_TYPE' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'CHARACTER_MAXIMUM_LENGTH', 'CHARACTER_MAXIMUM_LENGTH' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'CHARACTER_OCTET_LENGTH', 'CHARACTER_OCTET_LENGTH' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'NUMERIC_PRECISION', 'NUMERIC_PRECISION' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'NUMERIC_SCALE', 'NUMERIC_SCALE' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'DATETIME_PRECISION', 'DATETIME_PRECISION' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'CHARACTER_SET_NAME', 'CHARACTER_SET_NAME' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'COLUMNS', 'DATA_TYPE', 'COLUMN_TYPE' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        $column->setColumnField( '', 'KCU', 'CONSTRAINT_TYPE', 'COLUMN_KEY' );
        $request->addDisplay($column);
		
        $column = new \damix\engines\orm\request\structure\OrmColumn();
        // $column->setColumnField( '', 'KCU', 'CONSTRAINT_TYPE', 'EXTRA' );
		
		$formula = $column->setColumnFormula( 'if', 'EXTRA' );
		$formula->addParameterRaw( 'POSITION(\'nextval\' in column_default )' );
		$formula->addParameterOperator( \damix\engines\orm\conditions\OrmOperator::ORM_OP_GTEQ );
		$formula->addParameterRaw( 0 );
		$formula->addParameterValue( 'AUTO_INCREMENT' );
		$formula->addParameterValue( '0' );
		
        $request->addDisplay($column);
		
		
		
		// COLUMNS.IS_NULLABLE AS IS_NULLABLE, COLUMNS.DATA_TYPE AS DATA_TYPE, COLUMNS.CHARACTER_MAXIMUM_LENGTH AS CHARACTER_MAXIMUM_LENGTH, COLUMNS.CHARACTER_OCTET_LENGTH AS CHARACTER_OCTET_LENGTH, COLUMNS.NUMERIC_PRECISION AS NUMERIC_PRECISION, COLUMNS.NUMERIC_SCALE AS NUMERIC_SCALE, COLUMNS.DATETIME_PRECISION AS DATETIME_PRECISION, COLUMNS.CHARACTER_SET_NAME AS CHARACTER_SET_NAME, COLUMNS.DATA_TYPE AS COLUMN_TYPE, kcu.constraint_type
// from information_schema.columns COLUMNS
// left join (select kcu.constraint_schema, 
                  // kcu.constraint_name, 
                  // kcu.table_schema,
                  // kcu.table_name, 
                  // kcu.column_name, 
                  // kcu.ordinal_position,
                  // kcu.position_in_unique_constraint,
		   		  // tco.constraint_type
           // from information_schema.key_column_usage kcu
           // join information_schema.table_constraints tco
                // on kcu.constraint_schema = tco.constraint_schema
                // and kcu.constraint_name = tco.constraint_name
                // and tco.constraint_type = 'PRIMARY KEY'
          // ) as kcu
          // on COLUMNS.table_schema = kcu.table_schema
          // and COLUMNS.table_name = kcu.table_name
          // and COLUMNS.column_name = kcu.column_name
// left join information_schema.referential_constraints rco
          // on rco.constraint_name = kcu.constraint_name
          // and rco.constraint_schema = kcu.table_schema
// left join information_schema.key_column_usage rel
          // on rco.unique_constraint_name = rel.constraint_name
          // and rco.unique_constraint_schema = rel.constraint_schema
          // and rel.ordinal_position = kcu.position_in_unique_constraint
// where COLUMNS.table_schema in ('monschema') AND COLUMNS.table_name = 'film'

        
        $table = \damix\engines\orm\request\structure\OrmTable::newTable( 'COLUMNS' );
        $table->setInternal( 'information_schema' );
        $join = $request->addJoin( 'from', $table, 'COLUMNS' );
        
		$join = $request->addJoinSubrequest( 'subleft', $subrequest, 'KCU' );
		$join->addConditionField( 'COLUMNS', 'table_schema', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'KCU', 'table_schema' );
        $join->addLogic( \damix\engines\orm\conditions\OrmOperator::ORM_OP_AND );
        $join->addConditionField( 'COLUMNS', 'table_name', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'KCU', 'table_name' );
        $join->addLogic( \damix\engines\orm\conditions\OrmOperator::ORM_OP_AND );
        $join->addConditionField( 'COLUMNS', 'column_name', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'KCU', 'column_name' );
		
		$c = $request->getConditions();
		
		$schemaname = $ormtable->getSchema()?->getRealname();
		
		$c->addString( array( 'table' => 'COLUMNS', 'field' => 'TABLE_SCHEMA'), \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, ($this->isSchema() && !empty($schemaname)? $schemaname : $this->_cnx->getDatabase() ));
		$c->addString( array( 'table' => 'COLUMNS', 'field' => 'TABLE_NAME'), \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $ormtable->getRealname());



        return $request;
	}
}