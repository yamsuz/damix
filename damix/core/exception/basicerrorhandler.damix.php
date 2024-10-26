<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

declare(strict_types=1);
namespace damix\core\exception;

class BasicErrorHandler	
{
	static $errorCode=array(
		E_ERROR=>'error',
		E_RECOVERABLE_ERROR=>'error',
		E_WARNING=>'warning',
		E_NOTICE=>'notice',
		E_DEPRECATED=>'deprecated',
		E_USER_ERROR=>'error',
		E_USER_WARNING=>'warning',
		E_USER_NOTICE=>'notice',
		E_USER_DEPRECATED=>'deprecated',
		E_STRICT=>'strict'
	);
	
	static $errorsActive = array( 
		E_ERROR             => true,
		E_WARNING           => false,
		E_PARSE             => true,
		E_NOTICE            => true,
		E_CORE_ERROR        => true,
		E_CORE_WARNING      => true,
		E_COMPILE_ERROR     => true,
		E_COMPILE_WARNING   => true,
		E_USER_ERROR        => true,
		E_USER_WARNING      => true,
		E_USER_NOTICE       => true,
		E_STRICT            => true,
		E_RECOVERABLE_ERROR => true,
		E_DEPRECATED        => true,
		E_USER_DEPRECATED   => true,
		E_ALL               => false,
	);
	
	public static \damix\core\request\requestgeneric $request;
	
	public static function register(\damix\core\request\requestgeneric $request)
	{
		self::$request = $request;
		set_error_handler(array('\damix\core\exception\BasicErrorHandler', 'errorHandler'));
		set_exception_handler(array('\damix\core\exception\BasicErrorHandler', 'exceptionHandler'));
	}
	
	
	static function errorHandler(int $errno, string $errmsg, string $filename,int $linenum)
	{
		if(error_reporting(~E_ALL)==0)
			return;
		if(preg_match('/^\s*\((\d+)\)(.+)$/',$errmsg,$m)){
			$code=$m[1];
			$errmsg=$m[2];
		}
		else{
			$code=1;
		}
		if(!isset(self::$errorCode[$errno])){
			$errno=E_ERROR;
		}
		$codestr=self::$errorCode[$errno];
		$trace=debug_backtrace();
		array_shift($trace);
		self::handleError($codestr,$errno,$errmsg,$filename,$linenum,$trace);
	}
	
	static function exceptionHandler($e){
		self::handleError('error',$e->getCode(),$e->getMessage(),$e->getFile(), $e->getLine(),$e->getTrace());
	}
	
	private static function handleError(string $type, int $code, string $message, string $file, int $line, array $trace = array()) : void
	{
		
		// if( !self::$errorsActive[$code] )
		// {
			// return;
		// }
		$message = new \damix\engines\logs\LogErrorMessage($type,$code,$message,$file,$line,$trace);			
		\damix\engines\logs\log::dump($message, $type);
		
		if($type!='error')
			return;
		
		while(ob_get_level()&&@ob_end_clean());
		$resp=self::$request->getErrorResponse();
		$resp->outputErrors();
		\damix\engines\sessions\Session::end();
		
		exit(1);
	}
	
}