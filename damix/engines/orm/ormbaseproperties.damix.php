<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm;

abstract class OrmBaseProperties
{
	protected array $_properties = array();
    protected array $_primarykeys = array();
    protected array $_table = array();
    protected array $_index = array();
    protected string $_selector = '';
	
    public string $_prefix = '';
    public string $_suffix = '';
	
	public function __construct()
    {
     
    }
	
	public function __set( $name, $value )
    {
        $this->_properties[ $name ][ 'value' ] = $value;
		$this->_properties[ $name ][ 'update' ] = true;
    }
    
    public function __get( $name )
    {
        return $this->_properties[ $name ][ 'value' ] ?? null;
    }
	
	public function getProperty( $name ) : array
    {
        return $this->_properties[ $name ];
    }
	
	public function getProperties() : array
	{
		return $this->_properties;
	}
	
	public function getIndexes() : array
    {
        return $this->_index;
    }
	
	public function load( mixed $id ) : void
    {
        $obj = \damix\engines\orm\Orm::get( $this->_selector );
		
        $record = $obj->get( strval( $id ) );
		
        $this->loadrecord( $record );
    }
	
	public function loadrecord( $record, string $prefix = '' ) : bool
    {
        $properties = $this->_properties;
        if( $record )
        {
            foreach( $properties as $name => $property)
            {
				$this->_properties[ $name ][ 'value' ] = $record->{$prefix . $name};
				$this->_properties[ $name ][ 'update' ] = false;
            }
			return true;
        }
		
		return false;
    }
	
	public function onCreateBefore() : void{}
	public function onUpdateBefore() : void{}
	public function onCreate() : void{}
	public function onCreateAfter() : void{}
	public function onUpdate() : void{}
	public function onUpdateAfter() : void{}
	
	public function save() : bool
    {
        $orm = \damix\engines\orm\Orm::get( $this->_selector );
        $pk = $this->{$this->_primarykeys['name']};
        
        if( $pk > 0 )
        {
			$record = $orm->get( strval( $pk ) );
        
            $update = true;
            
			$this->onUpdateBefore(); 
        }
        else
        {
            $record = $orm->createRecord();
            
            $update = false;
			$this->onCreateBefore(); 
        }
        $properties = $this->_properties;
        
        foreach( $properties as $name => $property )
        {
			if( !isset( $property['update']) || $property['update'] )
			{
				$record->{ $property['realname'] } = $this->{$name};
			}
        }
        
		\damix\engines\events\Event::notify( 'ormbeforesave', array('record' => $record, 'orm' => $this));
		
        if( $update )
        {
			$this->onUpdate(); 
            $nb = $orm->update( $record );
			$this->onUpdateAfter(); 
        }
        else
        {
			$this->onCreate(); 
            $nb = $orm->insert( $record );
			$this->onCreateAfter(); 
        }
		
		\damix\engines\events\Event::notify( 'ormaftersave', array('record' => $record, 'orm' => $this));
        $this->{$this->_primarykeys['name']} = $record->{$this->_primarykeys['name']};
		
		return $nb > 0 ? true : false;
    }
	
	public function delete( mixed $id ) : bool
    {
        $obj = \damix\engines\orm\Orm::get( $this->_selector );
		
        return $obj->delete( $id ) > 0 ? true : false;
    }
    
    public function deletein( array $pk ) : bool
    {
        $obj = \damix\engines\orm\Orm::get( $this->_selector );

        foreach( $pk as $id )
        {
            $obj->delete( $id );
        }
		
		return true;
    }
	
	public function clear() : void
    {
        foreach( $this->_properties as $name => &$prop )
        {
            $prop['value'] = null;
        }
    }
	
	public function setArrayFormat( array $params ) : void
    {
		// \damix\engines\logs\log::dump( $params );
        foreach( $this->_properties as $name => $prop )
        {
            $field =  $this->_prefix . $prop['name'] . $this->_suffix;
            $exist = true;
			if( !isset( $params[ $field ] ) )
			{
				$exist = false;
			}
			
			$value = $params[ $field ] ?? null;
			
			if( $value === '' )
			{
				$value = $this->getDefault($prop);
			}
			
			if( $value === '#null#' )
			{
				$value = null;
			}
			switch( $this->_properties[ $name ]['datatype'] )
			{
				case 'autoincrement':
				case 'int':
				case 'smallint':
				case 'tinyint':
				case 'bigint':
					if( $value !== null )
					{
						
						$value = intval( \damix\engines\tools\xTools::parseInt( $value ) );
					}
					break;
				case 'decimal':
				case 'float':
				case 'real':
				case 'numeric':
					if( $value !== null )
					{
						$value = \damix\engines\tools\xTools::parseFloat( $value );
					}
					break;
				case 'bool':
					if( $value !== null )
					{
						$value = $value == '1' ? true : false;
					}
					break;
				case 'date':
				case 'timestamp':
				case 'datetime':
				case 'time':
		// \damix\engines\logs\log::dump( $prop );
					if( $value === null )
					{
						$value = $params[ 'date_' . $field ] ?? null;
						if( isset( $params[ 'date_' . $field ] ) )
						{
							$exist = true;
						}
					}
					if( $value === null )
					{
						$value = $params[ 'time_' . $field ] ?? null;
						if( isset( $params[ 'time_' . $field ] ) )
						{
							$exist = true;
						}
					}
					else
					{
						$value = \damix\engines\tools\xDate::load( $value );
					}
					
					break;
			}
			
			if( $exist )
			{
				$this->{$name} = $value;
			}
			
        }
    }
	
