<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\zones;

class ZoneSelector
	extends \damix\core\Selector
{
	protected array $_partselector = array( 'module', 'resource' );
	protected bool $core = false;
	public string $_classnamesuffix = 'Zone';
	
	public function __construct( string $selector, $params = array() )
    {
		parent::__construct( $selector, $params );
		
        $this->_directorytemp .= 'zones' . DIRECTORY_SEPARATOR;
		
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
	
	public function getNamespace() : string
    {
		if( $this->core )
		{
			$namespace = '\\damix';
		}
		else
		{
			$namespace = \damix\application::getNameApp();
		}
		
		$namespace .= '\\' . $this->getPart('module');
		
		
        return $namespace;
    }
	
	public function getClassName() : string
	{
		return strtolower( parent::getClassName() );
	}

	public function getPath() : string
    {
        $filename = $this->_folder;
  
		$filename .= DIRECTORY_SEPARATOR . strtolower($this->_part['module']);
		$filename .= DIRECTORY_SEPARATOR . 'zones' . DIRECTORY_SEPARATOR;
		$filename .= $this->_prefix . strtolower($this->_part['resource']) . $this->_suffix . '.' . $this->_extension;
        
        return $filename;
        
    }
}