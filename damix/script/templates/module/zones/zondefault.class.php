<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

namespace stmariepvl\bapteme;

class zoninscriptionliste
	extends \damix\engines\zones\ZoneBase
{
	protected string $tplSelector = 'bapteme~tplinscriptionliste';

	
	protected function prepareTpl() : void 
	{
	}
}