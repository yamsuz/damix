<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmContraint
{
    protected string $name;
    protected \damix\engines\orm\request\structure\OrmField $foreign;
    protected \damix\engines\orm\request\structure\OrmField $reference;
    protected ?\damix\engines\orm\request\structure\OrmContraintType $update;
    protected ?\damix\engines\orm\request\structure\OrmContraintType $delete;
	protected bool $ignore = false;
	
	public function __construct()
	{
		$this->update = null;
		$this->delete = null;
	}
	
	public function setName(string $value)
	{
		$this->name = $value;
	}
	
	public function getName() : string
	{
		return $this->name;
	}
	
	public function setForeign(\damix\engines\orm\request\structure\OrmField $value)
	{
		$this->foreign = $value;
	}
	
	public function getForeign() : \damix\engines\orm\request\structure\OrmField
	{
		return $this->foreign;
	}
	
	public function setReference(\damix\engines\orm\request\structure\OrmField $value)
	{
		$this->reference = $value;
	}
	
	public function getReference() : \damix\engines\orm\request\structure\OrmField
	{
		return $this->reference;
	}
	
	public function setUpdate(\damix\engines\orm\request\structure\OrmContraintType $value)
	{
		$this->update = $value;
	}
	
	public function getUpdate() : ?\damix\engines\orm\request\structure\OrmContraintType
	{
		return $this->update;
	}
	
	public function setDelete(\damix\engines\orm\request\structure\OrmContraintType $value)
	{
		$this->delete = $value;
	}
	
	public function getDelete() : ?\damix\engines\orm\request\structure\OrmContraintType
	{
		return $this->delete;
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