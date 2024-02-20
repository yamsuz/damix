<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\orm\drivers;


class OrmDriversPgsqlFunctionIfnull
    extends \damix\engines\orm\drivers\OrmDriversFunctionBase
{
    protected function getName() : string
	{
		return 'CASE';
	}
	
	protected function ToString() : string 
    {        
        $out = array();
		
		if( count( $this->parameters ) != 2 )
		{
			throw new \damix\core\exception\CoreException( 'Number of parameters : 5' );
		}
		
		$out[] = 'WHEN';
		$out[] = $this->getValue( $this->parameters[0] ) . ' IS NOT NULL';
		$out[] = 'THEN';
		$out[] = $this->getValue( $this->parameters[0] );
		$out[] = 'ELSE';
		$out[] = $this->getValue( $this->parameters[1] );
		$out[] = 'END';
		
        return $this->getName() . ' ' . implode( ' ', $out);
    }
}