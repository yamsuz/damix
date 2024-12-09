<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\zones;

class ZoneGenerator
{
	public function generate( ZoneSelector $selector ) : bool
    {
		$this->_sourceFile = $selector->getFileDefault();
		
		if( !file_exists( $this->_sourceFile ) )
		{
			throw new \damix\core\exception\CoreException('Le fichier de la zone n\'existe pas ' . $this->_sourceFile);
		}
		
		return true;
    }
	
}