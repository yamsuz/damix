<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\locales;


class LocaleBase
{
	protected array $_locale = array();
	
	public function __construct()
    {
    }
	
	public function get( string $key, array $args = array() ) : ?string
	{
		if( ! isset( $this->_locale[$key] ) )
		{
			$this->propertyinit();
		}
		
		if( ! isset( $this->_locale[$key] ) )
		{
			throw new \damix\core\exception\LocaleException('Locale not exists : ' . $key);
			return null;
		}
		
		$string = $this->_locale[$key];
		$string = call_user_func_array('sprintf', array_merge(array($string),$args));
	
		
		return $string;
	}
}