<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandModulecreate
	extends DamixCommands
{
	private string $application;
	
	public function execute(array $params)
	{
		$this->application = $params['a'] ?? null;
		$module = $params['m'] ?? null;
		
		
		if( $module === null )
		{
			self::display( 'Le nom du module est obligatoire.' );
			return;
		}
		
		$racine = $this->directory . $this->application;
		
		
		\damix\engines\tools\xFile::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'classes');
		\damix\engines\tools\xFile::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'controllers');
		\damix\engines\tools\xFile::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'locales' . DIRECTORY_SEPARATOR . 'fr_Fr');
		\damix\engines\tools\xFile::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'orm' . DIRECTORY_SEPARATOR . 'sorm');
		\damix\engines\tools\xFile::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'orm' . DIRECTORY_SEPARATOR . 'torm');
		\damix\engines\tools\xFile::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'templates');
		\damix\engines\tools\xFile::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'zones');
		
	}
	
}