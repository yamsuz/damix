<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmRow
{
	protected array $values = array();
    
	public function getFields() : array
	{
		$out = array();
		foreach( $this->values as $col )
		{
			$out[] = $col;
		}
		return $out;
	}
	
	public function addValue(string $field, mixed $value, \damix\engines\orm\request\structure\OrmDataType $datatype, bool $isPrimaryKey) : void
	{
		$this->values[ $field ] = array( 'name' => $field, 'datatype' => $datatype, 'value' => $value, 'isPrimaryKey' => $isPrimaryKey );
	}
	
	public function getValue(string $field) : array
	{
		return $this->values[ $field ] ?? null;
	}
	
}