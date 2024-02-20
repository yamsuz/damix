<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\urls;


class UrlSelector
    extends \damix\core\Selector
{
    protected array $_partselector = array( 'module', 'resource', 'function' );
    protected string $_separator = '/[~\/\:]/';
    protected string $drivers;
	protected string $_extension = 'damix.php';
    public function __construct( $selector )
    {
        parent::__construct( $selector );
		
		$c = \damix\engines\settings\Setting::get('default');
		
		$this->_folder = \damix\application::getPathCore() . DIRECTORY_SEPARATOR . 'urls' . DIRECTORY_SEPARATOR;
		$this->drivers = $c->get( 'url', 'engines' );
    }

	public function getPath() : string
    {
        $filename = $this->_folder . $this->drivers . DIRECTORY_SEPARATOR . 'url' . $this->drivers . $this->_suffix . '.' . $this->_extension;
        
        return $filename;
    }
	
	
	public function getClassName() : string
    {
		$classname = 'url' . $this->drivers;
        
        return $this->_classnameprefix . $classname . $this->_classnamesuffix;
    }
}