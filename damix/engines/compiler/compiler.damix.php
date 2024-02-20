<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/nce         
 */
declare(strict_types=1);
namespace damix\engines\compiler;

class Compiler
{
    public static function get( string $selector )
    {
        $xcompilercompiler = new CompilerCompiler();
        $obj = $xcompilercompiler->compile( $selector );
        
        return $obj;
    }
    
}