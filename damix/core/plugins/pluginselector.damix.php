<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\plugins;

class PluginSelector
	extends \damix\core\Selector
{
	protected array $_partselector = array( 'type', 'resource' );
	protected string $_extension = 'coord.php';
	
	public function __construct( string $selector, $params = array() )
    {
		parent::__construct( $selector, $params );
		
        $this->_classnameprefix = ucfirst($this->_part['type']);
    }
	
	public function getPath() : string
    {
        $coredir = \damix\application::getPathCore() . 'plugins' . DIRECTORY_SEPARATOR . 'drivers';
        $appdir = \damix\application::getPathApp() . 'plugins';  
		$filename = DIRECTORY_SEPARATOR . $this->_part['type'] . DIRECTORY_SEPARATOR . $this->_part['resource'] . DIRECTORY_SEPARATOR;
		$filename .= $this->_prefix . $this->_part['resource'] . $this->_suffix . '.' . $this->_extension;
        
		if( file_exists( $appdir . $filename ) )
		{
			return $appdir . $filename;
		}
		elseif( file_exists( $coredir . $filename ) )
		{
			return $coredir . $filename;
		}
		
        return '';
        
    }
	
	public function getNamespace() : string
	{
		return 'damix\coord';
	}
}