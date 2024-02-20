<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\locales;


class Locale
{
    static public $_singleton=array();
    
    public static function get( string $selector, array $params = array() ) : ?string
    {
		$langue = \damix\engines\settings\Setting::getValue('default', 'general', 'langue');
		
        $sel = new LocaleSelector( $selector );
		$uniq = $sel->getUniqueSelector();

		if(! isset(self::$_singleton[$uniq])){
			self::$_singleton[$uniq] = self::create( $sel, $params );
		}
		$obj = self::$_singleton[$uniq];
		
		return $obj->get( $sel->getPart( 'key' ), $params );
    }

    public static function create( LocaleSelector $selector, array $params ) : LocaleBase
    {
        $classname = '\\' . $selector->getFullNamespace();
        
		if( ! class_exists($classname,false ) )
        {
			if( LocaleCompiler::compile( $selector ) )
            {
                $temp = $selector->getTempPath();
                require_once( $temp );
            }
			else
			{
				throw new \damix\core\exception\CoreException( 'damix~lclerrors.core.locale.compilation.invalid', array( $selector->_selector ) );
			}
		}
        
        $obj = new $classname();
        return $obj;
    }
	
	public static function getEnum( string $selector, string $enum, array $params = array() ) : ?string
	{
		return self::get( $selector . '.' . $enum, $params);
	}
}