<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

namespace damix\damix;

class Datatable
	extends \damix\core\controllers\Controller
{

	public array $pluginParams = array(
		'*' => array( 'auth.required' => false),
	);
		
	public function data()
	{
		$rep = $this->getResponse( 'json' );
        
        $out = array();
        $params = $this->getParams();
        $draw = $params[ 'draw' ] ?? 0;
        $list = $params[ 'list' ] ?? array();
		
		
		$list[ 'draw' ] = $draw;
        $orders = $params[ 'order' ] ?? array();
        $columns = $params[ 'columns' ] ?? array();
        $selector = $list['selector'] ?? false;
		
		foreach( $orders as $order )
		{
			$pos = $order['column'];
			$way = $order['dir'];
			
			$list['orders' ][] = array(  'name' => $columns[ $pos ]['data'], 'way' => $way);
		}
        if( $selector )
        {
            $obj = \damix\engines\datatables\Datatable::get( $selector );
            if( $obj )
            {
                $out = $obj->getData( $list );
				
				$out->draw = $draw + 1;
            }
        }

		
        $rep->data = $out;
        return $rep;
	}
	
	
		
}