<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm;

class OrmStructureSelector
	extends \damix\core\Selector
{
	protected array $_partselector = array( 'module', 'resource', 'function' );
    protected string $_extension = 'orm.xml';
    protected string $_extensiontemp = '.php';
    public string $_type = 'orm.xml';
	public string $_classnameprefix = 'cOrmStructure_';
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
		$classname = $this->getPart( 'module' ) . '_' . $this->getPart( 'resource' );
        
		$classname = preg_replace('/[\.\-]/', '', $classname);
        
        return $this->_classnameprefix . $classname . $this->_classnamesuffix;
    }
	
    public function getNamespace() : string
    {
		$namespace = \damix\application::getNameApp() . '\\' . $this->getPart( 'module' ) . '\\' . $this->getPart( 'resource' );
		
        return $namespace;
    }
	
    public function getTempPath() : string
    {
        $dir = $this->getPart( 'module' ) . DIRECTORY_SEPARATOR . $this->getPart( 'resource' ) ;
        
        return $this->_directorytemp . '..' . DIRECTORY_SEPARATOR . 'donotdelete' . DIRECTORY_SEPARATOR . 'ormstructure' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR .  $this->getPart( 'resource' ) . $this->_extensiontemp;
    }
}