<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\response;

class ResponseBaseUrl
	extends ResponseBase
{
	public string $url;
	public array $params;
	public string $method = 'GET';
	
	public function __construct()
    {
		parent::__construct();
    }
	
	public function output() : void
	{
		switch( $this->method )
		{
			case 'POST':
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
				curl_setopt($ch, CURLOPT_URL, $this->url);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
				print curl_exec($ch);
				
				curl_close($ch);
				
				break;
			default:
				$this->sendHttpHeaders();
				
				header('Location: ' . $this->url . $this->encode_array());
				break;
		}
		
	}
	
	
	private function encode_array(): string
	{
		if(empty($this->params))
			return '';
			
		$c = 0;
		$out = '?';
		foreach($this->params as $name => $value)
		{
			if($c++ != 0) $out .= '&';
			$out .= urlencode($name) . '=';
			if(is_array($value))
			{
				$out .= urlencode(serialize($value));
			}
			else
			{
				$out .= urlencode($value);
			}
		}
		return $out;
	}
}