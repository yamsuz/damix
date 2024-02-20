<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\events;


class EventSelector
    extends \damix\core\Selector
{
    protected array $_partselector = array( 'event' );
    protected string $_extension = 'evt.xml';
    protected string $_extensiontemp = '.php';
    protected string $_directoryname = 'config';
    public string $_classnameprefix = 'Evt';
    public array $files = array();
    public array $completion = array();
    
    public function __construct()
    {
        parent::__construct( '' );
		
		$this->_folder = \damix\application::getPathApp();
    }
	
	public function getPath() : string
    {
        $filename = $this->_folder;
		$filename .= 'events.' . $this->_extension;
        
        return $filename;
    }
	
	public function getTempPath() : string
    {
        return $this->_directorytemp . 'events' . $this->_extensiontemp;
    }
	
	public function getNamespace() : string
    {
		$namespace = \damix\application::getNameApp();
        
        return $namespace . '\\events' ;
    }
	
	public function getClassName() : string
    {
		$classname = 'DamixEvents';
        
        return $this->_classnameprefix . ucfirst($classname) . $this->_classnamesuffix;
    }
	
	public function getFiles() : void
    {
        $filename = $this->getPath();
        
        if( file_exists( $filename ) )
        {
            $this->files[] = array( 'filename' => $filename );
        }

    }
    
}