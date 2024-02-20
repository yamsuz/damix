<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\acls;


abstract class AclBase
{
	protected static ?array $_acl = null;
	protected static ?array $_groups = null;
	
	abstract protected function loadgroup() : void;
	abstract protected function loadright() : void;
	abstract protected function setup() : void;
	abstract public function addsubject(string $subject, string $label = '') : void;
	abstract public function addright(string $subject, string $group, bool $cancel = false) : void;
	abstract public function addusergroup(string $user, string $group) : void;
	abstract public function addgroup(string $ode, string $label) : void;
	
	public function __construct()
	{
		
	}
	
	public function check( array $liste ) : bool
	{
		if( self::$_acl === null)
		{
			self::$_acl = array();
			$this->loadright();
		}
				
		foreach( $liste as $info )
		{
			if( ! isset( self::$_acl[$info] ) )
			{
				self::$_acl[$info] = false;
			}
			if( self::$_acl[$info] ) 
			{
				return true;
			}
		}
		
		return false;
	}

}