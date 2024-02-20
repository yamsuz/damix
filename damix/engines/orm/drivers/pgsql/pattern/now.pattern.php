<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\drivers\pattern;

class OrmDriversPatternNow
    extends \damix\engines\orm\drivers\OrmDriversPatternBase
{
    public function execute()
    {
        return array( 
			'value' => 'NOW()', 
			'datatype' => \damix\engines\orm\request\structure\OrmDataType::ORM_SQL, 
			'null' => false
		);
    }
}