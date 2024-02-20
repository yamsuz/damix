<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

declare(strict_types=1);
namespace damix\core\request;

class ClassicRequest 
	extends \damix\core\request\RequestGeneric
{
	
	protected function initParams()
	{
		
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->params['GET'] = $_GET;
		$this->params['POST'] = $_POST;
		$this->params['REQUEST'] = $_REQUEST;
		
		$url = \damix\core\urls\Url::get( $this->urlPathInfo );
		$this->url = $url;
		$this->url->parse( $this );

	}
}