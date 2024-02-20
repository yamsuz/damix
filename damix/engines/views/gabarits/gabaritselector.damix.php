<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\views\gabarits;


class GabaritSelector
	extends \damix\core\Selector
{
    protected array $_partselector = array( 'module', 'resource' );
    protected string $_extension = 'gabarit.xml';
    protected string $_extensiontemp = '.php';
    protected string $_directoryname = 'gabarit';
    protected string $_separator = '/[~]/';
    protected bool $core = false;
    public bool $usercompletion = false;
    
    public function __construct( $selector, $params = array() )
    {
        parent::__construct( $selector, $params );
		
        $this->_directorytemp .= $this->_directoryname . DIRECTORY_SEPARATOR;
		
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
    }
   
    public function getFileDefault() : string
    {
		$part = $this->_part;
		
		unset($part['module']);
		
        $dir = $this->getPart( 'module' ) . DIRECTORY_SEPARATOR . $this->_directoryname . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, array_values($part) );
        
        $filename = $this->_folder . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $this->_prefix . 'default' . $this->_suffix . '.' . $this->_extension;
        
        return $filename;
    }
	
	public function getTempPath() : string
    {
        $dir = strtolower( $this->_part['module'] . DIRECTORY_SEPARATOR . $this->_directoryname . DIRECTORY_SEPARATOR . $this->getPart( 'resource' ) );
		$part = $this->_part;
		
		unset($part['module']);
        
        $name = implode( '_', array_values($part) ) . ( $this->usercompletion ? '_' . \damix\engines\tools\xTools::login() : '' );
		
        return $this->_directorytemp . $dir . DIRECTORY_SEPARATOR . $name . $this->_extensiontemp;
    }
}