<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler;

class PluginFunctionDriver
{
    public CompilerDriver $parentDriver;
	
	public function execute( string $src ) : string {}
}