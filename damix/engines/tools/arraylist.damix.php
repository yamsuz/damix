<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\tools;

abstract class Arraylist
	implements \ArrayAccess, \Iterator, \Countable
{	
    private array $container = array(); //An Array of your actual values.
	private array $keys = array();   
	private int $position;            //have an associative array.

	public function __construct() {
		$position = 0;
	}

	public function count() : int { //This is necessary for the Countable interface. It could as easily return
		return count($this->keys);    //count($this->container). The number of elements will be the same.
	}

	public function rewind() :void{  //Necessary for the Iterator interface. $this->position shows where we are in our list of
		$this->position = 0;      
	}

	public function current():mixed { //Necessary for the Iterator interface.
		return $this->container[$this->keys[$this->position]];
	}

	public function key():mixed { //Necessary for the Iterator interface.
		return $this->keys[$this->position];
	}

	public function next() :void{ //Necessary for the Iterator interface.
		++$this->position;
	}

	public function valid():bool { //Necessary for the Iterator interface.
		return isset($this->keys[$this->position]);
	}

	public function offsetSet(mixed $offset, mixed $value):void { //Necessary for the ArrayAccess interface.
		if(is_null($offset)) 
		{
			$this->container[] = $value;
			$this->keys[] = array_key_last($this->container); //THIS IS ONLY VALID FROM php 7.3 ONWARDS. See note below for alternative.
		} 
		else 
		{
			$this->container[$offset] = $value;
			if(!in_array($offset, $this->keys))
			{
				$this->keys[] = $offset;
			}
		}
	}

	public function offsetExists(mixed $offset): bool {
		return isset($this->container[$offset]);
	}

	public function offsetUnset(mixed $offset):void {
		unset($this->container[$offset]);
		unset($this->keys[array_search($offset,$this->keys)]);
		$this->keys = array_values($this->keys);
	}
	
	public function offsetGet(mixed $offset) :mixed {
		return isset($this->container[$offset]) ? $this->container[$offset] : null;
	}
}