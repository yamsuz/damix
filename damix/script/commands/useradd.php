<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandUseradd
	extends DamixCommands
{
	private string $application;
	
	public function execute(array $params)
	{
		$this->application = $params['a'] ?? null;
		$login = $params['u'] ?? null;
		$password = $params['p'] ?? null;
		$driver = $params['d'] ?? null;
		
		if( $login === null )
		{
			self::display( 'Le login est obligatoire.');
			return;
		}
		
		if( $password === null )
		{
			self::display( 'Le mot de passe est obligatoire.');
			return;
		}
		
		
		$auths = \damix\engines\authentificate\Auth::get( );
		if( $auths->count() > 1) 
		{
			if( $driver === null )
			{
				self::display( 'Le nom du driver est obligatoire.');
				return;
			}
			
			
			
			foreach( $auths as $auth )
			{
				if( $auth->getDriverName() == $driver )
				{
					$auth->userNew($login, $password);					
				}
			}
		}
		else
		{
			$auths->userNew($login, $password);
		}
	}
		
}