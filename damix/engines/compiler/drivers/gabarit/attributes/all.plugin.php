<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\attributes;

class GabaritAttributeAll
    extends \damix\engines\compiler\PluginAttributeDriver
{
    public function write( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \damix\engines\compiler\CompilerContentAttribute $attribute, object $obj ) : bool
    {
        if( $node->name == 'compiler' )
        {
            return false;
        }

        $driver->content->addData( '_html', ' ' . $attribute->name .'="' . $attribute->value . '"' );

        return true;
    }
       
    protected function getLocale( string $locale ) : string
    {
		$define = \damix\engines\orm\Orm::getDefine( $locale );
		if( $define && isset( $define['field']['locale'] ))
		{
			$locale = $define['field']['locale'];
		}
	    
		$locale = \damix\engines\locales\Locale::get( $locale );
        
        return $locale;
    }
	
	protected function addAttributes(string $name, \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \damix\engines\compiler\CompilerContentAttribute $attribute ) : void
    {
        $attr = new \damix\engines\compiler\CompilerContentAttribute();
        $attr->name = $name;
        $attr->value = $attribute->value;

        $node->addAttributes( $attr );

        $plugin = $driver->getPluginAttribute( $attr->name );

        $attr->plugin = $plugin;

        if( $attr )
        {
            $attr->plugin->beforeRead( $driver, $node, $attribute );
        }
    }
}