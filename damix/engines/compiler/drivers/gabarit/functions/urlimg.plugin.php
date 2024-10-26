<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\functions;

class GabaritFunctionUrlimg
    extends \damix\engines\compiler\PluginFunctionDriver
{

	public function execute( string $src ) : string
    {
        $basepath = \damix\core\urls\Url::getBasePath();

        return $basepath . $src;
    }
}