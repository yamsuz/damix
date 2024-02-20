<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler;

class PluginAttributeDriver
{
    public function beforeRead( CompilerDriver $driver, CompilerContentElement $nodes, CompilerContentAttribute $attribute ) : bool{return true;}
    
    public function read( CompilerDriver $driver, CompilerContentElement $nodes, CompilerContentAttribute $attribute ) : bool{return true;}
    
    public function afterRead( CompilerDriver $driver, CompilerContentElement $nodes, CompilerContentAttribute $attribute ) : bool{return true;}
    
    public function write( CompilerDriver $driver, CompilerContentElement $nodes, CompilerContentAttribute $attribute, object $obj ) : bool{return true;}
    
    public function afterWrite( CompilerDriver $driver, CompilerContentElement $nodes, CompilerContentAttribute $attribute, object $obj ) : bool{return true;}
}