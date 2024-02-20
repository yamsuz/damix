<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
namespace damix\apps\response;

class ClassicMyjhjcresponse
	extends \damix\core\response\ResponseBaseJson
{
	
	public function output() : void
	{
		$js = \damix\engines\scripts\Javascript::getUrl();
        $css = array();
        $this->data[ 'jhjc' ] = array( 'js' => $js, 'css' => $css );
		
		parent::output();
	}
}