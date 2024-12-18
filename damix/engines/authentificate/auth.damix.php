<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\authentificate;


class Auth
{

    static protected $_singleton=array();
	
	public static function get() : AuthBaseCollections
    {
		$selectors = \damix\engines\settings\Setting::getValue('default', 'auth', 'driver');
		$auths = new AuthBaseCollections();
		$liste = preg_split('/;/', $selectors );
		foreach( $liste as $selector )
		{
			$sel = new AuthSelector($selector);

			if(! isset(self::$_singleton[$selector])){
				self::$_singleton[$selector] = self::create( $sel );
			}
			$obj = self::$_singleton[$selector];
			$auths->add( $obj );
		}
		
		return $auths;
    }

    private static function create( AuthSelector $selector ) : AuthBase
    {
        $classname = $selector->getFullNamespace();
		
		if( ! class_exists($classname,false ) )
        {
			$temp = $selector->getPath();
			require_once( $temp );
		}
        
        $obj = new $classname();
        return $obj;
    }
	
	public static function isConnected() : bool
	{
		$sessionname = \damix\engines\settings\Setting::getValue('default', 'auth', 'sessionname');
		
		return ( isset( $_SESSION[$sessionname] ) && ! empty($_SESSION[$sessionname]->login ) );
	}
	
	public static function login(string $user, string $password) : bool
	{
		$auths = self::get();
		
		if( empty( $password ) )
		{
			return false;
		}

		foreach( $auths as $auth )
		{
			if( $auth->verifyPassword( $user, $password ) )
			{
				$userdummy = \damix\engines\settings\Setting::getValue('default', 'auth', 'userdummy');
				$objUser = \damix\core\classes\Classe::get( $userdummy );
				
				$objUser->login = $user;
				
				$sessionname = \damix\engines\settings\Setting::getValue('default', 'auth', 'sessionname');
				$_SESSION[$sessionname] = $objUser;
				
				\damix\engines\events\Event::notify( 'authlogin', array('user' => $objUser, 'password' => $password));
				
				return true;
			}
		}
		
		return false;
	}
	
	public static function logout() : bool
	{
		$userdummy = \damix\engines\settings\Setting::getValue('default', 'auth', 'userdummy');
		$objUser = \damix\core\classes\Classe::get( $userdummy );
		$sessionname = \damix\engines\settings\Setting::getValue('default', 'auth', 'sessionname');
		$_SESSION[$sessionname] = $objUser;
		
		return true;
	}
	
}