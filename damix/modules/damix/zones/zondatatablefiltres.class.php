<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

namespace damix\damix;

class zondatatablefiltresZone
	extends \damix\engines\zones\ZoneBase
{
	protected string $tplSelector = 'damix~tpldatatablefiltres';

	
	protected function prepareTpl() : void 
	{
		$filtres = $this->getParam('filtres');
		
		
		$this->Tpl->assignParameter( 'filtres', $filtres );
		
	}
}