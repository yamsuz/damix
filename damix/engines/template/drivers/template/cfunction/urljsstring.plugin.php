<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\template\drivers;

class TemplatesFunctionUrljsstring
    extends \damix\engines\template\drivers\template\TemplateDriverCfunction
    
{
    public function Execute( string $args ) : string
    {
		eval( '$argument = array( ' . $args .' );');
		
        $params=array();
        $search=array();
		$repl=array();
		if( isset( $argument[1] ) )
		{
			foreach($argument[1] as $par=>$var){
				$params[$par]='__@@'.$var.'@@__';
				$search[]=urlencode($params[$par]);
				$repl[]='"+encodeURIComponent('.$var.')+"';
			}
		}
	
		$url = \damix\core\urls\Url::getPath( $argument[0], $params);
		return 'print \'"'.str_replace($search, $repl, $url).'"\';';
    }
}