<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace datatable\drivers;

class DatatableFunctionDonnee
     extends \datatable\drivers\DatatableDriver
{
    public function Execute( $args ) : ?\tools\monkey\IMonkeyObject
    {
        $property = $args[0]->Inspect();
       
        $somme = array();
        $parameters = \datatable\drivers\DatatableDriver::$parameters;
        
		if( $parameters instanceof mariadbDbResultSet )
		{
			foreach( $parameters as $record )
			{
				$i = new \tools\monkey\MonkeyString();
				$i->Value = $record->$property;
				$somme[] = $i;
			}
			$result = new \tools\monkey\MonkeyArray();
			$result->Elements = $somme;
			return $result;
		}
		else
		{
			if( isset( $parameters->$property ) )
			{
				return new \tools\monkey\MonkeyField( $parameters->$property );		   
			}
		}
		
		$result = new \tools\monkey\MonkeyString();
        $result->Value = '';
        return $result;
    }
}