<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\views\gabarits;


class GabaritBase
{
    protected $_properties = array();
    protected $_parameters = array();
    
    public function __construct()
    {
    }
    
    public function check( array $params, array &$out ) : bool
    {
        $error = false;
        foreach( $this->_properties as $name => $value )
        {
            $required = $value['required'] ?? false;
            $expr = $value['expr'] ?? false;
            
            if( $required )
            {
                if( isset( $params[ $name ] ) )
                {
                    if( $params[ $name ] == '' )
                    {
                        $orm = \damix\engines\orm\Orm::getStructure( $value['ref']['orm'] );

                        if( $orm )
                        {
                            $locale = \damix\engines\locales\Locale::get( $orm->selector->getPart('module') . '~lclerror.required.' . $orm->name . '.' . $value['ref']['property'] );
                        }
                        
                        $out[] = array(
                            'locale' => $locale,
                            'property' => $name,
                        );
                        $error = true;
                    }
                    
                }
            }
        }
        
        return !$error;
    }
	
	public function mergeParameters($params = array())
	{
		$this->_parameters = array_merge( $this->_parameters, $params );
	}
	
	public function getParam($value)
	{
		return isset( $this->_parameters[ $value ] )? $this->_parameters[ $value ] : null;	
	}
	
	public function getProperty($value)
	{
		return isset( $this->_properties[ $value ] )? $this->_properties[ $value ] : null;	
	}
}