<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\response;

class ResponseBaseJson
	extends ResponseBase
{
	public array | object $data;
	
	
	public function __construct()
    {
		parent::__construct();
    }
	
	public function output() : void
	{
		$this->sendHttpHeaders();
		
		$out = json_encode( $this->data );

		print $out;
	}
}