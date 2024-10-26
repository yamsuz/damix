<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\datatables;


class DatatableSelector
    extends \damix\core\Selector
{
    protected array $_partselector = array( 'module', 'resource', 'function' );
    protected string $_extension = 'dts.xml';
    protected string $_extensiontemp = '.php';
    protected string $_directoryname = 'datatable';
    protected bool $core = false;
    protected bool $classnameuser = true;
	protected string $classnameseparator = '_';
	
    public function __construct( $selector )
    {
        parent::__construct( $selector );
        
        $module = $this->getPart('module');
		$appdir = \damix\application::getPathApp() . 'modules' . DIRECTORY_SEPARATOR . $module;
		
		if( is_dir( $appdir ) )
		{
			$this->core = false;
			$this->_folder = \damix\application::getPathApp() . 'modules';
		}
		else
		{
			$this->core = true;
			$this->_folder = \damix\application::getPathCore() . '..' . DIRECTORY_SEPARATOR . 'modules';
		}
        $this->_dircompletion = \damix\application::getPathApp() . DIRECTORY_SEPARATOR . 'completion' . DIRECTORY_SEPARATOR . $this->_directoryname . DIRECTORY_SEPARATOR;
		$this->classnamebase = 'datatable' . $selector;

    }
   
    public function getFileDefault() : string
    {
        $dir = $this->getPart( 'module' ) . DIRECTORY_SEPARATOR . $this->_directoryname . DIRECTORY_SEPARATOR . $this->getPart( 'resource' ) . '_' . $this->getPart( 'function' );
        
        $filename = $this->_folder . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $this->_prefix . 'default' . $this->_suffix . '.' . $this->_extension;
        
        return $filename;
    }
	
	public function getTempPath() : string
    {
        $dir = strtolower( $this->_part['module'] . DIRECTORY_SEPARATOR . $this->_directoryname . DIRECTORY_SEPARATOR . $this->getPart( 'resource' ) . '_' . $this->getPart( 'function' ) );
      
        $login = \damix\engines\tools\xTools::login();
        
        $name = substr($login , 0, 5) . '_'. md5( $login . $this->getClassName() );
		
        return $this->_directorytemp . $dir . DIRECTORY_SEPARATOR . $name . $this->_extensiontemp;
    }
    
}