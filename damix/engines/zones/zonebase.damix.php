<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\zones;

abstract class ZoneBase
{
	protected string $tplSelector = '';
	protected \damix\engines\template\Template $Tpl;
	public array $params = array();
	
	public function __construct()
	{
	}
	
	public function getContent() : string
	{
		if( ! empty( $this->tplSelector ) )
		{
			$this->Tpl = \damix\engines\template\Template::get( $this->tplSelector );
		}
		
		$this->Tpl->assignParameters( $this->params );
		$this->prepareTpl();
		
		$content = '';
		if( ! empty( $this->tplSelector ) )
		{
			$content .= $this->Tpl->fetch();
		}
		
		return $content;
	}
	
	public function getParam( string $name ) : mixed
	{
		return $this->params[ $name ] ?? null;
	}
	
	public function getParams() : array
	{
		return $this->params;
	}
	
	protected function prepareTpl() : void {}
}