<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\template;

abstract class TemplateBaseGenerator
{
	public abstract function generate( \damix\engines\template\TemplateSelector $selector ) : bool;
}
