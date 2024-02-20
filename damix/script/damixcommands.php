<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class DamixCommands
{
	protected string $directory;
	protected string $dirtemplate;
	
	public function __construct()
	{
		$this->directory = __DIR__ . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR;
		$this->dirtemplate = __DIR__ . DIRECTORY_SEPARATOR . 'templates';
	}
	
	public static function run(array $params) : void
	{
		$application = $params['a'] ?? null;
		$execute = $params['e'] ?? null;
		
		
		$pathapp = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . $application;
		$damixapp = __DIR__ . DIRECTORY_SEPARATOR . '..' ;

		if( ! is_dir( $pathapp )  && $execute != 'appcreate' )
		{
			self::display( 'L\'application n\'existe pas.' );
			return; 
		}
		
		\damix\application::init(
				nameApp : $application, 
				pathConfig : $pathapp . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR, 
				pathCore : $damixapp . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR, 
				pathApp : $pathapp . DIRECTORY_SEPARATOR, 
				pathTemp : $pathapp . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR
				);

		if( self::load( $execute ) )
		{
			self::execcommande( $execute, $params);
		}
	}
	
	
	public static function load(string $execute) : bool
	{
		$filename = __DIR__ . '/commands/'. strtolower($execute) . '.php';		
		if( file_exists( $filename ) )
		{
			include_once( $filename );
			
			return true;
		}
		
		return false;
	}
	
	public static function execcommande(string $execute, array $params) : void
	{
		$classname = 'Command' . ucfirst( strtolower( $execute ) );
		if( class_exists( $classname ) )
		{
			$obj = new $classname();
			$obj->execute( $params );
		}
	}
	
	
	
	
	protected static function display(string $message) : void
	{
		print $message . "\r\n";
	}
	
}