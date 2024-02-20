<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\iconfont;


class IconFontSelector
    extends \damix\core\Selector
{
    protected array $_partselector = array();
    protected string $_extension = 'xml';
    protected string $_extensiontemp = '.php';
    protected string $_directoryname = 'iconfont';
    
    
    public function __construct( string $selector = '' )
    {
        parent::__construct( $selector );
        
        $this->_folder = \damix\application::getPathConfig() . 'iconfont';
    }
    
    public function getFiles() : void
    {
        $filename = $this->getFileDefault();
        
        if( file_exists( $filename ) )
        {
            $this->files[] = array( 'filename' => $filename );
        }
    }
    
    public function getClassName() : string
    {
        return $this->_classnameprefix . 'clstmpiconfont' . $this->_classnamesuffix;
    }
    
    public function getFileDefault() : string
    {
        $filename = $this->_folder . DIRECTORY_SEPARATOR . $this->_prefix . 'default' . $this->_suffix . '.' . $this->_extension;
        
        return $filename;
    }
   
    public function getTempPath() : string
    {
        return $this->_directorytemp . DIRECTORY_SEPARATOR . 'iconfont' . DIRECTORY_SEPARATOR . 'iconfont' . $this->_extensiontemp;
    }
    
}