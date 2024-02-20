<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\tools;

class GeneratorContent
{
    private $_function = array();
    private $_property = array();
    private $_data = array();
    private $_construct = array();
    private $_filecontent = array();
    private $_tab = 0;
    
    public function clear()
    {
        $this->_function = array();
        $this->_property = array();
        $this->_construct = array();
        $this->_tab = 0;
    
    }
    
    public function addFunction( string $name, array $params, array $content, string $visibility = 'public') : void
    {
        $this->appendFunction( $name, $params, $content, $visibility);
    }
    
    public function appendFunction( string $name, array $params, array $content, string $visibility = 'public', string $return = '') : void
    {
        if( ! isset( $this->_function[ $name ] ) )
        {
            $this->_function[ $name ] = array( 
                                'name' => $name,
                                'params' => $params,
                                'visibility' => $visibility,
                                'content' => $content,
                                'return' => $return,
                            );            
        }
        else
        {
            $this->_function[ $name ][ 'content' ][] = $content;
        }
    }
    
    public function appendFunctionContent( string $name, string $content) : void
    {
        $this->appendFunction( $name, array(), array( $content ) );
    }
    
    public function addData( string $name, string $content = '') : void
    {
        if( $content != '' )
        {
            $this->_data[ $name ][] = $content;
        }
    }
    
    public function addConstructInit( string $name, array $value ) : void
    {
        $this->_construct[ $name ] = $value;
    }
    
    public function addProperty( string $name, string $value, string $type, bool $null, string $visibility ) : void
    {
        $this->_property[ $name ] = array( 
                            'name' => $name,
                            'visibility' => $visibility,
                            'value' => $value,
                            'type' => $type,
                            'null' => $null,
                            );
    }
    
    public function getConstruct() : array
    {
        return $this->_construct;
    }
    
    public function getFunction() : array
    {
        return $this->_function;
    }
    
    public function getData() : array
    {
        return $this->_data;
    }
    
    public function getProperties() : array
    {
        return $this->_property;
    }
    
     public function writeText( $text = '', $crlf = 1 ): void
    {
        $this->writeLine( $text, 0 );
    }
    
    public function writeLine( $text = '', $crlf = 1 ): void
    {
        $str = '';
        
        for( $i = 0; $i < $this->_tab; $i++ )
        {
            $str .= '    ';
        }
        
        $str .= $text;
        
        for( $i = 0; $i < $crlf; $i++ )
        {
            $str .= chr( 13 );
        }
        
        $this->_filecontent[] = $str;
    }
    
    public function tab( $nb ) : void
    {
        $this->_tab += $nb;
    }
    
    public function getText() : string
    {
        $text = '';
        
        foreach( $this->_filecontent as $i => $c )
        {
            if( is_string( $c ) )
            {
                $text .= $c;
            }
            else if( $c instanceof xTemplateContent )
            {
                $text .= $c->getText();
            }
        }
        
        return $text;
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
    	
    protected function writecontent( )
    {
        $properties = $this->getProperties();
        
        foreach( $properties as $name => $info )
        {
            $this->tab( 1 );
            $this->writeLine( $info['visibility'] . ' ' . $info['type'] . ' $' . $name . ' = ' . $info['value'] . ';' );
            
            $this->tab( -1 );
        }

        $this->tab( 1 );

        $this->writeLine( '' );
        $construct = $this->getConstruct();
        $this->writeLine( 'public function __construct()' );
        $this->writeLine( '{' );
        $this->tab( 1 );
        $this->writeLine( 'parent::__construct();' );
        foreach( $construct as $name => $info )
        {
            $this->writeLine( implode( ';', $info ) );
        }
        $this->tab( -1 );
        $this->writeLine( '}' );
        $this->tab( -1 );
        
        
        $functions = $this->getFunction();
        foreach( $functions as $name => $info )
        {
            $this->tab( 1 );
            $this->writeLine( $info['visibility'] . ' function ' . $name . '(' . implode( ',', $info['params'] ). ')' . (!empty($info['return']) ? ' : ' . $info['return'] : ''));
            $this->writeLine( '{' );
            $this->tab( 1 );
            foreach( $info['content'] as $content )
            {
                if( is_array( $content ) )
                {
                    $content = implode( '', $content );
                }
                $this->writeLine( $content );
            }
            $this->tab( -1 );
            $this->writeLine( '}' );
            
            $this->tab( -1 );
        }
    }
}