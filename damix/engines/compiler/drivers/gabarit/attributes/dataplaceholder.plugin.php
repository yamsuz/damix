<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\attributes;

class gabaritattributedataplaceholder
    extends GabaritAttributeAll
{    
    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \damix\engines\compiler\CompilerContentAttribute $attribute ) : bool
    {
        $locale = $this->getLocale( $attribute->value );
        
        $attribute->name = 'data-placeholder';
        $attribute->value = $locale;
        
        return true;
    }
    
}