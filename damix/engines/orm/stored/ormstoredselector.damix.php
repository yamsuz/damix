<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\stored;


class OrmStoredSelector
    extends \damix\core\Selector
{
    const STORAGE_PROCEDURE = 'procedure';
    const STORAGE_FUNCTION = 'function';
    const STORAGE_TRIGGER = 'trigger';
    const STORAGE_EVENT = 'event';
    protected array $_partselector = array('resource');
    protected string $_extension = 'xml';
    protected string $_extensiontemp = '.php';
    protected string $_type = '';
    public array $files = array();
    
    public function __construct( $type )
    {
        parent::__construct( '' );
        
        $this->_folder = \damix\application::getPathConfig() . 'ormstored' . DIRECTORY_SEPARATOR;
        $this->_type = $type;
        
    }
    
    public function getFiles() : void
    {
        $filename = $this->getFileDefault();
		
        if( file_exists( $filename ) )
        {
            $this->files[] = array( 'filename' => $filename );
        }
    }
    
    public function getFileDefault() : string
	{
		switch( $this->_type )
        {
            case OrmStoredSelector::STORAGE_PROCEDURE:         
                $filename = $this->_folder . $this->_prefix . 'ormprocedure' . $this->_suffix . '.' . $this->_extension;
                break;
            case OrmStoredSelector::STORAGE_FUNCTION:
                $filename = $this->_folder . $this->_prefix . 'ormfunction' . $this->_suffix . '.' . $this->_extension;
                break;
            case OrmStoredSelector::STORAGE_EVENT:
                $filename = $this->_folder . $this->_prefix . 'ormevent' . $this->_suffix . '.' . $this->_extension;
                break;
            case OrmStoredSelector::STORAGE_TRIGGER:
                $filename = $this->_folder . $this->_prefix . 'ormevent' . $this->_suffix . '.' . $this->_extension;
                break;
        }
        return $filename;
	}
	
    public function getClassName() : string
    {
        switch( $this->_type )
        {
            case OrmStoredSelector::STORAGE_PROCEDURE:
                return 'cOrmStoredProcedure';
                break;
            case OrmStoredSelector::STORAGE_FUNCTION:
                return 'cOrmStoredFunction';
                break;
            case OrmStoredSelector::STORAGE_EVENT:
                return 'cOrmStoredEvent';
                break;
        }        
    }
  
    public function getTempPath() : string
    {
        switch( $this->_type )
        {
            case OrmStoredSelector::STORAGE_PROCEDURE:
                return $this->_directorytemp . '..' . DIRECTORY_SEPARATOR . 'donotdelete' . DIRECTORY_SEPARATOR . 'orm' .DIRECTORY_SEPARATOR . 'ormstored' . DIRECTORY_SEPARATOR . $this->_prefix . 'ormprocedure' . $this->_suffix . $this->_extensiontemp;
                break;
            case OrmStoredSelector::STORAGE_FUNCTION:
                return $this->_directorytemp . '..' . DIRECTORY_SEPARATOR . 'donotdelete' . DIRECTORY_SEPARATOR . 'orm' .DIRECTORY_SEPARATOR . 'ormstored' . DIRECTORY_SEPARATOR . $this->_prefix . 'ormfunction' . $this->_suffix . $this->_extensiontemp;
                break;
            case OrmStoredSelector::STORAGE_EVENT:
                return $this->_directorytemp . '..' . DIRECTORY_SEPARATOR . 'donotdelete' . DIRECTORY_SEPARATOR . 'orm' .DIRECTORY_SEPARATOR . 'ormstored' . DIRECTORY_SEPARATOR . $this->_prefix . 'ormevent' . $this->_suffix . $this->_extensiontemp;
                break;
        }
    }
  
    public function getPathContent( $name ) : string
    {
        $driver = \damix\engines\databases\Db::getDriverName();
        $filename = '';
        switch( $this->_type )
        {
            case OrmStoredSelector::STORAGE_PROCEDURE:
                $filename = $this->_folder . 'sql' . DIRECTORY_SEPARATOR . $driver . DIRECTORY_SEPARATOR . 'procedure' . DIRECTORY_SEPARATOR . strtolower($name) . '.proc.sql';
                break;
            case OrmStoredSelector::STORAGE_FUNCTION:
                $filename = $this->_folder . 'sql' . DIRECTORY_SEPARATOR . $driver . DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . strtolower($name) . '.func.sql';
                break;
            case OrmStoredSelector::STORAGE_TRIGGER:
                $filename = $this->_folder . 'sql' . DIRECTORY_SEPARATOR . $driver . DIRECTORY_SEPARATOR . 'trigger' . DIRECTORY_SEPARATOR . strtolower($name) . '.trig.sql';
                break;
            case OrmStoredSelector::STORAGE_EVENT:
                $filename = $this->_folder . 'sql' . DIRECTORY_SEPARATOR . $driver . DIRECTORY_SEPARATOR . 'event' . DIRECTORY_SEPARATOR . strtolower($name) . '.event.sql';
                break;
        }
        
        return $filename;
    }
}