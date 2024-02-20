<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmValue
{
    protected mixed $value;
    protected ?OrmDataType $datatype = null;

	
	public function setValue(mixed $value) : void
	{
		$this->value = $value;
		
		if( ! $this->datatype )
		{
			if( is_bool( $value ) )
			{
				$this->setDatatype( \damix\engines\orm\request\structure\OrmDataType::ORM_BOOL );
			}
			elseif( is_scalar( $value ) )
			{
				$this->setDatatype( \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR );
			}
			elseif( $value instanceof \damix\engines\tools\xDate )
			{
				$this->setDatatype( \damix\engines\orm\request\structure\OrmDataType::ORM_DATETIME );
			}
			elseif( $value instanceof \stdClass )
			{
				$this->setDatatype( \damix\engines\orm\request\structure\OrmDataType::ORM_OBJECT );
			}
		}
	}
	public function getValue() : mixed
	{
		return $this->value;
	}
	
	public function setDatatype(OrmDataType $value) : void
	{
		$this->datatype = $value;
	}
	
	public function getDatatype() : OrmDataType
	{
		return $this->datatype;
	}
}