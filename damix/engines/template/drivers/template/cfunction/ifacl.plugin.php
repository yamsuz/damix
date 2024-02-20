<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\template\drivers;

class TemplatesFunctionIfacl
    extends \damix\engines\template\drivers\template\TemplateDriverCfunction
    
{
    public function Execute( string $args1 ) : string
    {
		if(! $this->endBlock){
			$content = 'if(\damix\engines\acls\Acl::check('.$args1.')):';
		}else{
			$content = 'endif; ';
		}
		return $content;
    }
}