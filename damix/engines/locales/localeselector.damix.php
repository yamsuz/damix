<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\locales;


class LocaleSelector
    extends \damix\core\Selector
{
    protected array $_partselector = array( 'module', 'resource', 'key', 'locale' );
	protected string $_separator = '/[~\.]/';
    protected string $_extension = '.xml';
    protected string $_extensiontemp = '.php';
    protected string $_directoryname = 'config';
    protected string $_encoding = 'UTF-8';
    public string $_classnameprefix = 'xml';
    public array $files = array();
    public array $completion = array();
    protected ?\damix\engines\settings\SettingBase $setting = null;
	protected bool $core = false;

    public function __construct( $selector )
    {
		if( !$this->setting )
		{
			$this->setting = \damix\engines\settings\Setting::get('default');
		}
		
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
	
	public function getUniqueSelector() : string
	{
		return $this->getPart( 'module' ) . $this->_part['resource'] . $this->_part['locale'];
	}
	
	protected function readSelector() : void
    {
        $split = preg_split( $this->_separator, $this->_selector );
		$max = count( $split );
        if( count( $split ) > 2 )
        {
			$this->_part[ 'module' ] = $split[0];
			$this->_part[ 'resource' ] = $split[1];
			$this->_part[ 'locale' ] = $this->setting->get( 'general', 'langue' );
			
			$this->_part[ 'key' ] = '';
            for( $i = 2; $i < $max; $i++ )
            {
                $this->_part[ 'key' ] .= $split[ $i ] . ( $i < $max -1 ? '.' : '');
            }
			
            $this->_maxpart = count( $this->_part );
        }
    }
	
	public function getTempPath() : string
    {
        $dir = strtolower( $this->_part['module'] . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $this->_part['resource'] . '-' . $this->_part['locale'] . '-' . $this->_encoding );
      
        
        return $this->_directorytemp . $dir . $this->_extensiontemp;
    }
	
	public function getNamespace() : string
    {
        return 'damix\\engines\\xlocale';
    }
	
	public function getClassName() : string
    {
        $classname = $this->_part['module'] . '_' . $this->_part['resource'] . '_' . $this->_part['locale'] . '_' . $this->_encoding ;
		
		$classname = preg_replace('/[\.\-]/', '', $classname);
        
        return $this->_classnameprefix . $classname . $this->_classnamesuffix;
    }
	
    public function getFileDefault() : string
    {
        $filename = $this->_folder;
  
		$filename .= DIRECTORY_SEPARATOR . $this->_part['module'];
		$filename .= DIRECTORY_SEPARATOR . 'locales';
		$filename .= DIRECTORY_SEPARATOR . $this->_part['locale'] . DIRECTORY_SEPARATOR ;
		$filename .= $this->_prefix . $this->_part['resource'] . '.' . $this->_encoding . $this->_extension;
        
        return $filename;
    }
	
}