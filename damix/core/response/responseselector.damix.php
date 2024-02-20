<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\response;


class ResponseSelector
    extends \damix\core\Selector
{
    protected string $_extension = '.php';
    protected string $_directoryname = 'responses';
    public string $_suffix = 'damix';
    public string $_namespace = '\\damix\\apps\\response\\';
    public bool $generic = false;
    
    public function __construct( $selector )
    {
        parent::__construct( $selector );
		
		$this->_folder = \damix\application::getPathApp();
    }
	
	public function getFileDefault() : string
    {
		$dir = implode(DIRECTORY_SEPARATOR, $this->_part);
		
		$filename =  $this->_folder . $this->_directoryname . DIRECTORY_SEPARATOR . strtolower($dir) . '.' . $this->_suffix . $this->_extension;
	
        return $filename;
    }
	
    public function getClassName() : string
    {
        $classname = '';
		
		foreach( $this->_part as $part )
		{
			$classname .= ucfirst($part);
		}
        
        return $this->_namespace . $this->_classnameprefix . $classname . $this->_classnamesuffix;
    }
}