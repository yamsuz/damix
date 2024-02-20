<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\response;

abstract class ResponseBase
{
	protected int $_httpStatusCode;
	protected string $_httpStatusMsg;
	protected array $_httpHeaders;
	protected string $httpVersion;
	
	public function __construct()
    {
		$this->clearHttpHeaders();
    }
	
	public abstract function output() : void;
		
	public function clearHttpHeaders() : void
	{
		$this->httpVersion = '1.1';
		$this->_httpStatusCode = 200;
		$this->_httpStatusMsg = '';
		$this->_httpHeaders = array();
	}
	
	public function setHttpStatus( int $code, string $msg ) : void
	{
		$this->_httpStatusCode = $code;
		$this->_httpStatusMsg = $msg;
	}

	public function addHttpHeader(string $name, string|array $value, bool $overwrite = true) : void
	{
		if( isset( $this->_httpHeaders[$name] ) )
		{
			if( ! $overwrite )
			{
				if(! is_array( $value ) )
				{
					$this->_httpHeaders[ $name ] = array( $this->_httpHeaders[ $name ], $value );
				}
				else
				{
					$this->_httpHeaders[ $name ][] = $value;
				}
				return;
			}
		}
		$this->_httpHeaders[ $name ] = $value;
	}
	
	public function sendHttpHeaders() : void
	{
		header( 'HTTP/' . $this->httpVersion . ' ' . $this->_httpStatusCode . ' ' . $this->_httpStatusMsg);
		foreach($this->_httpHeaders as $ht => $hc){
			if(is_array($hc)){
				foreach($hc as $val){
					header($ht.': '.$val);
				}
			}
			else
				header($ht.': '.$hc);
		}
	}
	
	
	
	public function outputErrors() : void
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . 'error.en_US.php';
		header("HTTP/" . $this->httpVersion ." 500 Internal damix error");
		header('Content-Type: text/html;charset=UTF-8');
		include($file);
	}
}