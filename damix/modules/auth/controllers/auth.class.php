<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

namespace damix\auth;

class auth
	extends \damix\core\controllers\Controller
{

	public array $pluginParams = array(
		'*' => array( 'auth.required' => false),
	);
	
	public function index()
	{
		$rep = $this->getResponse('htmlauth');
		$error = $this->getParamString( 'error' );
		
			
		$rep->Tpl->assignParameter( 'MAIN', '');
		$rep->Tpl->assignParameter( 'error', $error );
		
		return $rep;
	}

	public function in()
	{
		$rep = $this->getResponse('redirect');
		
		$login = $this->getParamString( 'login' );
		$password = $this->getParamString( 'password' );
		
		
		if( \damix\engines\authentificate\Auth::login( $login, $password ) )
		{
			$action = \damix\engines\settings\Setting::getValue('default', 'general', 'startmodule') . '~' . \damix\engines\settings\Setting::getValue('default', 'general', 'startaction');
			$rep->selector = $action;
		}
		else
		{
			$rep->selector = \damix\engines\settings\Setting::getValue('default', 'auth', 'formauth');
			$rep->params = array( 'error' => true);
		}
		
		
		return $rep;
	}
	
	public function out()
	{
		$rep = $this->getResponse('redirect');
		
		if( \damix\engines\authentificate\Auth::logout() )
		{
			$rep->selector = \damix\engines\settings\Setting::getValue('default', 'auth', 'formauth');
		}
		else
		{
			$rep->selector = \damix\engines\settings\Setting::getValue('default', 'auth', 'auth_error');
		}
		
		
		return $rep;
	}
	
	public function welcome()
	{
		$rep = $this->getResponse('html');
		
		$rep->Tpl->assignParameter( 'MAIN', '');
		
		return $rep;
	}
	

}

