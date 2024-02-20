<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\apps\response;

class ClassicMyhtmlauthresponse
	extends \damix\core\response\ResponseBaseHtml
{
	protected string $_bodyTpl = 'auth~connexion';
	
}