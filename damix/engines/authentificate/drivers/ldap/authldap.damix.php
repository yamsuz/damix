<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\authentificate;


class AuthLdap
	extends \damix\engines\authentificate\AuthBase
{
	
	protected ?\damix\engines\tools\Ldap $ldap = null;
	protected ?\damix\auth\iUsers $Userdummy = null;
	
	public function __construct()
	{
		parent::__construct();
		$default = \damix\engines\settings\Setting::get('default');
		$classname = $default->get('auth', 'userdummy');
		
		$this->Userdummy = \damix\core\classes\Classe::get( $classname );
		
		$this->ldap = new \damix\engines\tools\Ldap();
		$this->ldap->connect();
	}
	
	public function getDriverName() : string
	{
		return 'ldap';
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
		try
		{
			if( $this->ldap->bind($login, $password) )
			{
				if( $this->findUser( $login ) )
				{
					$this->passwordsave($login, $password);
					return true;
				}
				else
				{
					$default = \damix\engines\settings\Setting::get('default');
					$ldapusercreate = $default->get('auth', 'ldapusercreate');
					if( tobool( $ldapusercreate ) )
					{
						return $this->userNew($login, $password);
					}
				}
			}
		}
		catch(\Exception $e)
		{
		}
		return false;
	}
	
	
	private function passwordsave( string $login, string $password ) : void
	{
		$default = \damix\engines\settings\Setting::get('default');
		$ldappasswordsave = $default->get('auth', 'ldappasswordsave');
		if( tobool( $ldappasswordsave ) )
		{
			if( $this->Userdummy->loadUser( $login ) )
			{
				$this->Userdummy->login = $login;
				$this->Userdummy->password = $this->cryptPassword( $password );
				$this->Userdummy->save();
			}
			
		}
	}
	
	public function userNew( string $login, string $password ) : bool
	{
		if( ! $this->Userdummy->loadUser( $login ) )
		{
			$this->Userdummy->clear();
			$this->Userdummy->idusers = null;
			$this->Userdummy->login = $login;
			
			$default = \damix\engines\settings\Setting::get('default');
			$ldappasswordsave = $default->get('auth', 'ldappasswordsave');
			if( tobool( $ldappasswordsave ) )
			{
				$this->Userdummy->password = $this->cryptPassword( $password );
			}
			else
			{
				$this->Userdummy->password = null;
			}
			$this->Userdummy->save();
			
			return true;
		}
		
		return false;
	}
	
	public function changePassword( string $login, string $password ) : bool
	{
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