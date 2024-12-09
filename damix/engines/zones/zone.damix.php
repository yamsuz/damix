<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\zones;

class Zone
{
	static protected $_singleton=array();
    
    public static function get( string $selector, array $params = array() ) : string
    {
        $sel = new ZoneSelector( $selector );

		$hash = self::getHashZone( $params );
		
		if(! isset(self::$_singleton[$selector][$hash])){
			self::$_singleton[$selector][$hash] = self::create( $sel );
			self::$_singleton[$selector][$hash]->params = $params;
		}
		return self::$_singleton[$selector][$hash]->getContent();
    }

    public static function create( ZoneSelector $selector ) : ZoneBase
    {
		$filename = $selector->getPath();
		
        $classname = $selector->getFullNamespace();
		
		if( file_exists( $filename ) )
		{
			require_once( $filename );
		}
		else
		{
			throw new \damix\core\exception\CoreException('Le fichier de la zone n\'existe pas ' . $filename);
		}
        
		// if( ! class_exists($classname,false ) )
        // {
			// if( ZoneCompiler::compile( $selector ) )
            // {
                // $temp = $selector->getTempPath();
                // require_once( $temp );
            // }
			// else
			// {
				// throw new \Exception();
			// }
		// }

        $obj = new $classname();
        return $obj;
    }
	
	protected static function getHashZone( array $params )
	{
		$ar=$params;
		ksort($ar);
		$id=md5(serialize($ar));
		return $id;
	}
}