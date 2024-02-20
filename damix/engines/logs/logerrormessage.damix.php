<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\logs;


class LogErrorMessage
	extends LogMessage
{
	protected string $category;
	protected string $file;
	protected int $line;
	protected array $trace;
	protected int $code;
	protected string $format='%date%\t%ip%\t[%code%]\t%msg%\t%file%\t%line%\n\t%url%\n%params%\n%trace%';
	
	public function __construct(string $category, int $code, string $message, string $file, int $line, array $trace)
	{
		$this->category=$category;
		$this->message=$message;
		$this->code=$code;
		$this->file=$file;
		$this->line=$line;
		$this->trace=$trace;
	}
	
	public function setFormat(string $format) : void
	{
		$this->format=$format;
	}
	public function getCode() : int
	{
		return $this->code;
	}
	public function getCategory() : string
	{
		return $this->category;
	}
	public function getMessage() : string
	{
		return $this->message;
	}
	public function getFile() : string
	{
		return $this->file;
	}
	public function getLine() : int
	{
		return $this->line;
	}
	public function getTrace() : array
	{
		return $this->trace;
	}
	public function getFormated() : string
	{
		if(isset($_SERVER['REQUEST_URI']))
			$url=$_SERVER['REQUEST_URI'];
		elseif(isset($_SERVER['SCRIPT_NAME']))
			$url=$_SERVER['SCRIPT_NAME'];
		else
			$url='Unknow request';
		// if(jApp::coord()&&($req=jApp::coord()->request)){
			// $params=$this->sanitizeParams($req->params);
			// $remoteAddr=$req->getIP();
		// }
		// else{
			$params=$this->sanitizeParams(isset($_GET)?$_GET:array());
			$remoteAddr=isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
		// }
		$traceLog="";
		foreach($this->trace as $k=>$t){
			$traceLog.="\n\t$k\t".(isset($t['class'])?$t['class'].$t['type']:'').$t['function']."()\t";
			$traceLog.=(isset($t['file'])?$t['file']:'[php]').' : '.(isset($t['line'])?$t['line']:'');
		}
		$httpReferer=isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER'] : 'Unknown referer';
		$messageLog=strtr($this->format,array(
			'%date%'=>@date("Y-m-d H:i:s"),
			'%typeerror%'=>$this->category,
			'%code%'=>$this->code,
			'%msg%'=>$this->message,
			'%ip%'=>$remoteAddr,
			'%url%'=>$url,
			'%referer%'=>$httpReferer,
			'%params%'=>$params,
			'%file%'=>$this->file,
			'%line%'=>$this->line,
			'%trace%'=>$traceLog,
			'%http_method%'=>isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'Unknown method',
			'\t'=>"\t",
			'\n'=>"\n"
		));
		return $messageLog;
	}
	protected function sanitizeParams(array $params) : string
	{
		$sensitiveParameters = array( 'password', 'passwd', 'pwd');
		foreach($sensitiveParameters as $param){
			if($param!=''&&isset($params[$param])){
				$params[$param]='***';
			}
		}
		return str_replace("\n",' ',var_export($params,true));
	}
}