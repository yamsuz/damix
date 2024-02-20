<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

declare(strict_types=1);
namespace damix\core\exception;

class OrmException
	extends DamixException
{
	public function __construct(string $message = "")
	{
		parent::__construct($message);
	}
}