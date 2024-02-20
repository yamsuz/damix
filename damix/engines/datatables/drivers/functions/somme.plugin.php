<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace datatable\drivers;

class DatatableFunctionSomme
    extends \datatable\drivers\DatatableDriver
    
{
    public function Execute( $args ) : ?\tools\monkey\IMonkeyObject
    {
        $property = array();
        
        $somme = 0;
        
		if( count( $args ) > 0 )
		{
			foreach( $args as $argument )
			{
				switch( $argument->Type )
				{
					case \tools\monkey\ObjectType::ARRAY:
						$property = $argument->Elements;
						foreach( $property as $record )
						{
							$somme += $record->Inspect();
						}
						break;
					case \tools\monkey\ObjectType::STRING:
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
					case \tools\monkey\ObjectType::FIELD:
						$property = $argument->Inspect();
						if( is_numeric( $property ) )
						{
							$somme += $property;
						}
						break;
				}
			}
		}
        else
		{
			$parameters = \datatable\drivers\DatatableDriver::$parameters;
			foreach( $parameters as $value )
			{
				if( is_numeric( $value ) )
				{
					$somme += $value;
				}
			}
		}
		
        $result = new \tools\monkey\MonkeyInteger();
        $result->Value = $somme;
        return $result;
    }
}