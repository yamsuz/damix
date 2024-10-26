<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\combo;


class OrmComboBase
{
    protected string $_html = '';
    protected string $_orm;
    protected string $_title;
    protected string $_color;
    protected string $_value;
    protected string $_function;
    protected string $_parent;
    protected int $_offset;
    protected int $_rowcount;
    protected bool $_firstempty;
    protected bool $_remotedata;
    protected array $_data = array();
    protected array $_property = array();
    
    public function __construct()
    {
    }   
    
    public function getInnerHtml() : string
    {
        return $this->_html;
    }
    
    protected function propertyinit()
    {
		
    }
    
	public function getDisplays() : array
	{
		$out = array();
	   
		foreach( $this->_property['option'] as $prop )
		{
			switch( $prop['type'] )
			{
				case 'reference':
				case 'property':
					$out[] = $prop['value'];
					break;
			}
		}
		
		return $out;
	}
	
	public function getRemoteData() : bool
	{
		return $this->_remotedata;
	}
	
	public function getValue() : string
	{
		return $this->_value;
	}
    
    public function getHtml(string $name, string $placeholder = '', bool $multiple = false) : string
    {
		$out = '<select class="form-control m-select2 select2_simple xdatafiltre"' . ( $multiple ? ' multiple="multiple"' : '' ). ' name="' . $name . '"';
		if( ! empty( $placeholder ) )
		{
			$out .= 'data-placeholder="' . $placeholder . '"';
		}
		$out .= '>';
		$out .= $this->getInnerHtml();
		$out .= '</select>';
		
        return $out;
    }
    
    public function getConditionsClear()
    {
        $obj = \damix\engines\orm\Orm::get( $this->_orm );
        $c = $obj->getConditionsClear( $this->_function );
        return $c;
    }
    
    public function getConditions()
    {
        $obj = \damix\engines\orm\Orm::get( $this->_orm );
        $c = $obj->getConditions( $this->_function );
        return $c;
    }
    
    public function getInnerConditions( $limit = 0, $offset = 0 )
    {
        $obj = \damix\engines\orm\Orm::get( $this->_orm );
        $o = $obj->getOrdersClear( $this->_function );
		
		foreach( $this->_property[ 'orders' ] as $order )
        {
            $o->add( $order[ 'property' ], $order[ 'way' ] );
        }
        
        $html = '';
        if( $this->_firstempty )
        {
            $html .= '<option></option>';
        }

        if( $limit > 0 )
        {
            $l = $obj->getLimits( $this->_function );
            $l->RowCount = $limit;
            $l->Offset = $offset;
        }
        
        $liste = $obj->{ $this->_function }();
		
		
		$propvalue = $this->getProperty( $this->_value );
		
        foreach( $liste as $info )
        {
            $display = '';
            
            foreach( $this->_property[ 'option' ] as $dis )
            {
                switch( $dis[ 'type' ] )
                {
                    case 'property':
                        $display .= $info->{ $dis[ 'value' ] };
                        break;
					case 'reference':
						$params['value'] = $this->getProperty( $dis[ 'value' ] );;
						$display .= $info->{$params['value']};
                    case 'string':
                        $display .= $dis[ 'value' ];
                        break;
                }
            }

            $html .= '<option value="'. $info->{ $propvalue } .'">'. preg_replace( '/\'/', '\\\'', $display ) .'</option>';
        }
		
        return $html;
    }
	
	public function getInnerConditionsArray( int $limit = 0, int $offset = 0 )
    {
        $obj = \damix\engines\orm\Orm::get( $this->_orm );
        $o = $obj->getOrdersClear( $this->_function );
        
        foreach( $this->_property[ 'orders' ] as $order )
        {
			$ormorder = new \damix\engines\orm\request\structure\OrmOrder();

			$ormorder->setColumn( $order[ 'property' ] );
			$ormorder->setWay(\damix\engines\orm\request\structure\OrmOrderWay::cast( $order[ 'way' ]) );
			
			$o->add( $ormorder );
        }
		
        $result = array();
		
		if( $limit > 0 )
        {
            $l = $obj->getLimits( $this->_function );
            $l->RowCount = $limit;
            $l->Offset = $offset;
        }
		
        $liste = $obj->{ $this->_function }();
		$propvalue = $this->getProperty( $this->_value );
        foreach( $liste as $info )
        {
			$display = '';
            
            foreach( $this->_property[ 'option' ] as $dis )
            {
                switch( $dis[ 'type' ] )
                {
                    case 'property':
                        $display .= $info->{ $dis[ 'value' ] };
                        break;
                    case 'reference':
						$params['value'] = $this->getProperty( $dis[ 'value' ] );
						$display .= $info->{$params['value']};
                        break;
                    case 'string':
                        $display .= $dis[ 'value' ];
                        break;
                }
            }

            $result[] = array( 
                'id' => $info->{ $propvalue }, 
                'text' => preg_replace( '/\'/', '\\\'', $display ) 
            );
        }
		
        return $result;
    }
	
	public function getProperty(string $value): string
	{
		if( $struct = \damix\engines\orm\Orm::getDefine( $value ))
		{
			$field = $struct['field'];

			if( $field )
			{
				return $struct['orm']->realname . '_' . $field['realname'];
			}
		}
		return $value;
	}
}