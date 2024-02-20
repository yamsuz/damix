<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class GabaritElementXdatailtre
    extends \damix\engines\compiler\GabaritElementAll
{
    public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \DOMNode $child ) : void
    {
		$selector =  $child->getAttribute( 'selector' );
		
		$node->name = 'div';
        
        $obj = \damix\engines\datatables\Datatable::get( $selector );
        
        $node->text = $obj->getFilters();
        
        $node->removeAttributes( 'locale' );
    }
    
}