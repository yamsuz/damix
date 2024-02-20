<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request;


enum OrmPropertyType
{
	case ORM_TYPE_FIELD;
	case ORM_TYPE_LOGIC;
	case ORM_TYPE_GROUPEND;
	case ORM_TYPE_GROUPBEGIN;
	case ORM_TYPE_FORMULA;
   
}