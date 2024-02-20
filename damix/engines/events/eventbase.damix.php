<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\events;


abstract class EventBase
{
	protected array $events = array();
	
	public function __construct()
    {
       
    }
	
	public function performEvent( string $name, array $params ) : mixed
	{
		$methodName='on' . $name;
		return $this->$methodName( $params );
	}
	
	public function getSelectorClasse( $event ) : array
	{
		return $this->events[ $event ] ?? array();
	}
}