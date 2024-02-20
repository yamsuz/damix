<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\scripts;

class CssSelector
    extends \damix\core\Selector
{
    protected array $_partselector = array( 'resource' );
    protected string $_extension = '.css.php';
    protected string $_extensiontemp = '.css';
    
    public function __construct( string $selector )
    {
        parent::__construct( $selector );
        
        $this->_folder = \damix\application::getPathConfig() . 'css';
    }
    
    protected function readSelector() : void
    {
        $this->_part = preg_split( $this->_separator, $this->_selector );
    }
    
    public function getTempPath() : string
    {
        $dir = implode( DIRECTORY_SEPARATOR, $this->_part );
		
        return \damix\application::getPathWww() . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $dir . $this->_extensiontemp;
    }
    
    public function getPath() : string
    {
        $dir = implode( DIRECTORY_SEPARATOR, $this->_part );
		
        return $this->_folder . DIRECTORY_SEPARATOR . $dir . $this->_extension;
    }

    public function getUrlPath() : string
    {
        $dirwww = \damix\core\urls\Url::getBasePath();
        $dir = implode( '/', $this->_part );
        
        return $dirwww . 'css/' . $dir . $this->_extensiontemp;
    }

}