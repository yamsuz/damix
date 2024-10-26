<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\datatables;

class DatatableBase
{
	protected string $title;
    protected string $key;
    protected string $module;
    protected string $resource;
    protected string $function;
    protected string $selection;
    protected string $type;
    protected string $classname;
    protected string $default;
    protected array $ormselector;
    protected array $condition  = array();
    protected array $filters = array();
    protected array $datatable = array();
    protected array $datareport = array();
    protected array $orders = array();
    protected array $kanban = array();
    protected bool $autoload = true;
    protected bool $paging = false;
    protected array $parameters;
    protected ?\Iterator $datas = null;
    protected array $table = array();
    protected array $data = array();
    
    protected \damix\engines\orm\OrmBaseFactory $orm;
    
    public function __construct()
    {    
    }
    
	protected function propertyinit() : void{}
	
    public function getTitle() : string
    {
        return $this->title;
    }
	
	public function getId() : string
    {
        return $this->module . '_' . $this->resource . '_' . $this->function;
    }
	
	public function getOrmSelector() : string
    {
        return $this->ormselector['module'] . '~' . $this->ormselector['resource'] . ':' . $this->ormselector['function'];
    }
    
	public function getSelector() : string
    {
        return $this->module . '~' . $this->resource . ':' . $this->function;
    }
	
	public function getJavascript(?string $name = null) : string
    {
        $out = array();
        
        $out[] = '$xlist = xscreen.list_add( \''. $this->getId() .'\' );';
        $out[] = '$xlist.selector = \'' . $this->module . '~' . $this->resource . ':' . $this->function . '\';';
        $out[] = '$xlist.type = \''. $this->type .'\';';
        $out[] = '$xlist.id = \'#' . $this->getId() . '\';';
        $out[] = '$xlist.selection = \'' . $this->selection . '\';';
        $out[] = '$xlist.paging = ' . ($this->paging ? 'true' : 'false') . ';';
        $out[] = '$xlist.autoload = ' . ($this->autoload ? 'true' : 'false') . ';';

        foreach( $this->condition as $cond )
        {
            if( $cond['type'] == 'js' )
            {
				$out[] = '$xlist.addparam(\'' . $cond['name'] . '\', ' . $cond['value1'] . ');';
            }
        }
        
        $columns = $this->datatable[ $name ?? $this->default ] ?? array();
        
		$out[] = '$xlist.columns.add( \'\', \'' . preg_replace( '/[\']/i', '\\\'', $this->key ) .'\' );';
        foreach( $columns as $col )
        {
			// Prise en charge de la locale si elle est renseignée
			if( $col[ 'header' ] != '' )
			{
				if( tobool( $col['visible'] ) )
				{
					$out[] = '$xlist.columns.add( \'' . preg_replace( '/[\']/i', '\\\'', $col[ 'header' ] ) .'\', \'' . preg_replace( '/[\']/i', '\\\'', $col[ 'name' ] ) .'\' );';
				}
			}
        }

        if( $this->autoload && $this->type == 'list')
        {
            $out[] = '$xlist.load();';
        }
        
        return implode( "\n", $out );
    }
	
	public function getFilters( $name = null )
    {
        $filters = $this->filters[ $name ?? $this->default ] ?? array();

		$zone = \damix\engines\settings\Setting::getValue('default', 'formcontrols', 'zonefiltres');
		
		if( empty( $zone ))
		{
			$zone = 'damix~zondatatablefiltres';
		}
		$html = \damix\engines\zones\Zone::get( $zone, array( 'filtres' => $filters, 'selector' => $this->getSelector() ));
       
        return $html;
    }
	
	public function getData( array $params ) : \StdClass
	{
		$this->parameters = $params;
		
		$this->loadColumns();
		$this->loadValues();
		if( $this->autoload || $this->parameters['draw'] > 1 )
		{
			$this->getDataValues();
		}
		
		return $this->getDatatable();
	}
	
	public function exportData( array $params ) : string
    {
		$this->paging = false;
		$this->parameters = $params;
		
		$this->loadColumns();
		$this->loadValues();
		if( $this->autoload || $this->parameters['draw'] > 1 )
		{
			$this->getDataValues();
		}
        
        $data = array_column( $this->table['columns'], 'data');
		$filename = \damix\application::getPathTemp() . \damix\engines\tools\xTools::login() . '.csv';
		$fp = fopen($filename, 'w');
		fwrite($fp, "\xEF\xBB\xBF");
		$this->fputcsv($fp, $data);
		
		$columns = $this->table['columns'];
		$rows = $this->table['rows'] ?? array();
		
		$enclosure = '"';
		$escape_char = "\\";
		$delimiter = ";";
		$record_seperator = PHP_EOL;

		foreach( $rows as $row )
		{

			$data = array();
			foreach( $columns as $i => $col )
			{
				$data[] = $row[$i]['value'];
			}	
			$this->fputcsv($fp, $data);
		}
	
		fclose($fp);
		
        return $filename;
    }
    
