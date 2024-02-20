<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandCacheclear
	extends DamixCommands
{
	private string $application;
	
	public function execute(array $params)
	{
		$this->application = $params['a'] ?? null;
		
		
		$racine = $this->directory . $this->application . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'compiled';
		\damix\engines\tools\xFile::deleteDir( $racine );
	}
	
}