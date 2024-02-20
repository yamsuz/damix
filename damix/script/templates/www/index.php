<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

require('../application.init.php');

$coordinator = new \damix\core\Coordinator();

$coordinator->setRequest(new \damix\core\request\ClassicRequest);
$coordinator->process();