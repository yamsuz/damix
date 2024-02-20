<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


enum OrmOrderWay
{
	case WAY_ASC;
	case WAY_DESC;
	
	public static function cast(string $value): OrmOrderWay
    {
        return match($value) {			
			'asc' => OrmOrderWay::WAY_ASC,
			'desc' => OrmOrderWay::WAY_DESC,
        };
    }
	
	
}