	private function fputcsv( $handle, array $fields, string $delimiter = ";", string $enclosure = '"', string $escape_char = "\\", string $record_seperator = PHP_EOL) :  bool
	{
		$result = [];
		foreach ($fields as $field) {
			$result[] = $enclosure . str_replace($enclosure, $escape_char . $enclosure, $field ?? '') . $enclosure;
		}
		return fwrite($handle, implode($delimiter, $result) . $record_seperator) > 0;
	}
	
	public function getDatatable() : \StdClass
	{
		$columns = $this->table['columns'];
		$rows = $this->table['rows'] ?? array();
		$nb = count($rows);
		$obj = new \StdClass();
		if( $nb > 0 ) 
		{
			$obj->recordsTotal = $this->datas->rowCount();
			$obj->recordsFiltered = $this->datas->rowCount();
		}
		else
		{
			$obj->recordsTotal = 0;
			$obj->recordsFiltered = 0;
		}
		$obj->data = array();
		
		foreach( $rows as $row )
		{
			$ligne = array();
			
			foreach( $columns as $i => $col )
			{
				$ligne[ $col['name'] ] = array( 'value' => 
					$row[$i]['value'], 
					'type' => $row[$i]['type'], 
					'couleur' => $row[$i]['couleur'] );
			}	
			$obj->data[] = $ligne;
		}
		
		return $obj;
	}
	
	public function getDatatableStructure(?string $name = null) : array
	{
		$columns = $this->datatable[ $name ?? $this->default ] ?? array();
		$data = array();
		foreach( $columns as $i => $col )
		{
			$data[ $col['name'] ] = $col;
		}	
	
		return $data;
	}
	
	protected function loadValues() : void
	{
		$this->orm = \damix\engines\orm\Orm::get( $this->ormselector['module'] . '~' . $this->ormselector['resource'] );
		
        $this->getDataValuesConditions();
        $this->getDataValuesOrders();
				
		$this->datas = $this->orm->{ $this->ormselector['function'] }();
		
		// \damix\engines\logs\log::dump( $this->datas->fetchAll() );
	}
	
	protected function getDataValues()
    {
        $data = array();
        \damix\engines\datatables\drivers\DatatableDriver::$parameters = $this->datas;
		
		if( $this->paging )
		{
			$start = intval($this->parameters['page']['start']);
			$RowCount = $start + intval($this->parameters['page']['length']);
		}
		else
		{
			$start = 0;
			$RowCount = $this->datas->rowCount();
		}

        foreach( $this->datas as $i => $record )
        {
			if( $i >= $start && $i < $RowCount )
			{
				$this->getDataValuesRecord( $record );
			}
        }
    }
	
	protected function getDataValuesRecord( object $record ) : void
    {
        $columns = $this->datatable[ $this->parameters['name'] ?? $this->default ] ?? array();
        
		$row = array();
		if( $this->key != null )
		{
			$row[] = array( 
				'value' => $record->{$this->key},
				'couleur' => null,
				'type' => \damix\engines\orm\request\structure\OrmDataType::ORM_INT,
				'pk' => true,
			);
			
		}
		
        foreach( $columns as $col )
        {
			if( tobool( $col['visible'] ) )
			{
				$couleur = $record->{$col['couleur']} ?? null;
				// $badge = $record->{$col['badge']} ?? null;
				$value = '';
				switch( $col['from'] ) 
				{
					case 'orm' :
						$value = $record->{$col['name']};
						break;
					case 'script' :
						if( isset( $col['functions']['content'] ) )
						{
							// $value = $this->ExecuteFonction( $col['functions']['content'], $record );
						}
						break;
				}
				
				$value = $this->getFormatData( $col, $value );
				
				
				$row[] = array( 
					'value' => $value,
					'couleur' => $couleur ?? '',
					'type' => $col[ 'datatype' ] ?? \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR,
					'pk' => false,
				);
			}
        }
		
		$this->table['rows'][] = $row;
    }
	
