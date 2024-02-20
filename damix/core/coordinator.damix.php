<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

declare(strict_types=1);
namespace damix\core;

class Coordinator
{
	public readonly \damix\core\request\requestgeneric $request;
	public \damix\core\controllers\ControllerSelector $selectorController;
	public \damix\core\controllers\Controller $Controller;
	public string $action;
	public array $plugins = array();
	protected \damix\engines\settings\SettingBase $setting;
	
	public function __construct()
	{
		$this->setting = \damix\engines\settings\Setting::get('default');
		$this->loadPlugins();
	}
	
	protected function loadPlugins()
	{
		$plugins = $this->setting->getAllSection( 'plugins' );
		$out = array();
		foreach( $plugins as $plugin)
		{
			$selector = 'coord~' . $plugin;
			$pl = \damix\core\plugins\Plugin::get( $selector );
			
			if( $pl ) 
			{
				$out[] = $pl;
			}
		}
		
		$this->plugins = $out;
	}
	
    public function setRequest(\damix\core\request\requestgeneric $request) 
    {
        $this->request = $request;
		
		\damix\core\exception\BasicErrorHandler::register($request);
		
		$this->request->init();
		
		$module = $this->request->getModule();
		if( empty( $module ) )
		{
			$module = $this->setting->get( 'general', 'startmodule' );
		}
		
		$action = $this->request->getAction();
		if( empty( $action ) )
		{
			$action = $this->setting->get( 'general', 'startaction' );
		}

		$this->action = $module . '~' . $action;
    }
	
	public function getController() 
	{
		$this->selectorController = new \damix\core\controllers\ControllerSelector($this->action);
		
		require_once( $this->selectorController->getPath() );
		
		$classname = $this->selectorController->getFullNamespace();
		
		$this->Controller = new $classname();
		$this->Controller->request = $this->request;
	}
	
	public function process()
	{
		\damix\engines\sessions\Session::start();
		
		foreach( $this->plugins as $plugin)
		{
			$plugin->beforeProcess($this);
		}
		
		$this->getController();
		
		foreach( $this->plugins as $plugin)
		{
			$plugin->beforeAction($this);
		}
		
		try
		{
			$response = $this->Controller->{$this->selectorController->getPart('function')}();
		}
		catch( \damix\core\exception\HttpException $e)
		{
			$response = new \damix\core\response\ResponseBaseHtml();
			$response->setHttpStatus($e->getCode(), $e->getMessage() );
		}
		
		foreach( $this->plugins as $plugin)
		{
			$plugin->beforeOutput($this);
		}
		
		$response->output();
		
		foreach( $this->plugins as $plugin)
		{
			$plugin->afterProcess($this);
		}
		
		\damix\engines\sessions\Session::end();
	}
}