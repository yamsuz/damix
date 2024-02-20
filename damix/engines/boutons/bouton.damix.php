<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\boutons;


class Bouton
{
    static protected $_singleton=array();
    
    public static function get( string $selector ) : ?BoutonBase
    {
        $sel = new BoutonSelector( $selector );

		if(! isset(Bouton::$_singleton[$selector])){
			Bouton::$_singleton[$selector] = Bouton::create( $sel );
		}
		return Bouton::$_singleton[$selector];
    }

    public static function create( BoutonSelector $selector ) : ?BoutonBase
    {
        $classname = $selector->getFullNamespace();
		if( ! class_exists($classname,false ) )
        {
			if( BoutonCompiler::compile( $selector ) )
            {
                $temp = $selector->getTempPath();
				if( file_exists( $temp ) )
				{
					require_once( $temp );
				}
				else
				{
					throw new \damix\core\exception\CoreException("the file ". $temp . " doesn't exist");
				}
            }
			else
            {
				throw new \damix\core\exception\CoreException("There's an error on compilation of the button");
            }
		}
        
        $obj = new $classname();
        return $obj;
    }
}