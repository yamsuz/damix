<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\template\drivers;

class TemplatesFunctionDatatableid
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
		
		
		$obj = \damix\engines\datatables\Datatable::get( $sel );

		return $obj->getId();
	}
}