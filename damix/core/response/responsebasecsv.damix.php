<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\response;

class ResponseBaseCsv
	extends ResponseBaseBinary
{
	public array $data;
	public string $separator = ",";
    public string $enclosure = "\"";
    public string $escape = "\\";
    public string $eol = "\n";
    public bool $bom = false;
	
	public function __construct()
    {
		parent::__construct();
    }
	
	public function output() : void
	{
		$this->generateCsv();
		$this->doDownload = true;
		$this->mimeType = 'text/csv';
		
		parent::output();
		
		@unlink( $this->fileName );
	}
	
	public function setExcelCsv() : void
	{
		$this->separator = ";";
		$this->enclosure = "\"";
		$this->escape = "";
		$this->eol = "\n";
	}
	
	private function generateCsv() : void
	{
		$this->fileName = \damix\application::getPathTemp() . uniqid('csv', true) . '.csv';
		
		$fp = fopen($this->fileName, 'w');
		if( $this->bom )
		{
			fputs($fp, ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		}
		
		foreach ($this->data as $fields) {
			fputcsv($fp, (array)$fields, $this->separator, $this->enclosure, $this->escape, $this->eol);
		}

		fclose($fp);
	}
}