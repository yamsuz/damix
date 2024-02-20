<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\databases\drivers;


class PgsqlDbResultSet
	extends \damix\engines\databases\DbResultSet
{
	
	protected function _free() : void{
		pg_free_result($this->_idResult);
	}
	protected function _rewind() : void{
		pg_result_seek($this->_idResult,0);
	}
	public function rowCount() : int|string{
		return pg_num_rows($this->_idResult);
	}
	public function columnCount() : int{
		return pg_num_fields($this->_idResult);
	}
	protected function _fetch() : object|null|false
	{
		return pg_fetch_object($this->_idResult);
	}
	public function columnNames() : array
	{
		$cols = array();
		$max = pg_num_fields( $this->_idResult );
		
		for( $i = 0; $i < $max; $i ++ )
		{
			$cols[] = array(
				'table' => pg_field_table($this->_idResult, $i),
				'alias_table' => pg_field_table($this->_idResult, $i),
				'name' => pg_field_name($this->_idResult, $i),
				'alias_name' => pg_field_name($this->_idResult, $i),
			);
		}
		return $cols;
	}
}