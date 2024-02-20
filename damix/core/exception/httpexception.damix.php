<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

declare(strict_types=1);
namespace damix\core\exception;

class HttpException
	extends DamixException
{
	public function __construct(string $message = "", int $code = 0)
	{
		parent::__construct($message, $code);
	}
}