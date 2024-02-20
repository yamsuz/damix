<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmOrder
{
    protected OrmColumn|string $column;
    protected ?OrmOrderWay $way = null;

	
	public function setColumn(\damix\engines\orm\request\structure\OrmColumn|string $value) : void
	{
		$this->column = $value;
	}
	public function getColumn() : \damix\engines\orm\request\structure\OrmColumn|string
	{
		return $this->column;
	}
	
	public function setWay(OrmOrderWay $value) : void
	{
		$this->way = $value;
	}
	
	public function getWay() : OrmOrderWay
	{
		return $this->way ?? OrmOrderWay::WAY_ASC;
	}
	
	public function getHashData()
    {
        return $this->_orders;
    }
}