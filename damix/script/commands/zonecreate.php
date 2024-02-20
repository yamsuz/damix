<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandZonecreate
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
		
		$zone = $params['c'] ?? null;
		
		if( $zone === null )
		{
			self::display( 'Le nom de la zone est obligatoire.' );
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
		$content[] = 'class ' . $zone;
		$content[] = '		extends \damix\engines\zones\ZoneBase';
		$content[] = '{';
		$content[] = '';
		$content[] = '	protected string $tplSelector = \'' . $module . '~tpl' . $zone . '\';';
		$content[] = '';
		$content[] = '	protected function prepareTpl() : void ';
		$content[] = '	{';
		$content[] = '		$this->Tpl->assignParameter( \'param1\', \'value1\' );';
		$content[] = '	}';
		$content[] = '';
		$content[] = '}';
		
		$filename = $this->directory . $application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'zones' . DIRECTORY_SEPARATOR . $zone . '.class.php';
		
		\damix\engines\tools\xFile::write($filename, implode( "\r\n", $content ));
		
	}
}