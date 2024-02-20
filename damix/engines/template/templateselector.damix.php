<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\template;


class TemplateSelector
    extends \damix\core\Selector
{
	protected array $_partselector = array( 'module', 'resource' );
    protected string $_extension = '.tpl';
    protected string $_extensiontemp = '.php';
    protected string $_directoryname = 'templates';
	protected bool $core = false;

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
	}
	
	public function getPath() : string
    {
        $filename = $this->_folder;
  
		$filename .= DIRECTORY_SEPARATOR . $this->_part['module'];
		$filename .= DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
		$filename .= $this->_prefix . $this->_part['resource'] . $this->_suffix . '.' . $this->_extension;
        
        return $filename;
    }
	
	public function getFileDefault() : string
    {        
        $filename = $this->_folder . DIRECTORY_SEPARATOR . $this->getPart('module') . DIRECTORY_SEPARATOR . $this->_directoryname . DIRECTORY_SEPARATOR . $this->_prefix . $this->getPart('resource') . $this->_suffix . $this->_extension;
        
        return $filename;
    }
		
	public function getTempPath() : string
    {       
        $filename = $this->_directorytemp . $this->getPart('module') . DIRECTORY_SEPARATOR .  'templates' . DIRECTORY_SEPARATOR . $this->_prefix . $this->getPart('resource') . $this->_suffix . $this->_extensiontemp;
		
		return $filename;
    }
	
	public function getGenerator() : \damix\engines\template\TemplateBaseGenerator
    {       
        $tpl = new \damix\engines\template\drivers\template\TemplateGenerator();
		return $tpl;
    }
}