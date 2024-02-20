<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm;

class OrmBaseRecord
{
	protected array $_properties = array();
    protected array $_table = array();
	
	public function __construct()
    {
     
    }
	
	public function __default() : void
    {
        
    }
	
	public function getProperty( string $name ) : ?array
    {
        return $this->_properties[ $name ] ?? null;
    }
	
	public function __get( string $name ) : mixed
    {
        return $this->_properties[ $name ][ 'value' ] ?? null;
    }
    
    public function __set( string $name, mixed $value ) : void
    {
        if( isset( $this->_properties[ $name ] ) )
        {
            $this->_properties[ $name ][ 'value' ] = $value;
            $this->_properties[ $name ][ 'update' ] = true;
        }
    }
	
	public function setValue( string $name, mixed $value ) : void
    {
        $this->_properties[ $name ][ 'value' ] = $value;
        $this->_properties[ $name ][ 'update' ] = false;
    }
    
	public function getValue( $name ) : mixed
    {
        return $this->_properties[ $name ][ 'value' ] ?? null;
    }
	
	public function isUpdate( $name ) : bool
    {
        return $this->_properties[ $name ][ 'update' ] ?? false;
    }
	
	public function clearUpdate() : void
    {
        foreach( $this->_properties as $name => $prop )
		{
			$this->_properties[ $name ][ 'update' ] = false;
		}
    }
	
	public function loadrecord( $record )
    {
        $table = $this->_table;
        $properties = $this->_properties;
        if( $record )
        {
            foreach( $properties as $name => $property )
            {
                $this->setValue( $property[ 'name' ], $record->{ $table[ 'name' ] . '_' . $property[ 'name' ] } );
            }
        }
    }
}