	protected function getDefault( array $property) : mixed
	{
		if( ! isset( $property['default'] ) )
		{
			return null;
		}
		if(strtoupper($property['default']) === 'NULL')
		{
			return null;
		}
		return $property['default'];
	}
    
    public function getFormatArray() : array
    {
        $out = array();

        foreach( $this->_properties as $name => $prop )
        {
            $value = $prop[ 'value' ] ?? null;
      
            $format = $this->_properties[ $name ]['format'];
			if( empty( $format ) )
			{
				$format = $this->_properties[ $name ]['datatype'];
			}
            $out = array_merge($out, $this->getFormatValue( $format, $name, $value ));
        }
        
        return $out;
    }
    
    protected function getFormatValue( string $datatype, string $name, mixed $value ): array
    {
        $out = array();
        switch( $datatype )
        {
            case 'int':
                if( $value !== null )
                {
                    $value = \damix\engines\tools\xTools::numberFormat( floatval($value), 0 );
                }
                break;
            case 'decimal':
                if( $value !== null )
                {
                    $value = \damix\engines\tools\xTools::numberFormat( floatval($value), 2, '.', '' );
                }
                break;
            case 'bool':
                $value = tobool( $value ) ? 1 : 0;
                break;
            case 'date':
                $xdate = \damix\engines\tools\xDate::load( $value );
                if( $xdate )
                {
                    $value = $xdate->format( \damix\engines\tools\xDate::LANG_DFORMAT );
                    $out['date_' . $this->_prefix . $name . $this->_suffix ] = $value;
                }
				else
				{
					$out['date_' . $this->_prefix . $name . $this->_suffix ] = '';
				}
                break;
            case 'datetime':
                $xdate = \damix\engines\tools\xDate::load( $value );
                if( $xdate )
                {
                    $value = $xdate->format( \damix\engines\tools\xDate::LANG_DTFORMAT );
                    $out['date_' . $this->_prefix . $name . $this->_suffix ] = $xdate->format( \damix\engines\tools\xDate::LANG_DFORMAT );
                    $out['time_' . $this->_prefix . $name . $this->_suffix ] = $xdate->format( \damix\engines\tools\xDate::LANG_TFORMAT );
                }
				else
				{
					$out['date_' . $this->_prefix . $name . $this->_suffix ] = '';
                    $out['time_' . $this->_prefix . $name . $this->_suffix ] = '';
				}
                break;
            case 'time':
				if( $value !== null )
                {
					$xdate = \damix\engines\tools\xDate::load( $value );
					if( $xdate )
					{
						$value = $xdate->format( \damix\engines\tools\xDate::LANG_TFORMAT );
						$out['time_' . $this->_prefix . $name . $this->_suffix ] = $xdate->format( \damix\engines\tools\xDate::LANG_TFORMAT );
					}
					else
					{
						$out['time_' . $this->_prefix . $name . $this->_suffix ] = '';
					}
				}
                break;
        }
        
        $out[ $this->_prefix . $name . $this->_suffix ] = $value;
        
        return $out;
    }
    
    public function getFormatObject()
    {
        $out = new \stdClass();
        
        foreach( $this->_properties as $name => $prop )
        {
            $value = $prop[ 'value' ] ?? null;
			$format = $this->_properties[ $name ]['format'];
			if( empty( $format ) )
			{
				$format = $this->_properties[ $name ]['datatype'];
			}
            switch( $format )
            {
                case 'int':
                    $value = \damix\engines\tools\xTools::numberFormat( floatval($value), 0 );
                    break;
                case 'decimal':
                    $value = \damix\engines\tools\xTools::numberFormat( floatval($value) );
                    break;
                case 'bool':
                    $value = tobool( $value );
                    break;
                case 'date':
                    $xdate = \damix\engines\tools\xDate::load( $value );
					if( $xdate )
                    {
						$value = $xdate->format( \damix\engines\tools\xDate::LANG_DFORMAT );
					}
                    break;
                case 'datetime':
                    $xdate = \damix\engines\tools\xDate::load( $value );
					if( $xdate )
                    {
						$value = $xdate->format( \damix\engines\tools\xDate::LANG_DTFORMAT );
						$out->{'date_' . $this->_prefix . $name . $this->_suffix} = $xdate->format( \damix\engines\tools\xDate::LANG_DFORMAT );
						$out->{'time_' . $this->_prefix . $name . $this->_suffix} = $xdate->format( \damix\engines\tools\xDate::LANG_TFORMAT );
					}
                    break;
                case 'time':
					if( $value !== null )
					{
						$xdate = \damix\engines\tools\xDate::load( $value );
						if( $xdate )
						{
							$value = $xdate->format( \damix\engines\tools\xDate::LANG_TFORMAT );
						}
					}
                    break;
            }
            $out->{$this->_prefix . $name . $this->_suffix} = $value;
        }
        
        return $out;
    }
   
    
}