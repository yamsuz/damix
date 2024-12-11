<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmGroups
    implements \Iterator
{
    private array $_groups = array();
    private int $_position = 0;
    
    public function __construct() 
	{
        $this->_position = 0;
    }

    public function rewind() : void
	{
        $this->_position = 0;
    }

    public function current() : OrmGroup 
	{
        return $this->_groups[$this->_position];
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
        return isset($this->_groups[$this->_position]);
    }
    
    public function getHashData() : array
    {
        return $this->_groups;
    }
    
    public function getGroups() : array
    {
        return $this->_groups;
    }
    
    public function clear() : void
    {
        $this->_groups = array();
    }
    
    public function add( OrmGroup $property ) : void
    {
        $this->_groups[] = $property;
    }
    
    public function merge( OrmGroups $groups ) : void
    {
        $this->_groups = array_merge($this->_groups, $groups->getGroups());
    }
}