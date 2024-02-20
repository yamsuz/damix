<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmField
{
    protected string $name;
    protected string $realname;
    protected string $reference;
	protected OrmDataType $datatype;
	protected bool $null;
	protected int $size;
	protected mixed $default;
	protected int $precision;
	protected bool $unsigned = false;
	protected bool $autoincrement = false;
	protected bool $primarykey = false;
	protected array $enumerate = array();
	protected int $position;
	protected OrmTable $table;
    
	public function __construct()
	{
		$this->null = true;
		$this->size = 0;
		$this->default = '';
		$this->precision = 0;
		$this->unsigned = false;
		$this->enumerate = array();
	}
	
	public function setReference(string $value) : void
	{
		$this->reference = $value;
	}
	
	public function getReference() : string
	{
		return $this->reference;
	}
	
	public function isReference() : bool
	{
		return !empty( $this->reference );
	}

	public function setName(string $value) : void
	{
		$this->name = $value;
		if( empty( $this->realname ) )
		{
			$this->realname = $value;
		}
	}
	public function getName() : string
	{
		return $this->name;
	}
	
	public function setRealname(string $value) : void
	{
		$this->realname = $value;
	}
	public function getRealname() : string
	{
		return $this->realname;
	}

	public function setDatatype(OrmDataType $value) : void
	{
		$this->datatype = $value;
		
		switch( $value )
		{
			case OrmDataType::ORM_VARCHAR;
				if( $this->size == 0)
				{
					$this->size = 255;
				}
				break;
			// case OrmDataType::ORM_CHAR;
				// break;
			// case OrmDataType::ORM_INT;
				// break;
			// case OrmDataType::ORM_LONG;
				// break;
			// case OrmDataType::ORM_FLOAT;
				// break;
			case OrmDataType::ORM_DECIMAL;
				if( $this->size == 0)
				{
					$this->size = 20;
				}
				if( $this->precision == 0)
				{
					$this->precision = 6;
				}
				break;
			// case OrmDataType::ORM_DATE;
				// break;
			// case OrmDataType::ORM_TIME;
				// break;
			// case OrmDataType::ORM_DATETIME;
				// break;
			// case OrmDataType::ORM_BOOL;
				// break;
		}
		
	}
	public function getDatatype() : OrmDataType
	{
		return $this->datatype;
	}

	public function setNull(bool $value) : void
	{
		$this->null = $value;
	}
	public function getNull() : bool
	{
		return $this->null;
	}
	
	public function setTable(OrmTable $table) : void
	{
		$this->table = $table;
	}
	
	public function getTable() : ?OrmTable
	{
		return $this->table ?? null;
	}
	
	public function setPrimaryKey(bool $value) : void
	{
		$this->primarykey = $value;
	}
	public function getPrimaryKey() : bool
	{
		return $this->primarykey;
	}

	public function setAutoincrement(bool $value) : void
	{
		$this->autoincrement = $value;
	}
	public function getAutoincrement() : bool
	{
		return $this->autoincrement;
	}

	public function setSize(int $value) : void
	{
		$this->size = $value;
	}
	public function getSize() : int
	{
		return $this->size;
	}
	
	public function setDefault(mixed $value) : void
	{
		$this->default = $value;
	}
	public function getDefault() : mixed
	{
		return $this->default;
	}
	
	public function setPrecision(int $value) : void
	{
		$this->precision = $value;
	}
	public function getPrecision() : int
	{
		return $this->precision;
	}
	
	public function setPosition(int $value) : void
	{
		$this->position = $value;
	}
	public function getPosition() : int
	{
		return $this->position;
	}

	public function setUnsigned(bool $value) : void
	{
		$this->unsigned = $value;
	}
	public function getUnsigned() : bool
	{
		return $this->unsigned;
	}

	public function setEnumerate(array $value) : void
	{
		$this->enumerate = $value;
	}
	public function getEnumerate() : array
	{
		return $this->enumerate;
	}
	
}