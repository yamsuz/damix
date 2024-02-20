<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\tools;

class Language
{
    private array $_instructions = array();
    private int $_max = 0;
    
    private ?\StdClass $_actions;
    private array $_functions = array();
    
    public function parse( string $chaine ) : bool
    {
        preg_match_all( '/([a-z0-9A-Z \'\.\-\/]*)([,\(\)])([a-z0-9A-Z \'\.\-\/_]*)/', $chaine, $out );
        
        $nb = count($out[0]);
        $this->_instructions = array();
        $this->_functions = array();
        $this->_actions = null;
        
        for($i = 0; $i < $nb ; $i ++)
        {
            $elt = trim($out[1][$i]);
            if( $elt != '' )
            {
                $this->_instructions[] = $elt;
            }
            $elt = trim($out[2][$i]);
            if( $elt != '' )
            {
                $this->_instructions[] = $elt;
            }
            $elt = trim($out[3][$i]);
            if( $elt != '' )
            {
                $this->_instructions[] = $elt;
            }
        }

        $this->_max = count( $this->_instructions );
        $this->_actions = $this->split( 0 );
        
        if( property_exists( $this->_actions, 'params' ) )
        {
            return true;
        }
        return false;
    }

    public function getFunctions() : array
    {
        return $this->_functions;
    }
    
    private function split( $nb ) : \StdClass
    {
        $current = new \StdClass();
        $pos = 0;
        
        for( $i = $nb; $i < $this->_max; $i ++)
        {
            switch( $this->_instructions[ $i ] )
            {
                case '(':
                    $pos ++;
                    break;
                case ')':
                    $current = $current->parent;
                    break;
                case ',':
                    break;
                default:
                    if( isset( $this->_instructions[ $i + 1 ] ) )
                    {
                        switch( $this->_instructions[ $i + 1 ] )
                        {
                            case '(':
                                $out = new \StdClass();
                                $out->name = $this->_instructions[ $i ];
                                $out->parent = $current;
                                $out->type = 'function';
                                
                                $current->params[] = $out;
                                
                                $current = $out;
                                break;
                            case ')':
                            case ',':
                                $out = new \StdClass();
                                $out->name = $this->_instructions[ $i ];
                                $out->parent = $current;
                                $out->type = 'params';
                                $current->params[] = $out;
                                break;
                        }
                    }
                    
                    break;
            }
        }
        return $current;
        
    }
    
    public function execute( $handler = null ) : array
    {
        if( $handler === null )
        {
            $handler = array( $this, 'callback');
        }
        return $this->executehand( $this->_actions->params, $handler);
    }
    
    private function executehand( $obj, $handler ) : array
    {
        $out = array();
        foreach( $obj as $i => $params )
        {
            switch( $params->type )
            {
                case 'function':
                    if( $params )
                    {
                        if( property_exists( $params, 'params' ) )
                        {
                            $p = $this->executehand( $params->params, $handler );
                            $result = $handler( $params, $p );
                            if( $result )
                            {
                                $out[] = $result;
                            }
                        }
                    }
                    break;
                case 'params':
                    $out[] = $params->name;
                    break;
            }
        }
        
        return $out;
    }

    private function callback( $obj, $params )
    {
        return call_user_func_array($obj->name, $params);
    }

}