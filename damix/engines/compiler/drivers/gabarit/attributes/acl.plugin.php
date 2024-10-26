<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\attributes;

class gabaritattributeacl
    extends GabaritAttributeAll
{    
	public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \damix\engines\compiler\CompilerContentAttribute $attribute ) : bool
    {
        $acl = $attribute->value ;
        
        $node->removeAttributes( $attribute->name );
        
        if( $acl != '' )
        {
            return \damix\engines\acls\Acl::check( $acl );
        }

        return true;
    }
      
}