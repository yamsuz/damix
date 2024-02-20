<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\orm\drivers;


class OrmDriversPgsqlFunctionDatediff
    extends \damix\engines\orm\drivers\OrmDriversFunctionBase
{
    protected function getName() : string
	{
		return '';
	}
	
	protected function ToString() : string 
    {        
        $out = array();
		
		$cast1 = '';
		switch( $this->parameters[0]->getColumnType() )
		{
			case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_VALUE:
				$cast1 = 'timestamp ';
				break;
		}
		$cast2 = '';
		switch( $this->parameters[1]->getColumnType() )
		{
			case \damix\engines\orm\request\structure\OrmColumnType::COLUMN_VALUE:
				$cast2 = 'timestamp ';
				break;
		}
		
      
		return 'DATE_PART( \'day\', ' . $cast1 . $this->getValue( $this->parameters[0] ) . ' - ' . $cast2 . $this->getValue( $this->parameters[1] ) . ')';
    }
}