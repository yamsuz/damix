<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

namespace damix\damix;

class forms
	extends \damix\core\controllers\Controller
{    
    
    public function load() 
    {
        $rep = $this->getResponse( 'jhjc' );
        
        $out = array();
        
        $selector = $this->request->getParamString( 's' );
       
		 
        $gabarit = \damix\engines\views\gabarits\Gabarit::get( $selector );
        $gabarit->createJSFile();
        
        $out[ 'html' ] = $gabarit->getHtml();
        
        $rep->data = $out;
        return $rep;
    }
    
}

