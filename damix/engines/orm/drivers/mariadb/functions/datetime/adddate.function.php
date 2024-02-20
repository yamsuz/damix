<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\orm\drivers;


class OrmDriversMariadbFunctionAdddate
    extends \damix\engines\orm\drivers\OrmDriversFunctionBase
{
    protected function getName() : string
	{
		return 'ADDDATE';
	}
	
	protected function ToString() : string 
    {        
        $out = array();
		
        $out[] = $this->getValue( $this->parameters[0] );
        $out[] = 'INTERVAL ' . $this->getValue( $this->parameters[1] ) . ' ' . $this->getUnite( $this->parameters[2]->getColumnRaw() );
       
        
        return $this->getName() . '(' . implode( ', ', $out) . ')';
    }

	protected function getUnite(\damix\engines\orm\request\structure\OrmValue $unite) : string
	{
		return match( $unite->getValue() ){
			'YEAR' => 'YEAR',
			'MONTH' => 'MONTH',
			'WEEK' => 'WEEK',
			'DAY' => 'DAY',
			'HOUR' => 'HOUR',
			'MINUTE' => 'MINUTE',
			'SECOND' => 'SECOND',
			'MICROSECOND' => 'MICROSECOND',
			default => throw new \damix\core\exception\OrmException( 'Unit unknown' ),
		};

	}
}