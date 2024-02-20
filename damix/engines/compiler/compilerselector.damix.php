<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler;

class CompilerSelector
    extends \damix\core\Selector
{
    public $files = array();
    public $completion = array();
    public $tempfile;
    public $_selectorname;
    public $_classnameprefix = '';
    public $_classnamesuffix = '';
    protected $_extensiontemp = '.php';
    
    public function __construct( $selector )
    {
        parent::__construct( $selector );
    }
   
    public function getTempPath() : string
    {
        $dir = implode( DIRECTORY_SEPARATOR, $this->_part );
        
        return $this->_directorytemp . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $this->getClassName() . $this->_extensiontemp;
    }
}