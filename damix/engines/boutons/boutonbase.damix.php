<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\boutons;


class BoutonBase
{
    protected $_boutons = array();
    
    public function __construct()
    {
    
    }
    
    public function getHtml() : string
    {
		$zone = \damix\engines\settings\Setting::getValue('default', 'formcontrols', 'zoneboutons');
		
		if( empty( $zone ))
		{
			$zone = 'damix~zonboutons';
		}
		$html = \damix\engines\zones\Zone::get( $zone, array( 'boutons' => $this->_boutons ));
       
        return $html;
    }
}