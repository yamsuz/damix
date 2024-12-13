<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandUsergroupadd
	extends DamixCommands
{
	private string $application;
	
	public function execute(array $params)
	{
		$this->application = $params['a'] ?? null;
		$login = $params['u'] ?? null;
		$driver = $params['d'] ?? null;
		$group = $params['g'] ?? null;
		
		if( $login === null )
		{
			self::display( 'Le login est obligatoire.');
			return;
		}
		
		if( $group === null )
		{
			self::display( 'Le group est obligatoire.');
			return;
		}
		
		
		$acl = \damix\engines\acls\Acl::get( );
		$acl->addusergroup( $login, $group);
	}
		
}