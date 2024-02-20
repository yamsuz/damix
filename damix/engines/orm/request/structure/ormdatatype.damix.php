<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


enum OrmDataType: string
{
	case ORM_BOOL = 'bool';
	case ORM_BIT = 'bit';
	case ORM_TINYINT = 'tinyint';
	case ORM_SMALLINT = 'smallint';
	case ORM_INT = 'int';
	case ORM_BIGINT =  'bigint';
	case ORM_DECIMAL = 'decimal';
	case ORM_DOUBLE = 'double';
	case ORM_NUMERIC = 'numeric';
	case ORM_FLOAT = 'float';
	case ORM_REAL = 'real';
	
	case ORM_DATE = 'date';
	case ORM_TIME = 'time';
	case ORM_DATETIME = 'datetime';
	case ORM_TIMESTAMP = 'timestamp';
	
	case ORM_CHAR = 'char';
	case ORM_VARCHAR = 'varchar';
	case ORM_TEXT = 'text';
	case ORM_LONGTEXT = 'longtext';
	
	case ORM_BINARY = 'binary';
	case ORM_BLOB = 'blob';
	case ORM_LONGBLOB = 'longblob';
	
	case ORM_ENUM = 'enum';
	
	case ORM_JSON = 'json';
	case ORM_FORMULA = 'formula';
	case ORM_SQL = 'sql';
	case ORM_PHONE = 'phone';
	
	public static function cast(string $value): OrmDataType
    {
		$out = self::tryFrom( $value );
        if( $out === null )
		{
			throw new \Exception( 'Datatype not found ' . $value );
		}
		return $out;
    }
	
	public static function castToGenerate(string $value): string
    {
        return match($value) {			
			'string' => '\damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR',
			'varchar' => '\damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR',
			'phone' => '\damix\engines\orm\request\structure\OrmDataType::ORM_PHONE',
			'text' => '\damix\engines\orm\request\structure\OrmDataType::ORM_TEXT',
			'char' => '\damix\engines\orm\request\structure\OrmDataType::ORM_CHAR',
			'int' => '\damix\engines\orm\request\structure\OrmDataType::ORM_INT',
			'bigint' => '\damix\engines\orm\request\structure\OrmDataType::ORM_BIGINT',
			'float' => '\damix\engines\orm\request\structure\OrmDataType::ORM_FLOAT',
			'decimal' => '\damix\engines\orm\request\structure\OrmDataType::ORM_DECIMAL',
			'double' => '\damix\engines\orm\request\structure\OrmDataType::ORM_DOUBLE',
			'date' => '\damix\engines\orm\request\structure\OrmDataType::ORM_DATE',
			'time' => '\damix\engines\orm\request\structure\OrmDataType::ORM_TIME',
			'datetime' => '\damix\engines\orm\request\structure\OrmDataType::ORM_DATETIME',
			'bit' => '\damix\engines\orm\request\structure\OrmDataType::ORM_BIT',
			'enum' => '\damix\engines\orm\request\structure\OrmDataType::ORM_ENUM',
			'bool' => '\damix\engines\orm\request\structure\OrmDataType::ORM_BOOL',
			'json' => '\damix\engines\orm\request\structure\OrmDataType::ORM_JSON',
			'formula' => '\damix\engines\orm\request\structure\OrmDataType::ORM_FORMULA',
			'tinyint' => '\damix\engines\orm\request\structure\OrmDataType::ORM_TINYINT',
			'longblob' => '\damix\engines\orm\request\structure\OrmDataType::ORM_LONGBLOB',
			'sql' => '\damix\engines\orm\request\structure\OrmDataType::ORM_SQL',
			default => throw new \damix\core\exception\CoreException( 'Datatype not found : ' . $value ),
        };
    }
	
	public function toString() : string
	{
		return $this->value;
	}
}