<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


enum OrmContraintType: string
{
    case ORM_CASCADE = 'cascade';
    case ORM_SETNULL = 'setnull';
    case ORM_NOACTION = 'noaction';
    case ORM_RESTRICT = 'restrict';
	
	
	public static function cast(string $value): OrmContraintType
    {
		$out = self::tryFrom( $value );
		
        if( $out === null )
		{
			$out = OrmContraintType::ORM_NOACTION;
		}
		return $out;
    }
	
	public function toString() : string
	{
		return $this->value;
	}
}