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
        $start = $params[ 'start' ] ?? 0;
        $length = $params[ 'length' ] ?? 100;
		
		
		$list[ 'draw' ] = $draw;
		$list[ 'page' ] = array( 'start' => $start, 'length' => $length );
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
	
	
	public function export() 
    {
        $rep = $this->getResponse( 'binary' );
		
		
        $out = array();
        $params = $this->getParams();
        $list = json_decode($params[ '@list' ], true);
        
		if( \damix\engines\acls\Acl::check('damix.datatable.menu.export') )
		{
			$orders = $params[ 'order' ] ?? array();
			$columns = $params[ 'columns' ] ?? array();
			$selector = $list['selector'] ?? null;
			
			if( $list['selector'] )
			{
				$obj = \damix\engines\datatables\Datatable::get( $list['selector'] );
				if( $obj )
				{
					$fileName = $obj->exportData( $list );
				}
			}

			$outputFileName = 'export_' . date( 'd' ) . '_' . date( 'm' ) . '_' . date( 'Y' ) . '.csv';
			$doDownload = true;
			
			$rep->mimeType = 'text/csv';
			$rep->fileName = $fileName;
			$rep->outputFileName = $outputFileName;
			$rep->doDownload = $doDownload;
			$rep->content = file_get_contents( $fileName );
			
			\damix\engines\tools\xFile::remove( $fileName );
		}
		
		
        return $rep;
    }
	
		
}