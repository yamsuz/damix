<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\template\drivers\template;

abstract class TemplateDriverCfunction 
{
    public bool $endBlock;
    protected string $classname;
	
	public function Execute( string $args ) : string
	{
        return 'print ' . $this->classname .'( ' . $args . ');';
	}
	
	public function quote( $text, $level = 1 ) : string
    {
        if( $level < 1 ) { $level = 1; }
        $replacement = '';
        
        for( $i = 0; $i < $level; $i++ )
        {
            $replacement .= '\\\'';
        }
        
        $text = preg_replace( '/[\']/i', $replacement, $text );
        $text = preg_replace( '/__QUOTE__/', '\'', $text );


        $text = '\'' . $text . '\'';

        return $text;
    }
}