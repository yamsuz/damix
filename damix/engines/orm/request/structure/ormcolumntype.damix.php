<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


enum OrmColumnType
{
    case COLUMN_FIELD;
    case COLUMN_COMMA;
    case COLUMN_RAW;
    case COLUMN_VALUE;
    case COLUMN_OPERATOR;
    case COLUMN_FORMULA;
}