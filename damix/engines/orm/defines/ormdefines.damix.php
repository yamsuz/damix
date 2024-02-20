<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\orm\defines;


class OrmDefines
{
    static protected ?OrmDefinesBase $_singleton = null;
    
    public static function get() : OrmDefinesBase
    {
        $sel = new OrmDefinesSelector();

		if(! isset(self::$_singleton)){
			self::$_singleton = self::create( $sel );
		}
		return self::$_singleton;
    }

    public static function create( OrmDefinesSelector $selector ) : OrmDefinesBase
    {
        $classname = $selector->getFullNamespace();
        
		if( ! class_exists($classname,false ) )
        {
			if( OrmDefinesCompiler::compile( $selector ) )
            {
                $temp = $selector->getTempPath();
                require_once( $temp );
            }
			else
			{
				throw new \Exception();
			}
		}
        $obj = new $classname();
        return $obj;
    }
      
}