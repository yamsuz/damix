<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\logs;


abstract class LogBase
{
	protected \damix\engines\logs\LogMessage $message;
	public LogSelector $selector;
	
	abstract public function write( string $category ) :bool;
	
	public function setMessage( mixed $message )
	{
		$this->message = new \damix\engines\logs\LogMessage();
		$this->message->setMessage( $message );
	}
	
	public function setMessageObject( mixed $message )
	{
		if( $message instanceof LogMessage )
		{
			$this->message = $message;
		}
		else
		{
			$this->message = new \damix\engines\logs\LogMessage( $message );
			$this->message->setMessageObject( $message );
		}
	}
	
}