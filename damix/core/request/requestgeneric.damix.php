<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

declare(strict_types=1);
namespace damix\core\request;

abstract class RequestGeneric
{
	protected array $params;
	protected string $method;
	protected string $urlPathInfo;
	protected string $pathInfo;
	
	protected \damix\core\urls\UrlBase $url;
	
	abstract protected function initParams();

	public function __construct(){}
	
	public function init()
	{
		$this->method = $_SERVER['REQUEST_METHOD'];
		
		
		if(isset($_SERVER['PATH_INFO'])){
			$pathinfo=$_SERVER['PATH_INFO'];
		}else if(isset($_SERVER['ORIG_PATH_INFO'])){
			$pathinfo=$_SERVER['ORIG_PATH_INFO'];
		}else
			$pathinfo='';
		
		$this->urlPathInfo = $pathinfo;
		$this->pathInfo = $pathinfo;
		$this->initParams();
	}
	
	public function getModule():string
	{
		return $this->url->module;
	}
	
	public function getAction():string
	{
		return $this->url->action;
	}
	
	public function getParams() : array
	{
		return $this->params['REQUEST'];
	}
	
	public function getParamString( string $name, string $default = null ) : ?string 
	{
		return $this->params['REQUEST'][ $name ] ?? $default;
	}
	
	public function getParamArray( string $name, string $default = null ) : ?array 
	{
		return $this->params['REQUEST'][ $name ] ?? $default;
	}
	
	public function getParamInt( string $name, int $default = null ) : int | null
	{
		if( isset( $this->params['REQUEST'][ $name ] ) )
		{
			return intval($this->params['REQUEST'][ $name ]);
		}
		else
		{
			return $default;
		}
	}
	
	public function getParamFloat( string $name, float $default = null ) : ?float
	{
		if( isset( $this->params['REQUEST'][ $name ] ) )
		{
			return floatval($this->params['REQUEST'][ $name ]);
		}
		else
		{
			return $default;
		}
	}
	
	public function getParamBool( string $name, bool $default = null ) : ?bool 
	{
		if( isset( $this->params['REQUEST'][ $name ] ) )
		{
			return boolval($this->params['REQUEST'][ $name ]);
		}
		else
		{
			return $default;
		}
	}
	
	public function getParamDate( string $name, \damix\engines\tools\xDate $default = null ) : ?\damix\engines\tools\xDate 
	{
		if( isset( $this->params['REQUEST'][ $name ] ) )
		{
			return \damix\engines\tools\xDate::load( $this->params['REQUEST'][ $name ] );
		}
		else
		{
			return $default;
		}
	}

	public function getErrorResponse() : \damix\core\response\ResponseBase
	{
		$response = new \damix\core\response\ResponseBaseHtml();
		return $response;
	}
}