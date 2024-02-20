<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandTemplatecreate
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
		
		$template = $params['c'] ?? null;
		
		if( $template === null )
		{
			self::display( 'Le nom du template est obligatoire.' );
			return;
		}
		
		
		$content = array();

		$content[] = '{literal}';
		$content[] = '<script type="text/javascript">';
		$content[] = '';
		$content[] = '</script>';
		$content[] = '{/literal}';


		
		$filename = $this->directory . $application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template . '.tpl';
		
		\damix\engines\tools\xFile::write($filename, implode( "\r\n", $content ));
		
	}
}