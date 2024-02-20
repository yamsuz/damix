<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\databases;


class DbSelector
    extends \damix\core\Selector
{
    protected array $_partselector = array( 'name' );
    protected string $_extension = 'db.php';
    public string $_classnamesuffix = 'DbConnection';
    
    public function __construct( $selector )
    {
        parent::__construct( $selector );
		
		$this->_folder = \damix\application::getPathCore() . '..' . DIRECTORY_SEPARATOR . 'engines' . DIRECTORY_SEPARATOR . 'databases' . DIRECTORY_SEPARATOR . 'drivers' . DIRECTORY_SEPARATOR . $this->getPart('name');
	}
	
	public function getNamespace() : string
    {
		return '\\damix\\engines\\databases\\drivers';
    }

}