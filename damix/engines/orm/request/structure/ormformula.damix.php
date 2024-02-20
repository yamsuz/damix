<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmFormula
{
    protected string $name;
    protected array $parameters = array();
    
	
	public function setName(string $value) : void
	{
		$this->name = $value;
	}
	
	public function getName() : string
	{
		return $this->name;
	}
	
	public function addParameter( OrmColumn $column ) : void
	{
		$this->parameters[] = $column;
	}
	
	public function getParameters() : array
	{
		return $this->parameters;
	}
	
	public function addParameterArray( array $params ) : void
	{
		foreach( $params as $param )
		{
			switch( $param['type'] )
			{
				case 'property':
					$this->addParameterField( $param['table'], $param['property'], $param['ref'] );
					break;
			}
		}
	}
	
	public function addParameterField(  string $table, string $name, string $ref = '' ) : void
	{
		$col = new OrmColumn();
		$col->setColumnField($ref, $table, $name);
		
		$this->addParameter( $col );
	}
	
	public function addParameterComma() : void
	{
		$col = new OrmColumn();
		$col->setColumnType( \damix\engines\orm\request\structure\OrmColumnType::COLUMN_COMMA );
		
		$this->addParameter( $col );
	}
	
	public function addParameterRaw( mixed $value ) : void
	{
		$col = new OrmColumn();
		$col->setColumnType( \damix\engines\orm\request\structure\OrmColumnType::COLUMN_RAW );
		
		$val = new \damix\engines\orm\request\structure\OrmValue();
		$val->setValue($value);
		$col->setColumnRaw( $val );
		
		$this->addParameter( $col );
	}
	
	public function addParameterFormula( mixed $value ) : void
	{
		$col = new OrmColumn();
		$col->setColumnType( \damix\engines\orm\request\structure\OrmColumnType::COLUMN_FORMULA );
		
		$val = new \damix\engines\orm\request\structure\OrmValue();
		$val->setValue($value);
		$col->setColumnRaw( $val );
		
		$this->addParameter( $col );
	}
	
	public function addParameterOperator(\damix\engines\orm\conditions\OrmOperator $operator) : void
	{
		$col = new OrmColumn();
		
		$val = new \damix\engines\orm\request\structure\OrmValue();
		$val->setValue( $operator );
		$col->setColumnOperator( $val );
		
		$this->addParameter( $col );
	}
	
	public function addParameterValue( mixed $value ) : void
	{
		$col = new OrmColumn();
		if( $value instanceof OrmValue )
		{
			$col->setColumnValue( $value );
		}
		else
		{
			$val = new \damix\engines\orm\request\structure\OrmValue();
			$val->setValue($value) ;
			$col->setColumnValue( $val );
		}
		$col->setColumnType( \damix\engines\orm\request\structure\OrmColumnType::COLUMN_VALUE );
		$this->addParameter( $col );
	}
}