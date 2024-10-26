<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\conditions;


class OrmCondition
{
	protected array $_data = array();
    protected array $_hashdata = array();
    protected int $_number = 0;
    protected \damix\engines\orm\drivers\OrmDriversBase $_driver;
    
    public function __construct(string $profile = '')
    {
        $this->_driver = \damix\engines\orm\drivers\OrmDrivers::getDriver($profile);
    }
    
    public function getHashData() : array
    {
        return $this->_hashdata;
    }
    public function getData() : array
    {
        return $this->_data;
    }
    
    public function clear() : void
    {
        $this->_data = array();
        $this->_hashdata = array();
        $this->_number = 0;
    }
	
	public function getDataValue( int $number ) : string
    {
		// \damix\engines\logs\log::dump( $this->_data );
        $condition = $this->_data[ $number ];
        if( is_array( $condition['right'] ) && $condition['operator'] == \damix\engines\orm\conditions\OrmOperator::ORM_OP_IN )
		{
			$out = array() ;
			
			
			foreach( $condition['right'] as $right )
			{
				$value = $this->_driver->getValue( $right, $condition['rightdatatype'], $condition['operator'] );
				switch( $condition['rightdatatype'] )
				{
					case \damix\engines\orm\request\structure\OrmDataType::ORM_CHAR:
					case \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR:
					case \damix\engines\orm\request\structure\OrmDataType::ORM_TEXT:
					case \damix\engines\orm\request\structure\OrmDataType::ORM_LONGTEXT:
						$value = $this->getValue( $value );
						break;
				}
				$out[] = $value;
			}
			
			return '(' . implode(', ', $out ) . ')';
		}
		else
		{
			$value = $this->_driver->getValue( $condition['right'], $condition['rightdatatype'], $condition['operator'] );
			switch( $condition['rightdatatype'] )
			{
				case \damix\engines\orm\request\structure\OrmDataType::ORM_CHAR:
				case \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR:
				case \damix\engines\orm\request\structure\OrmDataType::ORM_TEXT:
				case \damix\engines\orm\request\structure\OrmDataType::ORM_LONGTEXT:
					$value = $this->getValue( $value );
					break;
			}
			return $value;
		}
    }
	
	private function getValue(mixed $value )
	{
		if( $this->_driver->isCaseManagement() )
		{
			$params = array( 'type'=> 'raw', 'value' => $value );
			
			$formula = new \damix\engines\orm\request\structure\OrmFormula();
			$formula->setName('upper');
			$formula->addParameterArray( array( $params ) );
			
			if( $formula )
			{
				$obj = \damix\engines\orm\drivers\OrmDriversBase::getDriverFunction( $this->_driver->_cnx->getDriver(), $formula->getName() );
				if( $obj !== null )
				{
					$obj->driver = $this->_driver;
					return $obj->execute( $formula );
				}
			}
		}
		
		return $value;
	}
	
	public function addGroupBegin( string $name = '' ) : void
    {
        $this->_data[] = array( 
            'type' => \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPBEGIN,
            'value' => 'begin',
            'name' => $name,
            'number' => $this->_number,
        );
        
        $this->_hashdata[] = array( 'type' => \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPBEGIN, 'number' => $this->_number );
        
        $this->_number ++;
    }
    
    public function addGroupEnd( string $name = '' ) : void
    {
        $this->_data[] = array( 
            'type' => \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPEND,
            'value' => 'end',
            'name' => $name,
            'number' => $this->_number,
        );
        
        $this->_hashdata[] = array( 'type' => \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_GROUPEND, 'number' => $this->_number );
        
        $this->_number ++;
    }
	
	public function addLogic( \damix\engines\orm\conditions\OrmOperator $value = \damix\engines\orm\conditions\OrmOperator::ORM_OP_AND, string $name = '' ) : void
    {
        $this->_data[] = array( 
            'type' => \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_LOGIC,
            'value' => $value,
            'name' => $name,
            'number' => $this->_number,
        );
        
        $this->_hashdata[] = array( 'type' => \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_LOGIC, 'value' => $value, 'number' => $this->_number );
        
        $this->_number ++;
    }
	
	public function addString( string|array|\damix\engines\orm\request\structure\OrmFormula $property, ?\damix\engines\orm\conditions\OrmOperator $operator, string|array $value, string $name = '' ) : void
    {
        $this->addCondition($property, $operator, $value, $name, \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR );
    }
	
