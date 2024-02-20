<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\events;


class Event
{
    static protected ?EventBase $_singleton = null;
    
    public static function get( string $selector ) : EventBase
    {
        $sel = new EventSelector();

		if( ! self::$_singleton ){
			self::$_singleton = self::create( $sel );
		}
		return self::$_singleton;
    }

    public static function create( EventSelector $selector ) : EventBase
    {
        $classname = $selector->getFullNamespace();
        
		if( ! class_exists($classname,false ) )
        {
			if( EventCompiler::compile( $selector ) )
            {
                $temp = $selector->getTempPath();
                require_once( $temp );
            }
			else
			{
				throw new \damix\core\exception\CoreException();
			}
		}
        
        $obj = new $classname();
        return $obj;
    }
	
	public static function notify( string $event, array $params = array() ) : array
	{
		$result = array();
		
		$obj = self::get( $event );
		if( $obj )
		{
			$tab = $obj->getSelectorClasse( $event );
			foreach( $tab as $selector )
			{
				$obj = \damix\core\classes\Classe::get( $selector );
				if( $obj )
				{
					$evt = new \stdClass();
					$evt->result = $obj->performEvent( $event, $params );
					$evt->selector = $selector;
					$result[] = $evt;
				}
			}
		}
		return $result;
	}
}