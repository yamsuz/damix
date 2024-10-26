<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core;

abstract class Selector
{
    protected array $_partselector = array( 'module', 'resource' );
    protected array $_part = array();
    protected string $_separator = '/[~:]/';
    protected string $_folder = '';
    protected string $_directorytemp = '';
    protected string $_directoryname = '';
    protected string $_extensiontemp = '.php';
    protected string $_extension = 'class.php';
    public string $_classnameprefix = '';
    public string $_classnamesuffix = '';
    protected $_maxpart = 0;
    protected string $_dircompletion = '';
    protected string $_dirconfiguration = '';
    public array $files = array();
    public string $_suffix = '';
    public string $_prefix = '';
    public string $_selector = '';
    public array $_parameters = array();
    public array $completion = array();
    public bool $usercompletion = true;
    protected bool $classnameuser = false;
    protected string $classnamebase = '';
    protected string $classnameseparator = '';
    
    public function __construct( string $selector, $params = array() )
    {
        $this->_folder = \damix\application::getPathApp() . 'engines';
        $this->_selector = $selector;
        $this->_parameters = $params;
        $this->_directorytemp = \damix\application::getPathTemp() . 'compiled' . DIRECTORY_SEPARATOR;
            
        $this->readSelector();
    }
	
    protected function readSelector() : void
    {
        $split = preg_split( $this->_separator, $this->_selector );
        
		$ps = 0;
		foreach( $split as $i => $part )
		{
			if( ! empty( $part ) )
			{
				$name = $this->_partselector[ $ps ] ?? '';
				if( empty( $name ) )
				{
					$name = $i;
				}
				$this->_part[ $name ] = $part;
				
				$ps ++;
			}
		}
	
		$this->_maxpart = count( $this->_part );
	
    }
    
    public function getHashCode() : string
    {
        return md5($this->_selector);
    }
    
    public function getPart( string $value ) : string | null
    {
        return $this->_part[ $value ] ?? null;
    }
    
	public function toString()
    {
        return $this->_selector;
    }
	
    public function getClassName() : string
    {
		$part = array_values( $this->_part );
		if( empty( $this->classnamebase ) )
		{
			$classname = $part[$this->_maxpart - 1];
		}
		else
		{
			$classname = $this->classnamebase;
		}
		
		$classname = preg_replace('/[~:\.\-]/', $this->classnameseparator, $classname);
		
		if( $this->classnameuser )
		{
			$classname .= $this->classnameseparator . \damix\engines\tools\xTools::login();
		}
        
        return preg_replace('/[@\.]/', '_', $this->_classnameprefix . ucfirst($classname) . $this->_classnamesuffix);
    }
	
    public function getNamespace() : string
    {
		$part = array_values( $this->_part );
		$namespace = \damix\application::getNameApp();
        for( $i = 0; $i < $this->_maxpart - 1; $i++ )
		{
			$namespace .= '\\' . $part[$i];
		}
		
        return $namespace;
    }
	
    public function getFullNamespace() : string
    {
		return $this->getNamespace() . '\\' . $this->getClassName();
    }
    
    public function getPath() : string
    {
        $filename = $this->_folder;
        
        $i = 1;
        foreach( $this->_part as $part )
        {
            if( $i < $this->_maxpart )
            {
                $filename .= DIRECTORY_SEPARATOR . $part;
            }
            else
            {
                $filename .= DIRECTORY_SEPARATOR . $this->_prefix . $part . $this->_suffix . '.' . $this->_extension;
            }
            $i ++;
        }
        
        return $filename;
    }
	
    public function getFileDefault() : string
    {
        $dir = implode('_', $this->_part);
        
        $filename = $this->_folder . DIRECTORY_SEPARATOR . $this->_directoryname . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $this->_prefix . 'default' . $this->_suffix . '.' . $this->_extension;
        
        return $filename;
    }
	
    public function getFileGroup() : string
    {
        $dir = implode('_', $this->_part);
        
        $filename = $this->_dircompletion . $dir . DIRECTORY_SEPARATOR . $this->_prefix . 'group_'. \damix\engines\tools\xTools::usergroup() . $this->_suffix . '.' . $this->_extension;
        
        return $filename;
    }
   
    public function getFileUser() : string
    {
        $dir = implode('_', $this->_part);
        
        $filename = $this->_dircompletion . $dir . DIRECTORY_SEPARATOR . $this->_prefix . \damix\engines\tools\xTools::login() . $this->_suffix . '.' . $this->_extension;
        
        return $filename;
    }
	
    public function getTempPath() : string
    {
        $dir = implode(DIRECTORY_SEPARATOR, $this->_part);
        
		if( $this->usercompletion )
		{
			$login = \damix\engines\tools\xTools::login();
			
			$name = substr($login , 0, 5) . '_'. md5( $login . $this->getClassName() );
		}
		else
		{
			$name = md5($this->getClassName());
		}
        
        return $this->_directorytemp . $dir . DIRECTORY_SEPARATOR . $name . $this->_extensiontemp;
    }
    
    public function getFiles() : void
    {
        $filename = $this->getFileDefault();
       
		if( file_exists( $filename ) )
		{
			$this->files[] = array( 'filename' => $filename );
		}

		if( $this->usercompletion )
		{
			$filename = $this->getFileUserGroup();

			if( $filename )
			{
				$this->completion[] = array( 'filename' => $filename );
			}
		}
    }
    
    public function getFileUserGroup() : string|null
    {
        $filename = $this->getFileUser();

        if( file_exists( $filename ) )
        {
            return $filename;
        }
        else
        {
            $filename = $this->getFileGroup();
            
            if( file_exists( $filename ) )
            {
                return $filename;
            }
        }
        
        return null;
    }
    
   
}