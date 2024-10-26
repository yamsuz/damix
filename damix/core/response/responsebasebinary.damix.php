<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\response;

class ResponseBaseBinary
	extends ResponseBase
{
	
	public string $mimeType;
	public bool $doDownload = false;
	public string $outputFileName;
	public string $fileName;
	public string $content;
	
	public function __construct()
    {
		parent::__construct();
    }
	
	public function output() : void
	{
		
		if( empty( $this->outputFileName ) && ! empty( $this->fileName) ){
			$part = pathinfo( $this->fileName );
			$this->outputFileName = $part['basename'];
		}
		
		$this->setMimetype();
		$this->setFilesize();
		
		$this->addHttpHeader('Content-Type', $this->mimeType, $this->doDownload);

		if($this->doDownload)
			$this->downloadHeader();
		else
			$this->addHttpHeader('Content-Disposition','inline; filename="'.str_replace('"','\"',$this->outputFileName).'"',false);
		
		$this->sendHttpHeaders();
		
		session_write_close();
		echo($this->content);
		flush();
	}
	
	private function setMimetype() : void
	{
		if( empty( $this->mimeType ) )
		{
			$mime = mime_content_type($this->fileName);
			if( $mime === false )
			{
				$mime = 'application/octet-stream';
			}
			$this->mimeType = $mime;
		}
	}
	
	private function setFilesize() : void
	{
		$this->addHttpHeader('Content-Length', strval(strlen($this->content) ));
	}
	
	protected function downloadHeader() : void
	{
		$this->addHttpHeader('Content-Disposition','attachment; filename="'.str_replace('"','\"',$this->outputFileName).'"',false);
		$this->addHttpHeader('Content-Description','File Transfert',false);
		$this->addHttpHeader('Content-Transfer-Encoding','binary',false);
		$this->addHttpHeader('Pragma','public',false);
		$this->addHttpHeader('Cache-Control','maxage=3600',false);
	}
}