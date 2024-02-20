<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\orm\drivers;


class OrmDriversPgsqlFunctionAvg
    extends \damix\engines\orm\drivers\OrmDriversFunctionBase
{
    protected function getName() : string
	{
		return 'AVG';
	}
}