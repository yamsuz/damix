<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\orm\drivers;


abstract class OrmDriversFunctionBase
{
    public OrmDriversBase $driver;
    protected array $parameters;    
    abstract protected function getName() : string;
    
    public function execute( \damix\engines\orm\request\structure\OrmFormula $formula ) : string 
    {
        $this->parameters = $formula->getParameters();
        
        $out = $this->ToString();
     
        return $out;
    }
	
    protected function ToString() : string 
    {        
        $out = array();
		
        foreach( $this->parameters as $param )
        {
            $out[] = $this->getValue( $param );
        }
       
        return $this->getName() . '(' . implode( ', ', $out) . ')';
    }
	
	protected function getValue( \damix\engines\orm\request\structure\OrmColumn $param)
	{
		// \damix\engines\logs\log::dump( $param );
		switch( $param->getColumnType() )
		{
			case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_FIELD:
				$field = $param->getField();
				return $this->driver->getFieldName( $field );
			case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_COMMA:
				return ', ';
			case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_RAW:
				$value = $param->getColumnRaw();
				return $value->getValue();
			case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_OPERATOR:
				$value = $param->getColumnRaw();
				return $this->driver->getOperator( $value->getValue() );
			case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_VALUE:
				$value = $param->getColumnRaw();
				return $this->driver->getValue( $value->getValue(), $value->getDatatype() );
			case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_FORMULA:
				$name = $param['name'];
				
				$obj = OrmDriversBase::getDriverFunction( $name );
				if( $obj !== null )
				{
					return $obj->execute( $param['params'] );
				}
				else
				{
					$formule = '';
					foreach( $param['params'] as $info )
					{
						switch( $info['type'] )
						{
							case 'property':
								$formule .= (isset($info['table']) ? $info['table'] . '.' : '') . $info['property'] ;
								break;
							case 'raw':
								$formule .= $info['value'];
								break;
							case 'comma':
								$formule .= ',';
								break;
						}
					}
					
					return $name . '(' . $formule . ')';
				}
				
				break;
			default:
				throw new \damix\core\exception\CoreException('Column type does not exist');
		}
	}
}