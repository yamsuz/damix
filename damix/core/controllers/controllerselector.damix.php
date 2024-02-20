<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\controllers;

class ControllerSelector
	extends \damix\core\Selector
{
	protected array $_partselector = array( 'module', 'resource', 'function' );
	protected bool $core = false;
	
	public function __construct( string $selector, $params = array() )
    {
		parent::__construct( $selector, $params );
		
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
	
	public function getPart( $value ) : string | null
	{
		$part = parent::getPart( $value );
		if( $part === null )
		{
			$part = match ($value) {
				'module' => \damix\application::getNameApp(),
				'resource' => 'default',
				'function' => 'index',
			};
		}
		return $part;
	}
	
	public function getPath() : string
    {
		$filename = $this->_folder . DIRECTORY_SEPARATOR . $this->_part['module'];
		$filename .= DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR;
		$filename .= $this->_prefix . $this->_part['resource'] . $this->_suffix . '.' . $this->_extension;
        
        return $filename;
        
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
        $classname = $this->getPart('resource');
		
		$classname = preg_replace('/[\.\-]/', '', $classname);
        
        return $this->_classnameprefix . $classname . $this->_classnamesuffix;
    }
	
}