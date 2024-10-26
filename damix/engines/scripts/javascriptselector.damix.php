<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\scripts;

class JavascriptSelector
    extends \damix\core\Selector
{
    protected array $_partselector = array( 'resource' );
    protected string $_extension = '.js.php';
    protected string $_extensiontemp = '.js';
    
    public function __construct( $selector )
    {
        parent::__construct( $selector );
        
        $this->_folder = \damix\application::getPathConfig() . 'javascript';
    }
    
    protected function readSelector() : void
    {
        $this->_part = preg_split( $this->_separator, $this->_selector );
    }
    
    public function getTempPath() : string
    {
        $dir = implode( DIRECTORY_SEPARATOR, $this->_part );
		
        return \damix\application::getPathWww() . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . $dir . $this->_extensiontemp;
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
        $unique = '';
		$filename = $this->getTempPath();
		if( file_exists( $filename ) )
		{
			$unique = filemtime( $filename );
		}
        return $dirwww . 'js/' . $dir . $this->_extensiontemp . '?' . $unique;
    }

}