	private function getFormatData( array $col, ?string $value ) : mixed
	{
		$datatype = $col['datatype'];

		switch( $datatype )
		{
			case \damix\engines\orm\request\structure\OrmDataType::ORM_INT:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BIGINT:
				$value = \damix\engines\tools\xTools::numberFormat( floatval($value), 0 );
				break;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DATE:
				$xdate = \damix\engines\tools\xDate::load( $value );
				if( $xdate )
				{
					$value = $xdate->format( \damix\engines\tools\xDate::LANG_DFORMAT );
				}
				break;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DATETIME:
				$xdate = \damix\engines\tools\xDate::load( $value );
				if( $xdate )
				{
					$value = $xdate->format( \damix\engines\tools\xDate::LANG_DTFORMAT );
				}
				break;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_TIME:
				$xdate = \damix\engines\tools\xDate::load( $value );
				if( $xdate )
				{
					$value = $xdate->format( \damix\engines\tools\xDate::LANG_TFORMAT );
				}
				break;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_BOOL:
				$value = tobool( $value );
				break;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_DECIMAL:
			case \damix\engines\orm\request\structure\OrmDataType::ORM_FLOAT:
				$value = \damix\engines\tools\xTools::numberFormat( floatval($value) );
				break;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_PHONE:
				$value = \damix\engines\tools\xTools::phoneFormat( $value );
				break;
			case \damix\engines\orm\request\structure\OrmDataType::ORM_ENUM:
				if( $value )
				{
					$val = \damix\engines\locales\Locale::getEnum( $col['locale'], $value );
					if( $val !== null )
					{
						$value = $val;
					}
				}
				
				break;
		}
		
		return $value;
	}
	
	protected function loadColumns(?string $name = null) : void
	{
		$columns = $this->datatable[ $name ?? $this->default ] ?? array();
		
		$cols = array();
		
		$cols[] = array( 'name' => $this->key, 'data' => '', 'orderable' => false, 'width' => '3%');
		
		
		foreach( $columns as $col )
        {
			if( tobool( $col['visible'] ) )
			{
				$cols[] = array( 'name' => $col['name'], 'data' => $col['header'], 'orderable' => true, 'width' => $col['width']);
			}
        }
		
		$this->table['columns'] = $cols;
	}
	
