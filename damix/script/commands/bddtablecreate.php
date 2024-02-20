<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandBddtablecreate
	extends DamixCommands
{
	private string $application;
	
	public function execute(array $params)
	{
		$this->application = $params['a'] ?? null;
		
		if( $this->application === null )
		{
			self::display( 'Le nom de l\'application est obligatoire.' );
			return;
		}
	
		
		$ormdefinesbase = \damix\engines\orm\defines\OrmDefines::get();
		
		foreach( $ormdefinesbase->getDefines() as $define )
		{
			$orm = \damix\engines\orm\Orm::get( $define['selector'] );
			$orm->createTable( true );
		}
		
	}
	
}