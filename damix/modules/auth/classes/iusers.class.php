<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

namespace damix\auth;

interface  iUsers
{
	public function setup() : void;
	public function loadUser(string $login) : bool;	
	public function getPassword() : string;
}