<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\authentificate;


abstract class AuthBase
{
	protected string $password_salt;
	protected string $passwordHashOptions;
	
	abstract public function changePassword( string $login, string $password ) : bool;
	abstract public function userNew( string $login, string $password ) : bool;
	abstract public function userDelete( string $login ) : bool;
	abstract public function verifyPassword( string $login, string $password ) : bool;
	
	public function __construct()
	{
		$config = \damix\engines\settings\Setting::get('default' );;
		$this->password_salt = $config->get( 'auth', 'password_salt');
	}
	
	public function cryptPassword( string $password ) : string
	{
		$hash=crypt($password,$this->password_salt);
		return substr($hash,strrpos($hash,'$')+strlen($this->password_salt));
	}
	
	public function checkPassword( string $passwordhash, string $password ) : bool
	{
		if( hash_equals( $passwordhash, $this->cryptPassword($password) ))
		{
			return true;
		}
		
		return false;
	}
	
	public function setup() : void
	{
	}
}