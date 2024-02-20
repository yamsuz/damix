<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\tools;

class Ldap
{
	protected $cnx = null;
	
	public function connect() : bool
	{
		$default = \damix\engines\settings\Setting::get('default');
		$ldapserver = $default->get('auth', 'ldapserver');
		$ldapport = $default->get('auth', 'ldapport');
		
		$this->cnx = ldap_connect($ldapserver, intval($ldapport));
		ldap_set_option($this->cnx, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->cnx, LDAP_OPT_REFERRALS, 0);
		
		if( $this->cnx )
		{
			return true;
		}
		return false;
	}
	
	public function __destruct(){
		$this->close();
	}
	
	public function bind($login, $password) : bool
	{
		if( $this->cnx ){
			return ldap_bind($this->cnx, $login, $password);
		}
		return false;
	}
	
	public function search(string $base, string $filter, array $attributes) : array
	{
		$sr = ldap_search($this->cnx, $base, $filter, $attributes);
		if( $sr )
		{
			return ldap_get_entries($this->cnx, $sr);
		}
		return array();
	}
	
	public function close() : bool
	{
		if( $this->cnx )
		{
			return ldap_close( $this->cnx );
		}
		return true;
	}
}