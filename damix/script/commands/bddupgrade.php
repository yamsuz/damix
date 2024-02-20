<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandBddupgrade
	extends DamixCommands
{
	private string $application;
	
	public function execute(array $params)
	{
		$this->application = $params['a'] ?? null;
		
		if( $this->application === null )
		{
			$this->display( 'Le nom de l\'application est obligatoire.' );
			return;
		}
		
		$ormdefinesbase = \damix\engines\orm\defines\OrmDefines::get();
		
		
		$defines = $ormdefinesbase->getDefines();
		

		
		foreach( $defines as $define )
		{
			$orm = \damix\engines\orm\Orm::get( $define['selector'] );
			$orm->createTable( true );
		}
		
		foreach( $defines as $define )
		{
			$orm = \damix\engines\orm\Orm::get( $define['selector'] );
			$orm->alterTable( true );
		}
		
		foreach( $defines as $define )
		{
			$orm = \damix\engines\orm\Orm::get( $define['selector'] );
			$orm->alterIndexTable( true );
		}
		
		foreach( $defines as $define )
		{
			$orm = \damix\engines\orm\Orm::get( $define['selector'] );
			$orm->createTrigger();
		}
		
		
		
		
		$this->upgradesession();
		$this->upgradeacl();
		
		\damix\engines\orm\stored\OrmStored::CreateFunctions();
		\damix\engines\orm\stored\OrmStored::CreateProcedures();
		\damix\engines\orm\stored\OrmStored::CreateEvents();
	}
	
	private function upgradesession() : void
	{
		$driver = \damix\engines\settings\Setting::getValue('default', 'session', 'driver');
		if( $driver === 'db' )
		{
			$selector = \damix\engines\settings\Setting::getValue('default', 'session', 'tormselector');
			$orm = \damix\engines\orm\Orm::get( $selector );
			$orm->createTable( true );
			$orm->alterTable( true );
			$orm->alterIndexTable( true );
			$orm->createTrigger( true );
		}
	}
	
	private function upgradeacl() : void
	{
		$driver = \damix\engines\settings\Setting::getValue('default', 'acl', 'driver');
		if( $driver === 'db' )
		{
			$selectors = array();
			$selectors[] = \damix\engines\settings\Setting::getValue('default', 'acl', 'ormsubjects');
			$selectors[] = \damix\engines\settings\Setting::getValue('default', 'acl', 'ormrights');
			$selectors[] = \damix\engines\settings\Setting::getValue('default', 'acl', 'ormgroups');
			$selectors[] = \damix\engines\settings\Setting::getValue('default', 'acl', 'ormusersgroups');
			
			foreach( $selectors as $sel )
			{
				$orm = \damix\engines\orm\Orm::get( $sel );
				$orm->createTable( true );
			}
			
			foreach( $selectors as $define )
			{
				$orm = \damix\engines\orm\Orm::get( $sel );
				$orm->alterTable( true );
			}
			
			foreach( $selectors as $define )
			{
				$orm = \damix\engines\orm\Orm::get( $sel );
				$orm->alterIndexTable( true );
			}
			
			foreach( $selectors as $sel )
			{
				$orm = \damix\engines\orm\Orm::get( $sel );
				$orm->createTrigger();
			}
		}
	}
	
}