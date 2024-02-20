<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\databases\drivers;


class MariadbDbResultSet
	extends \damix\engines\databases\DbResultSet
{
	
	protected function _free() : void{
		@$this->_idResult->free_result();
	}
	protected function _rewind() : void{
		@$this->_idResult->data_seek(0);
	}
	public function rowCount() : int|string{
		return $this->_idResult->num_rows;
	}
	public function columnCount() : int{
		return $this->_idResult->field_count;
	}
	protected function  _fetch() : object|null|false
	{
		return $this->_idResult->fetch_object();
	}
	public function columnNames() : array
	{
		$cols = array();
		
		foreach( $this->_idResult->fetch_fields() as $field )
		{
			$cols[] = array(
				'table' => $field->orgtable,
				'alias_table' => $field->table,
				'name' => $field->orgname,
				'alias_name' => $field->name,
			);
		}
		return $cols;
	}
}