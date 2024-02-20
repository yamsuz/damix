<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\coord;

class CoordAcl
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
		$acl = true;
		
		if(isset( $params['auth.acl'] ))
		{
			$acl = \damix\engines\acls\Acl::check($params['auth.acl']);
		}
		
		if( !$acl )
		{
			
			$coord->action = $this->setting->get( 'auth', 'error_action');
			$coord->getController();
			return;
		
		}
	}
}