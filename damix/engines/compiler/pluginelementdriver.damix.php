<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler;

class PluginElementDriver
{
    public function beforeRead( CompilerDriver $driver, CompilerContentElement $nodes, \DOMNode $child ) : void {}
    
    public function read( CompilerDriver $driver, CompilerContentElement $nodes ) : void {}
    
    public function afterRead( CompilerDriver $driver, CompilerContentElement $nodes) : void {}
    
    public function write( CompilerDriver $driver, CompilerContentElement $nodes, object $obj ) : void {}
   
    public function afterWrite( CompilerDriver $driver, CompilerContentElement $nodes, object $obj ) : void {}
    
    public function afterwriteAttribute( CompilerDriver $driver, CompilerContentElement $nodes, object $obj ) : void {}
    
}