<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\conditions;


class OrmConditions
	extends \damix\engines\tools\Arraylist
{
	public function getDataValue( string $name, int $number ) : string
    {
        return $this[$name]->getDataValue( $number );
    }
	
	public function add( \damix\engines\orm\conditions\OrmCondition $condition, string $name = 'default' ) : void
	{
		$this[$name] = $condition;
	}
}