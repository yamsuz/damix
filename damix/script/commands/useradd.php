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
		
		
		$auth = \damix\engines\authentificate\Auth::get( );
		$auth->userNew($login, $password);
	}
		
}