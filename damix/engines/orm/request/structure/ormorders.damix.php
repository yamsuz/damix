<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmOrders
    implements \Iterator
{
    private array $_orders = array();
    private int $_position = 0;
    
    public function __construct() 
	{
        $this->_position = 0;
    }

    public function rewind() : void
	{
        $this->_position = 0;
    }

    public function current() : OrmOrder 
	{
        return $this->_orders[$this->_position];
    }

    public function key() : int 
	{
        return $this->_position;
    }

    public function next() : void
	{
        ++$this->_position;
    }

    public function valid() : bool
	{
        return isset($this->_orders[$this->_position]);
    }
    
    public function getHashData() : array
    {
        return $this->_orders;
    }
    
    public function clear() : void
    {
        $this->_orders = array();
    }
    
    public function add( OrmOrder $property ) : void
    {
        $this->_orders[] = $property;
    }
    
    public function addOrder( OrmColumn|string $value, OrmOrderWay $way = OrmOrderWay::WAY_ASC ) : void
    {
        $order = new OrmOrder();
		$order->setColumn( $value );
		$order->setWay( $way );
		$this->add( $order );
    }
}