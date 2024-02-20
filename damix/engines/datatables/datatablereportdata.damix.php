<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\datatables;

class DatatableReportData
    implements \Iterator
{
    private $_position = 0;
    protected $_rows = array();
    protected $_positionRows = array();
    protected $_max = 0;
    protected $_cols = array();
    
    
    public function __construct() {
        $this->_position = 0;
    }

    public function rewind() {
        $this->_position = 0;
    }

    public function current() {
        return $this->_rows[$this->_position];
    }

    public function key() {
        return $this->_position;
    }

    public function next() {
        ++$this->_position;
    }

    public function valid() {
        return isset($this->_rows[$this->_position]);
    }
    
	public function getCols()
	{
		return array_values($this->_cols);
	}
	
    public function addValue($row, $col, $value)
    {
		if( $row === null)
		{
			$row = '';
		}
		if( $col === null)
		{
			$col = '';
		}
		
		$row_name = $row['name'];
		$row_type = $row['type'] ?? 'string';
		
		// ksort($row);
		// ksort($col);
		$idrow = md5(serialize($row_name));
		$idcol = md5(serialize($col));
		
		$position = 0;
		if( array_key_exists($idrow, $this->_positionRows ))
		{
			$position = $this->_positionRows[$idrow];
		}
		else
		{
			$position = $this->_max;
			$this->_max ++;
			$this->_positionRows[$idrow] = $position;
			$this->_rows[$position] = new \stdClass();
		}
		if( isset( $this->_rows[$position]->$idcol ) )
		{
			$this->_rows[$position]->$idcol['value'][] = $value;
		}
		else
		{
			$this->_rows[$position]->$idcol = array( 'row'=> $row_name, 'type'=> $row_type, 'col'=> $col, 'value'=> array() );
			$this->_rows[$position]->$idcol['value'][] = $value;
		}
		
		if( ! isset( $this->_cols[$idcol] ) )
		{
			$this->_cols[$idcol] = $col;
		}
    }
}