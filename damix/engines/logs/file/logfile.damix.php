<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\logs;


class LogFile
	extends \damix\engines\logs\LogBase
{
    public function write( string $category ) : bool
	{
		$c = \damix\engines\settings\Setting::get('default');
		
		$file = $c->get( 'logfile', $category );
		
		if( $file === null )
		{
			$file = $c->get( 'logfile', 'default' );
		}
		
		$filename = $this->selector->getFolder() . $file;
		
		$message = \damix\engines\tools\xDate::loadformat( 'now', \damix\engines\tools\xDate::ISO_8601_DT ) . "\t". \damix\engines\tools\xTools::getIp() ."\t" . $category ."\t". $this->message->getFormated()."\n";
		
		@error_log($message, 3, $filename);
				
		return true;
	}
	
}