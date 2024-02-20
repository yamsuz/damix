<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandAppdelete
	extends DamixCommands
{
	public function execute(array $params)
	{
		$application = $params['a'] ?? null;
		
		if( $application === null )
		{
			self::display( 'Le nom de l\'application est obligatoire.' );
			return;
		}
		
		\damix\engines\tools\xFile::deleteDir( $this->directory . $application );
	}
}