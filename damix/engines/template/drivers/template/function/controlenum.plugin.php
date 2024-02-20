<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\template\drivers;

class TemplatesFunctionControlenum
    extends \damix\engines\template\drivers\template\TemplateDriverFunction
    
{
	public function Execute( array|string $args ) : string
    {
		$zone = \damix\engines\settings\Setting::getValue('default', 'formcontrols', 'zonecontrolenum');
		return \damix\engines\zones\Zone::get( $zone, $args);
	}
}