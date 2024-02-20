<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\orm\defines;

class OrmDefinesBase
{
    protected array $_compiled = array();
    
    public function get( string $key ) : ?string
    {
        return $this->_compiled[$key]['selector'] ?? null;
    }
    
    public function getClasse( string $key ) : ?string
    {
        return $this->_compiled[$key]['class'] ?? null;
    }
    public function getDefines() : array
    {
        return $this->_compiled;
    }
}