<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\coord;

class CoordAuth
	extends \damix\core\plugins\PluginBaseCoord
{
	protected \damix\engines\settings\SettingBase $setting;
	
	public function __construct()
	{
		$this->setting = \damix\engines\settings\Setting::get('default');
	}
	
	public function beforeAction(\damix\core\Coordinator $coord) : void
	{
		$params = $coord->Controller->getPluginParams( $coord->selectorController->getPart('function') );
		
		$needAuth = (isset( $params['auth.required'] ) ? $params['auth.required'] : tobool($this->setting->get( 'auth', 'auth_required')));
		
		
		if( $needAuth && !\damix\engines\authentificate\Auth::isConnected())
		{
			$sessionname = \damix\engines\settings\Setting::getValue('default', 'auth', 'sessionname');
		
			if( ! isset( $_SESSION[$sessionname]->login ) || $_SESSION[$sessionname]->login != '' )
			{		
				$coord->action = $this->setting->get( 'auth', 'formauth');
				$coord->getController();
				return;
			}
		}
		
		
	}
}