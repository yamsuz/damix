<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class gabaritelementtable
    extends \damix\engines\compiler\GabaritElementAll
{
    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node ) : void
    {
        $node->name = 'table';
		        
        parent::read( $driver, $node );
    }
}