<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\template\drivers;

class TemplatesFunctionDatatable
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

		$out[] = '<div class="damix-dt__content-filtre-bouton"><div class="kt-portlet"><div class="damix-dt__body"><div id="'. $obj->getId() .'" style="width:100%"><table class="display nowrap datatable">';
		$out[] = '</table></div></div></div></div>';
		$out[] = '<script type="text/javascript">';
		$out[] = $obj->getJavascript();
		$out[] = '</script>';
		
		return implode("\n", $out);
	}
}