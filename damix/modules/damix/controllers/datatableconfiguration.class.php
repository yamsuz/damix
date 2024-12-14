<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

namespace damix\damix;

class datatableconfiguration
	extends \damix\core\controllers\Controller
{    

	public function index()
	{
        $rep = $this->getResponse( 'html' );

        $selector = $this->getParamString( 's' );
        
		if( $rep->Tpl === null )
		{
			throw new \damix\core\exception\CoreException('damix~lclerrors.core.response.template.not.exist');

		}
		
        $rep->Tpl->assignZone( 'MAIN', 'damix~zondatatableconfiguration', array( 'selector' => $selector ) );
        return $rep;
    }
	
	public function load()
    {
        $rep = $this->getResponse( 'json' );
        
        $selector = $this->getParamString( 's' );
        $screen = $this->getParamString( 'n' );
        
        $obj = \damix\engines\datatables\Datatable::get( $selector );
        $list = array( 'visible' => array(), 'hidden' => array());
        $ormselector = $obj->getOrmSelector();

        $result = array( 'ormselector' => $ormselector);
        
        $ormmethod = \damix\engines\orm\method\OrmMethod::get( $ormselector );
        $properties = $ormmethod->getProperties();
        
        $table = $obj->getDatatableStructure( $screen );
        $orders = $obj->getOrders( $screen );
		
        foreach( $properties as $name => $prop )
        {
            if( isset( $table[ $name ] ) )
            {
                $table[ $name ][ 'locale' ] = $prop[ 'locale' ];
                $table[ $name ][ 'datatype' ] = $prop[ 'datatype' ];
				if( \damix\engines\locales\Locale::isLocale( $prop[ 'locale' ] ) ) 
				{
					$table[ $name ][ 'header' ] = \damix\engines\locales\Locale::get( $prop[ 'locale' ] );
				}
				
				if( $table[ $name ]['visible' ] )
				{
					$list[ 'visible' ][$name] = $table[ $name ];
				}
				else
				{
					$list[ 'hidden' ][$name] = $table[ $name ];
				}
            }
            else
            {
                $list[ 'hidden' ][$name] = array( 
                    'ref' => $prop[ 'ref' ],
                    'name' => $name,
                    'locale' => $prop[ 'locale' ] ?? '',
                    'header' => ($prop[ 'locale' ] ? \damix\engines\locales\Locale::get( $prop[ 'locale' ] ) : ''),
                    'datatype' => ( $prop[ 'format' ] != '' ? $prop[ 'format' ] : $prop[ 'datatype' ] ),
                    'from' => 'orm',
                    'sort' => '',
                );
            }
        }
       	
		foreach( $table as $prop )
		{
			if( $prop['from'] != 'orm' )
			{
				$type = tobool( $prop['visible'] ) ? 'visible' : 'hidden';
				
				$list[ $type ][$prop['name']] = array( 
                    'name' => $prop['name'],
                    'ref' => $prop['ref'],
                    'locale' => $prop[ 'locale' ] ?? '',
                    'header' => ($prop[ 'locale' ] ? \damix\engines\locales\Locale::get( $prop[ 'locale' ] ) : ''),
                    'datatype' => $prop[ 'datatype' ],
                    'from' => $prop['from'],
                    'order' => $prop['order'],
                    'functions' => $prop['functions'] ?? array(),
                    'sort' => '',
                );
			}
		}
		
		

		foreach( $orders as $order )
		{
			$name = $order['name'];
			if( isset( $list[ 'visible' ][$name] ) )
			{
				$list[ 'visible' ][$name]['sort'] = $order['way'];
			}
			elseif( isset( $list[ 'hidden' ][$name] ) )
			{
				$list[ 'hidden' ][$name]['sort'] = $order['way'];
			}
		}
		if( isset( $list[ 'visible' ] ) )
		{
			if( count( $list[ 'visible' ] ) > 1 )
			{
				usort( $list[ 'visible' ], function($a, $b){
					return $a['order'] - $b['order'];
					
				});
			}
			else
			{
				$list[ 'visible' ] = array_values( $list[ 'visible' ] );
			}
		}
		$list[ 'hidden' ] = array_values($list[ 'hidden' ]);
        $result[ 'list' ] = $list;
		
        $indexes = $ormmethod->getIndexes();
		$filter = $obj->getDataFilter( $screen );
		// \damix\engines\logs\log::dump( $list );
        
        foreach( $indexes as $name => $index )
        {
            $prop = $ormmethod->getProperty( $name );
			
            if( isset( $filter[ $name ] ) )
            {
                $filter[ $name ][ 'visible' ] = true;
                $filter[ $name ][ 'ref' ] = $prop[ 'ref' ];
                $result[ 'filter' ][ 'visible' ][] = $filter[ $name ];
            }
            else
            {
				$result[ 'filter' ][ 'hidden' ][] = array(
                    'name' => $name, 
                    'ref' => $prop[ 'ref' ],
                    'header' => \damix\engines\locales\Locale::get( $prop[ 'locale' ] ),
                    'locale' => $prop[ 'locale' ],
                    'datatype' => ($prop[ 'combo' ] == '' ? $prop[ 'datatype' ] : 'select' ),
                    'null' => $prop[ 'null' ],
                    'table' => $prop[ 'table' ],
                    'field' => $prop[ 'name' ],
                    'selector' => $prop[ 'combo' ],
                    'group' => '',
                    'from' => 'orm',
                    'operator' => '',
                    'defaultvalue1' => '',
                    'defaultvalue2' => '',
                    'row' => '',
                    'column' => '',
                    'visible' => false,
                );
            }
        }
	    
        $rep->data = $result;
        return $rep;
    }
    
    public function save()
    {
        $rep = $this->getResponse( 'json' );
        
        $result = array();
		
        $params = $this->getParams();
       
		if( \damix\engines\acls\Acl::check( 'damix.datatable.configuration.enregistrer' ) )
		{
			// if( tobool(\jApp::config()->production ) )
			// {
				$obj = \damix\engines\datatables\Datatable::saveUser( $params );
			// }
			// else
			// {
				// $obj = \damix\engines\datatables\Datatable::saveDefault( $params );
			// }
		}
		
        $rep->data = $result;
        return $rep;
    }
	
    public function savedefault()
    {
        $rep = $this->getResponse( 'json' );
        
        $result = array();
		
        $params = $this->params();
       
		if( \damix\engines\acls\Acl::check( 'cmr.core.datatable.configuration.defaut' ) )
		{
			$obj = \damix\engines\datatables\Datatable::saveDefault( $params );
		}
		
        $rep->data = $result;
        return $rep;
    }
}