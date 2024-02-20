<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmColumn
{
    protected string $alias;
    protected OrmColumnType $columntype;
    protected OrmField $field;
    protected OrmFormula $formula;
    protected OrmValue $value;
		
	public function setAlias(string $value) : void
	{
		if( ! empty( $value ) )
		{
			$this->alias = $value;
		}
	}
	
	public function getAlias() : string
	{
		return $this->alias;
	}
	
	public function setColumnType(OrmColumnType $value) : void
	{
		$this->columntype = $value;
	}
	
	public function getColumnType() : OrmColumnType
	{
		return $this->columntype;
	}
	
	public function getFormula() : OrmFormula
	{
		return $this->formula;
	}
	
	public function setColumnField(string $ref, string $table, string $name, string $alias = '') : void
	{
		$table = \damix\engines\orm\request\structure\OrmTable::newTable( $table );
		$table->setReference( $ref );
		$field = new OrmField();
		$field->setName( $name );
		$field->setTable( $table );
		$field->setReference( $ref );
		$this->setColumnType( \damix\engines\orm\request\structure\OrmColumnType::COLUMN_FIELD );
		$this->setAlias( ( empty( $alias ) ? $name : $alias ) );
		$this->setField( $field );
	}
	
	public function setColumnValue(OrmValue $value) : void
	{
		$this->value = $value;
		$this->setColumnType( \damix\engines\orm\request\structure\OrmColumnType::COLUMN_VALUE );
	}
	
	public function setColumnRaw(OrmValue $value) : void
	{
		$this->value = $value;
		$this->setColumnType( \damix\engines\orm\request\structure\OrmColumnType::COLUMN_RAW );
	}
	
	public function setColumnOperator(OrmValue $value) : void
	{
		$this->value = $value;
		$this->setColumnType( \damix\engines\orm\request\structure\OrmColumnType::COLUMN_OPERATOR  );
	}
	
	public function getColumnRaw() : OrmValue
	{
		return $this->value;
	}
	
	public function setColumnFormula(string $name, string $alias) : OrmFormula
	{
		$this->setColumnType( \damix\engines\orm\request\structure\OrmColumnType::COLUMN_FORMULA );
		$this->setAlias( $alias );
		$this->formula = new OrmFormula();
		$this->formula->setName( $name );
		return $this->formula;
	}
	
	public function setField(OrmField $value) : void
	{
		$this->field = $value;
	}
	
	public function getField() : OrmField
	{
		return $this->field;
	}
	
}