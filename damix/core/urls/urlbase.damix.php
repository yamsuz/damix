<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\urls;


abstract class UrlBase
{
	public UrlSelector $selector;
	public string $module;
	public string $action;
	
	public abstract function parse(\damix\core\request\RequestGeneric $request);
	public function toString(array $params = array(), bool $forxml = false) : string
	{
		return $this->getPath() . $this->getQuery($params, $forxml);
	}
	
	public abstract function getPath() : string;
	
	public function getQuery(array $params = array(), bool $forxml = false) : string
	{
		if(count($params)>0){
			$q=http_build_query($params,'',($forxml?'&amp;':'&'));
			if(!$q)
				return '';
			if(strpos($q,'%3A')!==false)
				$q=str_replace('%3A',':',$q);
			return '?'.$q;
		}
		return '';
	}
	
	public function getBasePath() : string
	{
		$c = \damix\engines\settings\Setting::get('default');
		$scriptname = $c->get( 'url', 'scriptname' );
		
		$url = Url::getBasePath() . $scriptname;
		return $url;
	}
	
	
}