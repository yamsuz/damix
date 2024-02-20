<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

declare(strict_types=1);
namespace damix\core\exception;

class CoreException
	extends DamixException
{
	public function __construct(string $message = "", array $params = array())
	{
		parent::__construct($message);
	}
}