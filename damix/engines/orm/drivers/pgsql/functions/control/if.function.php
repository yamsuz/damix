<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\orm\drivers;


class OrmDriversPgsqlFunctionIf
    extends \damix\engines\orm\drivers\OrmDriversFunctionBase
{
    protected function getName() : string
	{
		return 'CASE';
	}
	
	protected function ToString() : string 
    {        
        $out = array();
		
		if( count( $this->parameters ) != 5 )
		{
			throw new \damix\core\exception\CoreException( 'Number of parameters : 5' );
		}
		
		$out[] = 'WHEN';
		$out[] = $this->getValue( $this->parameters[0] ) . ' ' . $this->getValue( $this->parameters[1] ) . ' ' . $this->getValue( $this->parameters[2] );
		$out[] = 'THEN';
		$out[] = $this->getValue( $this->parameters[3] );
		$out[] = 'ELSE';
		$out[] = $this->getValue( $this->parameters[4] );
		$out[] = 'END';
		
        return $this->getName() . ' ' . implode( ' ', $out);
    }
}