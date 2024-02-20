<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandControllercreate
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
		
		$module = $params['m'] ?? null;
		
		if( $module === null )
		{
			self::display( 'Le nom du module est obligatoire.' );
			return;
		}
		
		$controller = $params['c'] ?? null;
		
		if( $controller === null )
		{
			self::display( 'Le nom du controller est obligatoire.' );
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
		$content[] = 'class ' . $controller;
		$content[] = '	extends \damix\core\controllers\Controller';
		$content[] = '{';
		$content[] = '';
		$content[] = '	public function index()';
		$content[] = '	{';
		$content[] = '		$rep = $this->getResponse(\'html\');';
		$content[] = '		$rep->setTitle( \'Ma Page\' );';
		$content[] = '		$rep->setBodyTpl( \''.$module.'~tplhome\' );';
		$content[] = '		return $rep;';
		$content[] = '	}';
		$content[] = '';
		$content[] = '}';
		
		$filename = $this->directory . $application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controller . '.class.php';
		
		\damix\engines\tools\xFile::write($filename, implode( "\r\n", $content ));
		
	}
}