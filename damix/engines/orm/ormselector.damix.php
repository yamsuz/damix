<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm;

class OrmSelector
	extends \damix\core\Selector
{
	protected array $_partselector = array( 'module', 'resource', 'function' );
    protected string $_extension = 'orm.xml';
    protected string $_extensiontemp = '.php';
    public string $_type = 'orm.xml';
	protected bool $core = false;

	
	public function __construct( string $selector, $params = array() )
    {
		parent::__construct( $selector, $params );
		
        $module = $this->getPart('module');
		$appdir = \damix\application::getPathApp() . 'modules' . DIRECTORY_SEPARATOR . $module;
		
		if( is_dir( $appdir ) )
		{
			$this->core = false;
			$this->_folder = \damix\application::getPathApp() . 'modules' . DIRECTORY_SEPARATOR . $this->_part['module'] . DIRECTORY_SEPARATOR;
		}
		else
		{
			$this->core = true;
			$this->_folder = \damix\application::getPathCore() . '..' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->_part['module'] . DIRECTORY_SEPARATOR;
		}
        
        switch( substr( $this->getPart( 'resource' ), 0, 1 ) )
        {
            case 's':
                $this->_type = 'select';
                break;
            case 't':
                $this->_type = 'structure';
                break;
            case 'v':
                $this->_type = 'view';
                break;
        }
    }
	
	public function getPath() : string
    {
        $resource = $this->getPart( 'resource' );
        
        $folder = substr( $resource, 0, 1 ) . 'orm';
        
        return $this->_folder . 'orm' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $this->_prefix . $resource . $this->_suffix . '.' . $this->_extension;
    }
    
    public function getFiles() : void
    {
        $filename = $this->getPath();
        
        if( file_exists( $filename ) )
        {
            $this->files[] = array( 'filename' => $filename );
        }
    }
    
	public function getClassName() : string
    {
		$classname = implode( "_", $this->_part );
        
		$classname = preg_replace('/[\.\-]/', '', $classname);
        
        return $this->_classnameprefix . $classname . $this->_classnamesuffix;
    }
	
    public function getNamespace() : string
    {
		$part = array_values( $this->_part );
		if( $this->core )
		{
			$namespace = 'damix';
		} else{
			$namespace = \damix\application::getNameApp();
		}
        for( $i = 0; $i < $this->_maxpart - 1; $i++ )
		{
			$namespace .= '\\' . $part[$i];
		}
		
        return $namespace;
    }
	
    public function getTempPath() : string
    {
        $dir = implode( DIRECTORY_SEPARATOR, $this->_part );
        
        return $this->_directorytemp . '..' . DIRECTORY_SEPARATOR . 'donotdelete' . DIRECTORY_SEPARATOR . 'orm' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR .  $this->getPart( 'resource' ) . $this->_extensiontemp;
    }
	
	public function getPropertiesClassName() : string
    {
        return 'cOrmProperties_'. $this->getPart( 'module' ) .'_' . $this->getPart( 'resource' );
    }
    
    public function getFactoryClassName() : string
    {
        return 'cOrmFactory_'. $this->getPart( 'module' ) .'_' . $this->getPart( 'resource' );
    }
    
    public function getRecordClassName() : string
    {
        return 'cOrmRecord_'. $this->getPart( 'module' ) .'_' . $this->getPart( 'resource' );
    }
}