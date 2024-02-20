<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\orm\drivers;


class OrmDriversPgsqlFunctionAdddate
    extends \damix\engines\orm\drivers\OrmDriversFunctionBase
{
    protected function getName() : string
	{
		return 'DATEADD';
	}
	
	protected function ToString() : string 
    {        
        $out = array();
		
		$cast = '';
		if( $this->parameters[0]->getColumnType() == \damix\engines\orm\request\structure\OrmColumnType::COLUMN_VALUE )
		{
			$cast = 'timestamp ';
		}
		
        $out[] = $cast . $this->getValue( $this->parameters[0] );
        $out[] = '+ INTERVAL';
        $out[] = $this->getValue( $this->parameters[1] ) . ' ' . $this->getUnite( $this->parameters[2]->getColumnRaw() );
       
        
        return '(' . implode( ' ', $out) . ')';
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