<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\iconfont;


class IconFontBase
{
    protected $_icon = array();
    
    public function __construct()
    {
    }
    
    public function getHtml( $name ) : string
    {
		
		if( ! isset( $this->_icon[$name] ) )
		{
			return '';
		}
		
        $icon = $this->_icon[$name];
		
        if ($icon['class'] == 'xbutton_filter'){
            $html = '<i class="damix-dt_btn-filter-small '. $icon['fontclass'] .' '. $icon['class'] .' xbutton_'. $icon['name'] .' "></i>';
        }
        elseif ($icon['class'] == 'xbutton_reglage'){
            $html = '<i class="damix-dt_btn-filter-medium '. $icon['fontclass'] .' '. $icon['class'] .' xbutton_'. $icon['name'] .' "></i>';
        }
        else{
            $html = '<i class="damix-dt_btn-action-small '. $icon['fontclass'] .' '. $icon['class'] .' xbutton_'. $icon['name'] .' "></i>';
        }

        return $html;
    }
    
    public function getProperty( $name )
    {
        return $this->_icon[ $name ];
    }
}