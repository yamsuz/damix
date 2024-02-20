<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandPagecreate
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
		
		$name = $params['c'] ?? null;
		
		if( $name === null )
		{
			self::display( 'Le nom de la name est obligatoire.' );
			return;
		}
		
		DamixCommands::load('controllercreate');
		DamixCommands::load('zonecreate');
		DamixCommands::load('templatecreate');
		
		DamixCommands::execcommande('controllercreate', array( 'a' => $application, 'm' => $module, 'c' => 'ctr' . $name ) );
		DamixCommands::execcommande('zonecreate', array( 'a' => $application, 'm' => $module, 'c' => 'zon' . $name ) );
		DamixCommands::execcommande('templatecreate', array( 'a' => $application, 'm' => $module, 'c' => 'tpl' . $name ) );
		
	}
}