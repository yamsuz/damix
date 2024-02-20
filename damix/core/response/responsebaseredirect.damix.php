<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\response;

class ResponseBaseRedirect
	extends ResponseBase
{
	public bool $temporary = true;
	public string $selector;
	public string $anchor = '';
	public array $params = array();
	
	public function __construct()
    {
		parent::__construct();
    }
	
	public function output() : void
	{
		if($this->temporary)
		{
			$this->setHttpStatus(303,'See Other');
		}
		else
		{
			$this->setHttpStatus(301,'Moved Permanently');
		}
		$this->sendHttpHeaders();
		header('Location: '.\damix\core\urls\Url::getPath($this->selector, $this->params) . (! empty($this->anchor) ? '#'.$this->anchor:''));
	}
	
}