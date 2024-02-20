<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler;

class CompilerContentAttribute
{
    public $name;
    public $value;
    public $plugin;
    public $domattribute;
    
    public static function getAttribute( \DOMAttr $attribute )
    {
        $attr = new CompilerContentAttribute();
        $attr->name = $attribute->name;
        $attr->value = $attribute->value;
        $attr->domattribute = $attribute;
        
        return $attr;
    }
}