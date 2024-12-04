<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandClasscreate
	extends DamixCommands
{
	private string $application;
	
	public function execute(array $params)
	{
		$application = $params['a'] ?? null;
		
		if( $application === null )
		{
			self::display( 'Le nom de l\'application est obligatoire.' );
			return;
		}
		
		$module = $params['m'] ?? null;
		
		if( $module === null )
		{
			self::display( 'Le nom du module est obligatoire.' );
			return;
		}
		
		$classe = $params['c'] ?? null;
		
		if( $classe === null )
		{
			self::display( 'Le nom de la classe est obligatoire.' );
			return;
		}
		
		
		$content = array();
		
		$content[] = '<?php';
		$content[] = '/**';
		$content[] = '* @package      ' . $application;
		$content[] = '* @Module       ' . $module;
		$content[] = '* @author       Damix';
		$content[] = '* @copyright    2023';
		$content[] = '*/';
		$content[] = '';
		
		$content[] = 'declare(strict_types=1);';
		$content[] = '';
		$content[] = 'namespace '. $application .'\\'. $module .';';
		$content[] = '';
		$content[] = 'class ' . $classe;
		$content[] = '{';
		$content[] = '';
		$content[] = '';
		$content[] = '}';
		
		$filename = $this->directory . $application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $classe . '.class.php';
		
		\damix\engines\tools\xFile::write($filename, implode( "\r\n", $content ));
		
	}
	
}