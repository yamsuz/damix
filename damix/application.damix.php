<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix;

class application
{
	private static string $pathTemp;
	private static string $pathApp;
	private static string $pathConfig;
	private static string $pathCore;
	private static string $nameApp;
	
	public static function init( $nameApp, $pathCore, $pathApp, $pathTemp, $pathConfig)
	{
		self::$nameApp = $nameApp;
		self::$pathCore = $pathCore;
		self::$pathApp = $pathApp;
		self::$pathTemp = $pathTemp;
		self::$pathConfig = $pathConfig;
		
		require_once( \damix\application::getPathCore() . DIRECTORY_SEPARATOR . 'includes.php' );
	}
		
	public static function getPathApp()
	{
		return self::$pathApp;
	}
	
	public static function getPathTemp()
	{
		return self::$pathTemp;
	}
	
	public static function getNameApp()
	{
		return self::$nameApp;
	}
	
	public static function getPathCore()
	{
		return self::$pathCore;
	}
	
	public static function getPathConfig()
	{
		return self::$pathConfig;
	}
	
	public static function getPathWww()
	{
		return self::$pathApp . 'www' . DIRECTORY_SEPARATOR;
	}
}