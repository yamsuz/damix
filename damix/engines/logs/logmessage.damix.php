<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\logs;


class LogMessage
{
	public string $message;

	public function __construct()
	{
	}
	
	public function setMessage( mixed $message )
	{
		$this->message = strval($message);
	}
	
	public function setMessageObject( mixed $message )
	{
		$this->message = var_export($message, true);
	}
	
	public function getFormated() : string 
	{
		return $this->message;
	}
}