<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\settings;


class SettingBase
{
	protected array $_config = array();
	
	public function __construct()
    {
		$this->propertyinit();
    }
	
	public function get( string $section, string $name ) : ?string
	{		
		return $this->_config[$section][$name] ?? null;
	}
	
	public function set( string $section, string $name, string $value ) : void
	{
		$this->_config[$section][$name] = $value;
	}
	
	public function getAllSection( string $section ) : array
	{
		return $this->_config[$section] ?? array();
	}
	
	protected function propertyinit(): void
	{
	}
}