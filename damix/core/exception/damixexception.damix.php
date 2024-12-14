<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

declare(strict_types=1);
namespace damix\core\exception;

abstract class DamixException
	extends \Exception
{
	public function __construct(string $message = "", int $code = 0)
	{
		
		if( \damix\engines\locales\Locale::isLocale( $message ) )
		{
			$message = \damix\engines\locales\Locale::get( $message );
		}
		parent::__construct($message, $code);
	}
}