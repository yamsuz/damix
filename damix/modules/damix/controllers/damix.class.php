<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

namespace damix\damix;

class damix
	extends \damix\core\controllers\Controller
{
	public function index()
	{
		$rep = $this->getResponse('html');
	
		$rep->Tpl->assignParameter( 'MAIN', '');
		
		return $rep;
	}
	
}

