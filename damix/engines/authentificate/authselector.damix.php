<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\authentificate;


class AuthSelector
    extends \damix\core\Selector
{
    protected array $_partselector = array( 'name' );
    protected string $_extension = 'damix.php';
    public string $_prefix = 'auth';
	public string $_classnameprefix = 'Auth';
	public string $_var;

    public function __construct( string $selector)
    {
        parent::__construct( $selector );
		
		$this->_folder = \damix\application::getPathCore() . '..' . DIRECTORY_SEPARATOR . 'engines' . DIRECTORY_SEPARATOR . 'authentificate' . DIRECTORY_SEPARATOR . 'drivers' . DIRECTORY_SEPARATOR . $this->getPart('name');

    }
	
	public function getNamespace() : string
    {
		return '\\damix\\engines\\authentificate';
    }
	
}