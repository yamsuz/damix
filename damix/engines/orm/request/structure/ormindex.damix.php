<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmIndex
	extends OrmStructure
{
    protected string $name;
    protected OrmIndexType $type;
    protected array $fields = array();
    protected bool $ignore = false;
	
    public function setName(string $value) : void
	{
		$this->name = $value;
	}
	
    public function getName() : string
	{
		return $this->name;
	}
	
	public function setIndexType(OrmIndexType $value) : void
	{
		$this->type = $value;
	}
	
	public function getIndexType() : OrmIndexType
	{
		return $this->type;
	}
	
	public function addField(OrmField $field, \damix\engines\orm\request\structure\OrmOrderWay $way = \damix\engines\orm\request\structure\OrmOrderWay::WAY_ASC, int $length = -1) : void
	{
		$this->fields[] = array( 'field' => $field, 'way' => $way, 'length' => $length );
	}
	
	public function getFields() : array
	{
		return $this->fields;
	}
	
	public function setIgnore(bool $ignore) : void
	{
		$this->ignore = $ignore;
	}
	
	public function getIgnore() : bool
	{
		return $this->ignore;
	}
}