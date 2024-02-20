<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


enum OrmTriggerAction
{
    case ORM_INSERT;
    case ORM_UPDATE;
    case ORM_DELETE;
}