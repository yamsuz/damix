<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request;


class OrmRequestStored
	extends OrmRequest
{
	protected string $name;
	protected string $content;
	protected string $intervalvaleur;
	protected string $intervalunite;
	protected \damix\engines\orm\request\structure\OrmTriggerEvent $event;
	protected \damix\engines\orm\request\structure\OrmTriggerAction $action;
	protected array $parameters = array();
	protected \damix\engines\orm\request\structure\OrmField $return;
	
	public function __construct()
	{
		$this->structure = new \damix\engines\orm\request\structure\OrmStored();
	}
	
	public function setName( string $value ) : void
	{
		$this->name = $value;
	}
	
	public function getName() : string
	{
		return $this->name;
	}
	
	public function setReturn( \damix\engines\orm\request\structure\OrmField $value ) : void
	{
		$this->return = $value;
	}
	
	public function getReturn() : \damix\engines\orm\request\structure\OrmField
	{
		return $this->return;
	}
	
	public function setIntervalValeur( string $value ) : void
	{
		$this->intervalvaleur = $value;
	}
	
	public function getIntervalValeur() : string
	{
		return $this->intervalvaleur;
	}
	
	public function setIntervalUnite( string $value ) : void
	{
		$this->intervalunite = $value;
	}
	
	public function getIntervalUnite() : string
	{
		return $this->intervalunite;
	}
	
	public function setSqlType( \damix\engines\orm\request\structure\OrmStructureType $value ) : void
	{
		$this->sqltype = $value;
	}
	
	public function getSqlType() : \damix\engines\orm\request\structure\OrmStructureType
	{
		return $this->sqltype;
	}
	
	public function setContent( string $value ) : void
	{
		$this->content = $value;
	}
	
	public function getContent() : string
	{
		return $this->content;
	}
	
	public function addParameter( \damix\engines\orm\request\structure\OrmField $value ) : void
	{
		$this->parameters[] = $value;
	}
	
	public function getParameters() : array
	{
		return $this->parameters;
	}

	public function setEvent( \damix\engines\orm\request\structure\OrmTriggerEvent $value ) : void
	{
		$this->event = $value;
	}
	
	public function getEvent() : \damix\engines\orm\request\structure\OrmTriggerEvent
	{
		return $this->event;
	}
	
	public function setAction( \damix\engines\orm\request\structure\OrmTriggerAction $value ) : void
	{
		$this->action = $value;
	}
	
	public function getAction() : \damix\engines\orm\request\structure\OrmTriggerAction
	{
		return $this->action;
	}
		
}