<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


enum OrmStructureType
{
	case SQL_CREATE;
	case SQL_ALTER;
	case SQL_DROP;
	case SQL_INSERT;
	case SQL_UPDATE;
	case SQL_DELETE;
	case SQL_SELECT;
	case SQL_PROCEDURE_STORED_CREATE;
	case SQL_PROCEDURE_STORED_DELETE;
	case SQL_FUNCTION_STORED_CREATE;
	case SQL_FUNCTION_STORED_DELETE;
	case SQL_EVENT_STORED_CREATE;
	case SQL_EVENT_STORED_DELETE;
	case SQL_TRIGGER_STORED_CREATE;
	case SQL_TRIGGER_STORED_DELETE;
}