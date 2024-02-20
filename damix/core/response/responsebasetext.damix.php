<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\response;

class ResponseBaseText
	extends ResponseBase
{

	public string $content = 'HTML5';

	
	public function __construct()
    {
		parent::__construct();
    }
	
	
	public function output() : void
	{
		$this->addHttpHeader('Content-Type','text/plain;charset=' . \damix\engines\settings\Setting::getValue('default', 'general', 'general'),false);

		$this->sendHttpHeaders();
	
		print $this->content;
	}
	
}