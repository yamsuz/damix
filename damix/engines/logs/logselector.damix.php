<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\logs;


class LogSelector
    extends \damix\core\Selector
{
    protected array $_partselector = array( 'type' );
	protected string $_separator = '/[~\.]/';
    protected string $_extension = 'damix.php';
    public string $_prefix = 'log';
	public string $_classnameprefix = 'Log';
	public string $_var;

    public function __construct( $selector )
    {
        parent::__construct( $selector );
		
		$this->_folder = \damix\application::getPathCore() . '..' . DIRECTORY_SEPARATOR . 'engines' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $this->getPart( 'type' );
		$this->_var = \damix\application::getPathApp() . 'var'  . DIRECTORY_SEPARATOR . 'logs';
    }
	
	public function getClassName() : string
    {
        return $this->_classnameprefix . ucfirst($this->getPart( 'type' )) . $this->_classnamesuffix;
    }
	
	public function getFolder() : string
    {
        return $this->_var . DIRECTORY_SEPARATOR;
    }
}