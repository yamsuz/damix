<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

namespace damix\damix;

class forms
	extends \damix\core\controllers\Controller
{    
    
    public function load() 
    {
        $rep = $this->getResponse( 'jhjc' );
        
        $out = array();
        
        $selector = $this->request->getParamString( 's' );
       
		 
        $gabarit = \damix\engines\views\gabarits\Gabarit::get( $selector );
        $gabarit->createJSFile();
        
        $out[ 'html' ] = $gabarit->getHtml();
        
        $rep->data = $out;
        return $rep;
    }
	
	public function selectdata()
	{
		$rep = $this->getResponse('json');

		$out = array();
		
		$selector = $this->getParamString('selector');
		$query = $this->getParamString('q');
		$page = $this->getParamInt('page', 1);
		
		$max = 30;
		
		$combo = \damix\engines\orm\combo\OrmCombo::get( $selector );
		$displays = $combo->getDisplays();
		$c = $combo->getConditions();
		
		foreach( $displays as $prop )
		{
			$this->addCondition($c, $prop, $query);
			$c->addLogic(\damix\engines\orm\conditions\OrmOperator::ORM_OP_OR );
		}
		
		$this->addCondition($c, $combo->getValue(), $query);
		
		$params = $combo->getInnerConditionsArray($max, ( $page - 1 ) * $max);
		
		$nombre = count($params);
		$out = array(
			"total_count" => ($nombre >= $max ? $page * $max + 1 : $page * $max),
			"more" => $nombre >= $max,
			"items" => $params
		);
		
		
		$rep->data = $out;

		return $rep;
	}
	
	private function addCondition(\damix\engines\orm\conditions\OrmCondition $condition, string $name, mixed $value)
	{
		$ref = \damix\engines\orm\Orm::getDefine($name);
		if( $ref )
		{
			// \damix\engines\logs\log::dump( $ref['field']['datatype'] );
			
			switch( \damix\engines\orm\request\structure\OrmDataType::cast( $ref['field']['datatype'] ) )
			{
				case \damix\engines\orm\request\structure\OrmDataType::ORM_CHAR:
				case \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR:
				case \damix\engines\orm\request\structure\OrmDataType::ORM_TEXT:
				case \damix\engines\orm\request\structure\OrmDataType::ORM_LONGTEXT:
					$condition->addString( $name, \damix\engines\orm\conditions\OrmOperator::ORM_OP_LIKE, $value);
					break;
				case \damix\engines\orm\request\structure\OrmDataType::ORM_INT:
				case \damix\engines\orm\request\structure\OrmDataType::ORM_SMALLINT:
				case \damix\engines\orm\request\structure\OrmDataType::ORM_TINYINT:
				case \damix\engines\orm\request\structure\OrmDataType::ORM_BIGINT:
					$condition->addInt( $name, \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, intval($value));
					break;
			}
		}
		
	}
    
}

