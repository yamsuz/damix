<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

namespace damix\damix;

class zoncontrolstringZone
	extends \damix\engines\zones\ZoneBase
{
	protected string $tplSelector = 'damix~tplcontrolstring';

	
	protected function prepareTpl() : void 
	{
		$name = $this->getParam('name');
		$value1 = $this->getParam('value1');
		$locale = $this->getParam('locale');
		$operator = $this->getParam('operator');
		$ref = $this->getParam('ref');
		$params = $this->getParams();

		if( ! empty( $ref ) )
		{
			if( $struct = \damix\engines\orm\Orm::getDefine( $ref ))
			{
				$orm = $struct['orm'];
				$table = $struct['orm']->name;
				$field = $struct['field'];

				if( $field )
				{
					if( empty( $name ) )
					{
						$params['name'] = $field['name'];
					}
					
					// $params['locale'] = $field['locale'];
				}
			}
		}
		
		
		unset( $params['type'] );
		unset( $params['operator'] );
		unset( $params['locale'] );
		unset( $params['value1'] );
		unset( $params['multiple'] );
		unset( $params['datatype'] );
		unset( $params['selector'] );
		unset( $params['null'] );
		unset( $params['enumerate'] );
		unset( $params['ref'] );
			
		$this->Tpl->assignParameter( 'operator', $operator );
		$this->Tpl->assignParameter( 'value1', $value1 );
		$this->Tpl->assignParameter( 'params', $params );
	}
}