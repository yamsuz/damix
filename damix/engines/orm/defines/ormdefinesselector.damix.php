<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\defines;


class OrmDefinesSelector
    extends \damix\core\Selector
{
    protected array $_partselector = array( );
    protected string $_extension = '.xml';
    protected string $_extensiontemp = '.php';
    public string $_classnameprefix = '';
    public array $files = array();
    public array $completion = array();
    
    public function __construct()
    {
        parent::__construct( '' );

		$this->_folder = \damix\application::getPathConfig() . 'ormdefines';
	}

	public function getFileDefault() : string
    {
        $filename = $this->_folder . DIRECTORY_SEPARATOR . $this->_prefix . 'ormdefines' . $this->_suffix . $this->_extension;

        return $filename;
    }
	
	public function getClassName() : string
    {
        return $this->_classnameprefix . 'cxOrmDefines' . $this->_classnamesuffix;
    }
	
	public function getNamespace() : string
    {
		return 'orm\\defines';
    }
	
	public function getTempPath() : string
    {
		return $this->_directorytemp . 'define' . DIRECTORY_SEPARATOR . $this->getClassName() . $this->_extensiontemp;
    }
}