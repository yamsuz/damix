<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\template\drivers;

class MonkeyFunctionSomme
    extends \damix\engines\template\drivers\monkey\MonkeyDriver
    
{
    public function Execute( $args ) : ?\damix\engines\monkey\IMonkeyObject
    {
        $property = array();
        
        $somme = 0;
        
        foreach( $args as $argument )
        {
            switch( $argument->Type )
            {
                case \damix\engines\monkey\ObjectType::ARRAY:
                    $property = $argument->Elements;
                    foreach( $property as $record )
                    {
                        $somme += $record->Inspect();
                    }
                    break;
                case \damix\engines\monkey\ObjectType::STRING:
                    $property = $argument->Inspect();
                    $parameters = \datatable\drivers\DatatableDriver::$parameters;
                    foreach( $parameters as $record )
                    {
						if( isset( $record->$property ) && is_numeric( $record->$property ) )
						{
							$somme += $record->$property;
						}
                    }
                    break;
                case \damix\engines\monkey\ObjectType::FIELD:
                case \damix\engines\monkey\ObjectType::INTEGER:
                    $property = $argument->Inspect();
					if( is_numeric( $property ) )
					{
						$somme += $property;
					}
                    break;
            }
        }
        
        $result = new \damix\engines\monkey\MonkeyInteger();
        $result->Value = $somme;
        return $result;
    }
}