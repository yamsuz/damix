<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\sessions;


class SessionSelector
    extends \damix\core\Selector
{
    protected array $_partselector = array( 'type' );
	protected string $_separator = '/[~\.]/';
    protected string $_extension = 'damix.php';
    public string $_prefix = 'session';
	public string $_classnameprefix = 'Session';
	public string $_var;

    public function __construct( $selector )
    {
        parent::__construct( $selector );
		
		$this->_folder = \damix\application::getPathCore() . '..' . DIRECTORY_SEPARATOR . 'engines' . DIRECTORY_SEPARATOR . 'sessions' . DIRECTORY_SEPARATOR . $this->getPart( 'type' );
		
    }
	public function getNamespace() : string
    {
        return 'damix\\engines\\sessions';
    }
}