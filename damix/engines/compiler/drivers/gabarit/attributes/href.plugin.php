<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\attributes;

class GabaritAttributeHref
    extends GabaritAttributeAll
{    
    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $nodes, \damix\engines\compiler\CompilerContentAttribute $attribute ) : bool
    {
        
        $href = $attribute->value;
        
		$val = \damix\core\urls\Url::getPath( $href );        
        
        $attribute->value = $val;
        
        return true;
    }
    
}