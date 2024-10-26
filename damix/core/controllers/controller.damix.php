<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\controllers;

abstract class Controller
{
	public \damix\core\request\requestgeneric $request;
	public string $responseType;
	public array $pluginParams = array();
	
	protected function getResponse( string $name ) : \damix\core\response\ResponseBase
	{
		$this->responseType = $name;
		
		$c = \damix\engines\settings\Setting::get('default');
	   
		$selResponse = $c->get( 'response', $name );
		
		if( $selResponse === null )
		{
			throw new \damix\core\exception\CoreException('the response ' . $name . ' does not exist');
		}
	   
		$response = \damix\core\response\Response::get( $selResponse );
		
		return $response;
	}
	
	public function getPluginParams( string $function ) : array
	{
		$pluginparams = array();
		if(isset($this->pluginParams['*'])){
			$pluginparams=$this->pluginParams['*'];
		}
		if(isset($this->pluginParams[$function]))
		{
			$pluginparams = $this->pluginParams[$function];
		}
		return $pluginparams;
	}
	
	public function getParams() : array
	{
		return $this->request->getParams();
	}

	public function getParamString( string $name, string $default = null ) : ?string 
	{
		return $this->request->getParamString( $name, $default );
	}

	public function getParamArray( string $name, string $default = null ) : ?array 
	{
		return $this->request->getParamArray( $name, $default );
	}

	public function getParamInt( string $name, int $default = null ) : int | null
	{
		return $this->request->getParamInt( $name, $default );
	}
	
	public function getParamFloat( string $name, float $default = null ) : ?float
	{
		return $this->request->getParamFloat( $name, $default );
	}
	
	public function getParamBool( string $name, bool $default = null ) : ?bool 
	{
		return $this->request->getParamBool( $name, $default );
	}
	
	public function getParamDate( string $name, \damix\engines\tools\xDate $default = null ) : ?\damix\engines\tools\xDate 
	{
		return $this->request->getParamDate( $name, $default );
	}
	
	public function auth() : bool
	{
		$login = $_SERVER['PHP_AUTH_USER'] ?? '';
		$password = $_SERVER['PHP_AUTH_PW'] ?? '';
	
		return \damix\engines\authentificate\Auth::login( $login, $password );
	}
}