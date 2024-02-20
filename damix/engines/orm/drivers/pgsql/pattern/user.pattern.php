<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\drivers\pattern;

class OrmDriversPatternUser
    extends \damix\engines\orm\drivers\OrmDriversPatternBase
{
    public function execute()
    {
        return array( 
			'value' => \damix\engines\tools\xTools::login(), 
			'datatype' => \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR, 
			'null' => false
		);
    }
}