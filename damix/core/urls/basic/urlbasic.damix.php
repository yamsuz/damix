<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\urls\basic;


class UrlBasic
	extends \damix\core\urls\UrlBase
{
   public function parse(?\damix\core\request\RequestGeneric $request)
   {
		$module = $this->selector->getPart( 'module' );
		$resource = $this->selector->getPart( 'resource' );
		if( $module !== null ) 
		{
			$this->module = $module;
		}
		elseif( $request !== null )
		{
			$this->module = $request->getParamString( 'module', '' );
		}
		
		if( $resource != null )
		{
			$this->action = $resource . ':' . $this->selector->getPart( 'function' );
		}
		elseif( $request !== null )
		{
			$this->action = $request->getParamString( 'action', '' );
		}
   }
   
   public function getPath() : string
	{
		$basepath = $this->getBasePath(). '/' . $this->module . '/' . $this->action;
		
		return $basepath;
	}
}