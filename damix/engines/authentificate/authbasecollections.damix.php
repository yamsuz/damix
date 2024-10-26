<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\authentificate;


class AuthBaseCollections
	extends \damix\engines\tools\Arraylist
{
	public function add( \damix\engines\authentificate\AuthBase $auth ) : void
	{
		$this[] = $auth;
	}
	
	public function setup() : void
	{
		foreach( $this as $auth )
		{
			$auth->setup();
		}
	}
	
	public function changePassword( string $login, string $password ) : bool
	{
		if( $this->count() == 1 )
		{
			return $this[0]->changePassword($login, $password);
		}
		
		return false;
	}
	
	public function userNew( string $login, string $password ) : bool
	{
		if( $this->count() == 1 )
		{
			return $this[0]->userNew($login, $password);
		}
		
		return false;
	}
	
	public function userDelete( string $login ) : bool
	{
		if( $this->count() == 1 )
		{
			return $this[0]->userDelete($login);
		}
		
		return false;
	}
	
	public function verifyPassword( string $login, string $password ) : bool
	{
		if( $this->count() == 1 )
		{
			return $this[0]->verifyPassword($login, $password);
		}
		
		return false;
	}
	
	public function getDriverName() : string
	{
		if( $this->count() == 1 )
		{
			return $this[0]->getDriverName();
		}
		
		return '';
	}
	
	public function cryptPassword( string $password ) : string
	{
		if( $this->count() == 1 )
		{
			return $this[0]->cryptPassword($password);
		}
		
		return '';
	}
	
	public function checkPassword( string $passwordhash, string $password ) : bool
	{
		if( $this->count() == 1 )
		{
			return $this[0]->checkPassword($passwordhash, $password);
		}
		
		return false;
	}
}