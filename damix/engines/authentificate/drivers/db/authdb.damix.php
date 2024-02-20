<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\authentificate;


class AuthDb
	extends \damix\engines\authentificate\AuthBase
{
	
	protected ?\damix\auth\Userdummy $Userdummy = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$default = \damix\engines\settings\Setting::get('default');
		$classname = $default->get('auth', 'userdummy');
		$this->Userdummy = \damix\core\classes\Classe::get( $classname );
	}
	
	public function setup() : void
	{
		$this->Userdummy->setup();
	}
	
	protected function findUser(string $login) : bool
	{
		return $this->Userdummy->loadUser( $login );
	}
	
	public function verifyPassword( string $login, string $password ) : bool
	{
		if( $this->findUser( $login ) )
		{
			if( $this->checkPassword( $this->Userdummy->getPassword(), $password ) )
			{
				return true;
			}
		}
		return false;
	}
	
	public function userNew( string $login, string $password ) : bool
	{
		if( ! $this->Userdummy->loadUser( $login ) )
		{
			$this->Userdummy->idusers = null;
			$this->Userdummy->login = $login;
			$this->Userdummy->password = $this->cryptPassword($password);
			$this->Userdummy->save();
			
			return true;
		}
		
		return false;
	}
	
	public function changePassword( string $login, string $password ) : bool
	{
		if( $this->Userdummy->loadUser( $login ) )
		{
			$this->Userdummy->password = $this->cryptPassword($password);
			$this->Userdummy->save();
		}
		
		return true;
	}
	
	public function userDelete( string $login ) : bool
	{
		if( $this->Userdummy->loadUser( $login ) )
		{
			$this->Userdummy->delete( $this->Userdummy->idusers );
			return true;
		}
		
		return false;
	}
	
	
}