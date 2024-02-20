<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class GabaritElementEnum
    extends \damix\engines\compiler\drivers\gabarit\elements\GabaritElementSelect2
{
    protected $_autoClose = false;

    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node ) : void
    {
		$enumerate = $node->getAttrValue( 'enumerate' );
		$locale = $node->getAttrValue( 'locale' );
		if( ! empty( $this->ormStructure ) )
		{
			$orm = $this->ormStructure;
			$table = $orm->name;
			$field = $this->ormField;

			if( $field )
			{
				if( empty( $name ) )
				{
					$params['name'] = $field['name'];
				}
				
				$locale = $field['locale'];
				$enumerate = $field['enumerate'];
			}
		
		}
		
		$enum = preg_split( '/;/', $enumerate );
			
		$values = array('');
		
		foreach( $enum as $info )
		{
			$values[$info] = (empty($locale) ? $info : \damix\engines\locales\Locale::getEnum( $locale, $info ));
		}
		
		$html = '<option></option>';
		foreach( $values as $key => $val)
		{
			$html .= '<option value="'. $key . '">' . $val . '</option>';
		}
		$node->name = 'select';
		$node->text = $html;
		$driver->content->appendFunction( 'propertyinit', array(), array( '\damix\engines\orm\combo\OrmCombo::addJSLink();' ), 'public');

        parent::read($driver, $node);
    }

}