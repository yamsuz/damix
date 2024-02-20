<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\conditions;


enum OrmOperator
{
	case ORM_OP_EQ;
    case ORM_OP_NOTEQ;
    case ORM_OP_LTEQ;
    case ORM_OP_GTEQ;
    case ORM_OP_LT;
    case ORM_OP_GT;
    case ORM_OP_LIKE;
    case ORM_OP_NOTLIKE;
    case ORM_OP_ISNULL;
    case ORM_OP_LIKE_BEGIN;
    case ORM_OP_LIKE_END;
    case ORM_OP_IN;
    
    case ORM_OP_AND;
    case ORM_OP_OR;
	
	
	
	public static function cast(string $value): OrmOperator
    {
        return match($value) {			
			'eq' => OrmOperator::ORM_OP_EQ,
			'noteq' => OrmOperator::ORM_OP_NOTEQ,
			'lteq' => OrmOperator::ORM_OP_LTEQ,
			'gteq' => OrmOperator::ORM_OP_GTEQ,
			'lt' => OrmOperator::ORM_OP_LT,
			'gt' => OrmOperator::ORM_OP_GT,
			'like' => OrmOperator::ORM_OP_LIKE,
			'notlike' => OrmOperator::ORM_OP_NOTLIKE,
			'isnull' => OrmOperator::ORM_OP_ISNULL,
			'likebegin' => OrmOperator::ORM_OP_LIKE_BEGIN,
			'likeend' => OrmOperator::ORM_OP_LIKE_END,
			'in' => OrmOperator::ORM_OP_IN,
			
			'and' => OrmOperator::ORM_OP_AND,
			'or' => OrmOperator::ORM_OP_OR,
			default => OrmOperator::ORM_OP_EQ,
        };
    }
	public static function toString(OrmOperator $value): string
    {
        return match($value) {			
			OrmOperator::ORM_OP_EQ => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ',
			OrmOperator::ORM_OP_NOTEQ => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_NOTEQ',
			OrmOperator::ORM_OP_LTEQ => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_LTEQ',
			OrmOperator::ORM_OP_GTEQ => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_GTEQ',
			OrmOperator::ORM_OP_LT => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_LT',
			OrmOperator::ORM_OP_GT => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_GT',
			OrmOperator::ORM_OP_LIKE => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_LIKE',
			OrmOperator::ORM_OP_NOTLIKE => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_NOTLIKE',
			OrmOperator::ORM_OP_ISNULL => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_ISNULL',
			OrmOperator::ORM_OP_LIKE_BEGIN => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_LIKE_BEGIN',
			OrmOperator::ORM_OP_LIKE_END => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_LIKE_END',
			OrmOperator::ORM_OP_IN => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_IN',
			
			OrmOperator::ORM_OP_AND => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_AND',
			OrmOperator::ORM_OP_OR, => '\damix\engines\orm\conditions\OrmOperator::ORM_OP_OR',
        };
    }
}