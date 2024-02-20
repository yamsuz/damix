<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\combo;


class OrmComboSelector
	extends \damix\core\Selector
{
    protected array $_partselector = array('resource');
    protected string $_extension = 'xml';
    protected string $_extensiontemp = '.php';
    public array $files = array();
    
    public function __construct( string $selector, $params = array() )
    {
        parent::__construct( $selector, $params );
        
        $this->_folder = \damix\application::getPathConfig() . 'ormcombos' . DIRECTORY_SEPARATOR;
        
    }
    
    public function getFiles() : void
    {
        
        $filename = $this->_folder . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . $this->_prefix . 'ormcombos' . $this->_suffix . '.' . $this->_extension;
        
        if( file_exists( $filename ) )
        {
            $this->files[] = array( 'filename' => $filename );
        }
    }
    
    public function getClassName(): string 
    {
        return 'cOrmCombo_'. $this->getPart( 'resource' );
    }
    
    public function getTempPath(): string 
    {
        return $this->_directorytemp . DIRECTORY_SEPARATOR . 'orm' . DIRECTORY_SEPARATOR . 'combo' . DIRECTORY_SEPARATOR . strtolower( $this->_selector ) . DIRECTORY_SEPARATOR . strtolower( $this->_selector ) .  md5( serialize( $this->_parameters ) ) . $this->_extensiontemp;
    }
    
    public function getHashCode() : string 
    {
        return 'cOrmCombo_'. $this->getPart( 'resource' ) . md5( serialize( $this->_parameters ) );
    }
}