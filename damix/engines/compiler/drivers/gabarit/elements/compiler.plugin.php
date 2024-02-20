<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class GabaritElementCompiler
    extends \damix\engines\compiler\PluginElementDriver
{
    protected $_autoClose = false;
    
    public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \DOMNode $child ) : void
    {
        if( $child->hasAttribute( 'compilerjs' ) )
        {
            $compilerjs = tobool( $child->getAttribute( 'compilerjs' ) );
        }
        else
        {
            $compilerjs = true;
        }
        
        $driver->content->addProperty( 'compilerjs', $compilerjs ? 'true' : 'false', 'bool', false, 'private' );
        
    }
    
    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node ) : void {}
    
    public function afterRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node) : void {}
    
    public function write( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void {}
   
    public function afterWrite( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void {}
    
    public function afterwriteAttribute( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void {}
    
}