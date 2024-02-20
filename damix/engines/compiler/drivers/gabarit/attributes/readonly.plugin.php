<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\attributes;

class gabaritattributereadonly
    extends GabaritAttributeAll
{    
    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \damix\engines\compiler\CompilerContentAttribute $attribute ) : bool
    {
        $content = array();
        
        if( tobool( $attribute->value ) )
        {
            $attribute->value = 'readonly';
        }
        else
        {
            $node->removeAttributes( $attribute->name );
        }
        
        return true;
    }
    
}