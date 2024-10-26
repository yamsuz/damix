<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class GabaritElementSelect2
    extends \damix\engines\compiler\GabaritElementAll
{
    protected $_autoClose = true;


    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node ) : void
    {
      
		$driver->content->appendFunction( 'propertyinit', array(), array( '\damix\engines\orm\combo\OrmCombo::addJSLink();' ), 'public');

        parent::read($driver, $node);
    }

	public function write( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void
    {
        if( $node->name == '' )
        {
            return;
        }
		
		$selector = $node->getAttrValue( 'selector' );
		$name = $node->getAttrValue( 'name' );
		
		$attrs = $node->getAttributes();
		
		$params = 'array(';
		foreach( $attrs as $name => $value )
		{
			$params .= '__QUOTE__'. $name .'__QUOTE__ => __QUOTE__' . $value->value . '__QUOTE__,';
		}
		$params .= ')';
		
        $zone = \damix\engines\settings\Setting::getValue('default', 'formcontrols', 'zonecontrolselect');
        // $driver->content->addData( '_html', '__QUOTE__ . \damix\engines\orm\combo\OrmCombo::getHtml( __QUOTE__' . $selector . '__QUOTE__, __QUOTE__' . $name . '__QUOTE__, ) . __QUOTE__');
        $driver->content->addData( '_html', '__QUOTE__ . \damix\engines\zones\Zone::get( __QUOTE__' . $zone . '__QUOTE__, '.$params.') . __QUOTE__');
		
		$node->clearAttributes();
    }
	
	public function afterwriteAttribute( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj ) : void
    {
       
    }
  
}