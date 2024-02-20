<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


enum OrmIndexType
{
	case ORM_INDEX;
	case ORM_UNIQUE;
	case ORM_SPATIAL;
	case ORM_FULLTEXT;
	
	public static function cast(string $value): OrmIndexType
    {
        return match($value) {			
			'index' => OrmIndexType::ORM_INDEX,
			'unique' => OrmIndexType::ORM_UNIQUE,
			'spatial' => OrmIndexType::ORM_SPATIAL,
			'fulltext' => OrmIndexType::ORM_FULLTEXT,
        };
    }
}