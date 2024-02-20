<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\template\drivers;

class TemplatesFunctionDatafiltre
    extends \damix\engines\template\drivers\template\TemplateDriverFunction
    
{
    public function Execute( array|string $args ) : string
    {
		if( is_array( $args ) )
		{
			$sel = $args['selecteur'];
		}
		else
		{
			$sel = $args;
		}
		
		$out = array();
		$obj = \damix\engines\datatables\Datatable::get( $sel );
		
		$out[] = '<div id="filter_'. $obj->getId() .'" style="width:100%">';
		$out[] = $obj->getFilters();
		$out[] = '</div>';
		
		return implode("\n", $out);
	}
}