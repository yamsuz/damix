<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

namespace damix\damix;

class zonboutonsZone
	extends \damix\engines\zones\ZoneBase
{
	protected string $tplSelector = 'damix~tplboutons';

	
	protected function prepareTpl() : void 
	{
		$boutons = $this->getParam('boutons');
		
		
		$this->Tpl->assignParameter( 'boutons', $boutons );
		
	}
}