	public function addNull( string|array|\damix\engines\orm\request\structure\OrmFormula $property, string $name = '' ) : void
    {
        $this->addCondition($property, \damix\engines\orm\conditions\OrmOperator::ORM_OP_ISNULL, null, $name, \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR );
    }
	
	public function addInt( string|array $property, ?\damix\engines\orm\conditions\OrmOperator $operator, int|array $value, string $name = '' ) : void
    {
        $this->addCondition($property, $operator, $value, $name, \damix\engines\orm\request\structure\OrmDataType::ORM_INT );
    }
	
	public function addBool( string|array $property, ?\damix\engines\orm\conditions\OrmOperator $operator, bool|array $value, string $name = '' ) : void
    {
        $this->addCondition($property, $operator, $value, $name, \damix\engines\orm\request\structure\OrmDataType::ORM_BOOL );
    }
	
	public function addDecimal( string|array $property, ?\damix\engines\orm\conditions\OrmOperator $operator, float|array $value, string $name = '' ) : void
    {
        $this->addCondition($property, $operator, $value, $name, \damix\engines\orm\request\structure\OrmDataType::ORM_DECIMAL );
    }
	
	public function addDate( string|array $property, ?\damix\engines\orm\conditions\OrmOperator $operator, \damix\engines\tools\xDate $value, string $name = '' ) : void
    {
		$this->addCondition($property, $operator, $value, $name, \damix\engines\orm\request\structure\OrmDataType::ORM_DATE );
    }
	
	public function addDateTime( string|array $property, ?\damix\engines\orm\conditions\OrmOperator $operator, \damix\engines\tools\xDate $value, string $name = '' ) : void
    {
		$this->addCondition($property, $operator, $value, $name, \damix\engines\orm\request\structure\OrmDataType::ORM_DATETIME );
    }
	
	public function addPeriod( string|array $property, ?\damix\engines\tools\xDate $value1, ?\damix\engines\tools\xDate $value2, string $name = '' ) : void
    {
		if( $value1 === null && $value2 === null )
		{
			return;
		}
        $this->addGroupBegin( $name );
		if( $value1 !== null )
		{
			$this->addDate( $property, \damix\engines\orm\conditions\OrmOperator::ORM_OP_GTEQ, $value1, $name );
		}
		if( $value2 !== null )
		{
			$value2->addDay( 1 );
			if( $value1 !== null )
			{
				$this->addLogic( \damix\engines\orm\conditions\OrmOperator::ORM_OP_AND, $name );
			}
			$this->addDate( $property, \damix\engines\orm\conditions\OrmOperator::ORM_OP_LT, $value2, $name );
		}
        $this->addGroupEnd( $name );
    }
	
	public function addFunction( mixed $property, ?\damix\engines\orm\conditions\OrmOperator $operator, string $value, string $name = '' ) : void
    {
		$this->addCondition($property, $operator, $value, $name, \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR );
    }
	
	public function addCondition(mixed $property, ?\damix\engines\orm\conditions\OrmOperator $operator, mixed $value, string $name, \damix\engines\orm\request\structure\OrmDataType $rightdatatype ) : void
	{
		if( is_array( $value ) )
		{
			$operator = \damix\engines\orm\conditions\OrmOperator::ORM_OP_IN;
		}
		elseif( $operator === null )
        {
            $operator = \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ;
        }
		
		if( $property instanceof \damix\engines\orm\request\structure\OrmFormula )
		{
			$leftdatatype = \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FORMULA;
		}
		else
		{
			$leftdatatype = \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD;
		}
		
        $this->_data[] = array( 
            'type' => \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD,
            'leftdatatype' => $leftdatatype,
            'left' => $property,
            'operator' => $operator,
            'rightdatatype' => $rightdatatype,
            'right' => $value,
            'name' => $name,
            'number' => $this->_number,
        );
        
        $this->_hashdata[] = array( 'type' => \damix\engines\orm\request\OrmPropertyType::ORM_TYPE_FIELD, 'leftdatatype' => $leftdatatype, 'left' => $property, 'operator' => $operator, 'number' => $this->_number );
        
        $this->_number ++;
	}
	
}