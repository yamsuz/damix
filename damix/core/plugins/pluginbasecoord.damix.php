<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\plugins;

abstract class PluginBaseCoord
	extends \damix\core\plugins\PluginBase
{
	public function beforeProcess(\damix\core\Coordinator $coord) : void{}
	public function beforeAction(\damix\core\Coordinator $coord) : void{}
	public function beforeOutput(\damix\core\Coordinator $coord) : void{}
	public function afterProcess(\damix\core\Coordinator $coord) : void{}
}