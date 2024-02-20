<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\settings;


class SettingSelector
    extends \damix\core\Selector
{
    protected array $_partselector = array( 'name' );
    protected string $_extension = 'cfg.xml';
    protected string $_extensiontemp = '.php';
    protected string $_directoryname = 'config';
    public string $_classnameprefix = 'cfg';
    public array $files = array();
    public array $completion = array();
    public bool $usercompletion = false;
	
    public function __construct( $selector )
    {
        parent::__construct( $selector );
		$this->_folder = \damix\application::getPathApp();
	}

	public function getFileDefault() : string
    {
        $dir = implode('_', $this->_part);
        
        $filename = $this->_folder . 'var' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $this->_prefix . $dir . $this->_suffix . '.' . $this->_extension;

        return $filename;
    }
		
	public function getTempPath() : string
    {       
        $filename = $this->_directorytemp . 'config' . DIRECTORY_SEPARATOR . $this->_prefix . $this->getPart('name') . $this->_suffix . $this->_extensiontemp;
		
		return $filename;
    }
	
	public function getNamespace() : string
    {
		return 'damix\engines\settings';
    }
}