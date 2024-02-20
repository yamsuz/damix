<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class GabaritElementBr
    extends \damix\engines\compiler\GabaritElementAll
{

    public function write(  \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void
    {        
        $driver->content->addData( '_html', '<br/>' );
    }
       
    public function afterwriteAttribute( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void
    {
    }
    
    public function afterWrite( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void
    {
    }    
}