	protected function getDataValuesConditions()
    {
        $function = $this->ormselector['function'];
        
        $c = $this->orm->getConditionsClear( $function );
		
		if( isset( $this->condition ) )
        {
            foreach( $this->condition as $condition )
            {
                switch( $condition['type'] )
				{
					case 'groupbegin':
						$c->addGroupBegin( $condition['group'] ?? '' );
						break;
					case 'groupend':
						$c->addGroupEnd( $condition['group'] ?? '' );
						break;
					case 'logic':
						$c->addLogic( \damix\engines\orm\conditions\OrmOperator::cast( $condition['value']) );
						break;
					case 'string':
					
						$name = array('table' => $condition['table'], 'field' => $condition['property']);
						
						$c->addString( $name, \damix\engines\orm\conditions\OrmOperator::cast( $condition['operator']), $condition['value1'], $condition['group']);
						break;
					case 'field':
						$name = array('table' => $condition['table'], 'field' => $condition['property']);
						
						if( $condition['operator'] != 'isnull' )
						{
							switch( $condition['datatype'] )
							{
								case 'date':
								case 'time':
								case 'datetime':
									$c->addDate( $name, $condition['operator'],  \damix\engines\tools\xDate::load($condition['value1']),  \damix\engines\tools\xDate::load($condition['value2']), $condition['group'] );
									break;
								case 'bool':
									$c->addBool( $name, $condition['operator'], ($condition['value1'] == '1' ? true : false), $condition['group'] );
									break;
								default:
									$c->addString( $name, $condition['operator'], $condition['value1'], $condition['group']);
									break;
							}
						}
						else
						{
							$c->addNull( $name, $condition['group']);
						}
						break;
                    case 'js':
                        $value1 = null;
                        $ref = $condition['name'];
						if( isset( $this->parameters['params'] ) )
						{
							$del = null;
							foreach( $this->parameters['params'] as $i => $data )
							{
								if( $data['ref'] == $ref )
								{
									$value1 = $data['value1'];
									$del = $i;
									break;
								}
							}
							
							if( $del !== null)
							{
								unset( $this->parameters['params'][$i] );
							}
						}
                        
                        if( $value1 !== null)
                        {
							$name = array('table' => $condition['table'], 'field' => $condition['property']);
                            $c->addString( $name, \damix\engines\orm\conditions\OrmOperator::cast( $condition['operator']), $value1, $condition['group']);
                        }
						
                        break;
				}
			
            }
        }
		
        if( isset( $this->parameters['filter'] ) )
        {
			$name = $this->parameters['name'];
			
			// \damix\engines\logs\log::dump( $this->filters );
            foreach( $this->parameters['filter'] as $field => $filters )
            {
                if( count( $filters ) > 0 && isset( $this->filters[$name] ))
                {
                    $property = $this->filters[$name][$field];
					$field = $property['field'];
                    switch( $property['datatype'] )
                    {
                        case 'date':
                        case 'datetime':
			
							if( ! empty( $filters[0] ) )
							{
								$val1 = \damix\engines\tools\xDate::load( $filters[0] );
							}
							else
							{
								$val1 = null;
							}
							switch( $property['operator'] )
							{
								case 'period':
									if( isset( $filters[1] ) && ! empty( $filters[1] ) )
									{
										$val2 = \damix\engines\tools\xDate::load( $filters[1] );
									}
									else
									{
										$val2 = null;
									}
									$c->addPeriod( $field, $val1, $val2, 'g1' );
									break;
								default:
									if( $val1 )
									{
										$c->addDate( $field, \damix\engines\orm\conditions\OrmOperator::cast( $property['operator'] ), $val1, 'g1' );
									}
									break;
							}
							
                            break;
                        case 'contain':
							if( empty( $filters[0] ) )
							{
								break;
							}
                            $c->addContain( $field, $filters[0], 'g1' );
                            break;
                        case 'select':
							if( empty( $filters[0] ) )
							{
								break;
							}
							if( is_array( $filters[0] ) )
							{
								$tmp = array();
								$null = false;
								$nb = 0;
								foreach( $filters[0] as $filter )
								{
									if( $filter == '#null#' )
									{
										$null = true;
									}
									else
									{
										$tmp[] = $filter;
										$nb ++;
									}
								}
								
								if( $null || $nb > 0 )
								{
									$c->addGroupBegin( 'g1' );
								}
								if( $null )
								{
									$c->addNull( $field, 'g1' );
									if( $nb > 0 )
									{
										$c->addLogic( \damix\engines\orm\conditions\OrmOperator::ORM_OP_OR );
									}
								}
								if( $nb > 0 )
								{
									$c->addString( $field, $property['operator'], $tmp, 'g1' );
								}
								if( $null || $nb > 0 )
								{
									$c->addGroupEnd( 'g1' );
								}
							}
							else
							{
								if( $filters[0] == '#null#' )
								{
									$c->addNull( $field, 'g1' );
								}
								else
								{
									$c->addString( $field, \damix\engines\orm\conditions\OrmOperator::cast( $property['operator'] ), $filters[0], 'g1' );
								}
							}
                            break;
                        case 'bool':
							if( $filters[0] !== '' )
							{
								$c->addBool( $field, \damix\engines\orm\conditions\OrmOperator::cast( $property['operator'] ), ( $filters[0] == '1' ? true : false), 'g1' );
							}
                        default:
							if( ! empty( $filters[0] ) )
							{
								$c->addString( $field, \damix\engines\orm\conditions\OrmOperator::cast( $property['operator'] ), $filters[0], 'g1' );
							}
                            break;
                    }
                }
				
            }
        }

        // if( isset( $this->parameters['params'] ) and count($this->parameters['params']) > 0 )
        // {
            // $c->addGroupBegin( 'params' );
            // foreach( $this->parameters['params'] as $field => $params )
            // {
                // $datatype = $params['datatype'] ?? 'string';
                // switch( $datatype )
                // {
                    // case 'date':
                        // $c->addDate($params['table'] . '.' . $params['property'], \damix\engines\orm\conditions\OrmOperator::cast( $params['operator'] ), $params['value1'], $params['value2'] ?? null, 'params' );
                        // break;
                    // default:
                        // $c->addString( $params['table'] . '.' . $params['property'], \damix\engines\orm\conditions\OrmOperator::cast( $params['operator'] ), $params['value1'], 'params' );
                        // break;
                // }
            
            // }
            // $c->addGroupEnd( 'params' );
        // }
	}
	
	protected function getDataValuesOrders($name = null)
    {
        $function = $this->ormselector['function'];
        
        $o = $this->orm->getOrdersClear( $function );
		
		$orders = $this->parameters['orders'] ?? array();
		
		if( count( $orders ) == 0)
		{
			$orders = $this->orders[ $name ?? $this->default ] ?? array();
		}

		foreach( $orders as $info )
		{		
			$order = new \damix\engines\orm\request\structure\OrmOrder();
			$order->setColumn($info['name']);
			$order->setWay(\damix\engines\orm\request\structure\OrmOrderWay::cast( $info['way'] ));
			$o->add( $order );
		}
	}
	
	public function getOrders( ?string $name = null ) : array
    {
        $table = $this->_orders[ $name ?? $this->_default ] ?? array();
        $out = array();
        foreach( $table as $elt )
        {
            $out[ $elt['name'] ] = $elt;
        }
        return $out;
    }
	
	 public function getDataFilter( ?string $name = null ) : array
    {
        $table = $this->_filters[ $name ?? $this->_default ] ?? array();
        $out = array();
        foreach( $table as $elt )
        {
            $out[ $elt['name'] ] = $elt;
        }
        return $out;
    }
}