<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\template;


class Template
{
    public array $parameters = array();
    protected TemplateSelector $selector;
    
    public static function get( string $selector ) : Template
    {
		$sel = new TemplateSelector( $selector );
		
		if( ! TemplateCompiler::compile( $sel ) )
		{
			throw new \damix\core\exception\CoreException('Le template ' . $selector . ' est en erreur');
		}
		
		$template = new Template();
		$template->selector = $sel;
		return $template;
    }
	
	public function fetch()
	{
		$content='';
		ob_start();
		try{
			require_once( $this->selector->getTempPath() );
			$fct = 'template_' . $this->selector->getHashCode();
			$fct($this);
			$content=ob_get_clean();
		}catch(Exception $e){
			ob_end_clean();
			throw $e;
		}
		return $content;
	}
	
	public function assignParameter( string $name, mixed $value = '' ) : void
	{
		$this->parameters[ $name ] = $value;
	}
	
	public function assignParameters( array $params ) : void
	{
		$this->parameters = array_merge( $this->parameters, $params );
	}
	
	public function assignZone( string $name, string $zoneSelector, array $params = array() ) : void
	{
		$this->assignParameter($name,\damix\engines\zones\Zone::get( $zoneSelector, $params ) );
	}
	
	public function getValue( string $name ) : mixed
	{
		return $this->parameters[ $name ] ?? '';
	}
	
	public static function loadEnvironmentMonkey() : \damix\engines\monkey\MonkeyEnvironment
    {
		$MonkeyEnvironment = new \damix\engines\monkey\MonkeyEnvironment();
        
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'drivers' . DIRECTORY_SEPARATOR . 'monkey' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR;
        $dir = $dir2 = \damix\application::getPathApp() . 'plugins' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'monkey' . DIRECTORY_SEPARATOR;
       
        $liste = self::loadMonkeyDriver($dir);
        
        foreach( $liste as $fct)
        {
            include_once $fct['fullpath'];
            
            $plugin = '\damix\engines\template\drivers\MonkeyFunction' . ucfirst($fct['name']);
			
            $MonkeyEnvironment->Set( strtolower($fct['name']), new \damix\engines\monkey\MonkeyBuiltin(
                                array( 
                                    new $plugin(), 
                                    'Execute' )
                                    )
                );
        }
        return $MonkeyEnvironment;
    }
    
    private static function loadMonkeyDriver( string $dir ) : array
    {
		if( !is_dir( $dir ) )
		{
			return array();
		}
        $directories = scandir( $dir );
        $out = array();
        foreach( $directories as $elt )
        {
            if( $elt != '.' && $elt != '..' )
            {
                if( is_dir( $dir . DIRECTORY_SEPARATOR . $elt ) )
                {
                    $out = array_merge( $out, self::loadMonkeyDriver( $dir . DIRECTORY_SEPARATOR . $elt ) );
                }
                else
                {
                    if( preg_match( '/^([a-zA-Z0-9]*)\.plugin\.php$/', $elt, $match ) )
                    {
                        $name = $match[1];
                        $out[ $name ] = array( 
                            'name' => $name, 
                            'load' => false, 
                            'fullpath' => $dir . $elt,
                            );
                    }
                }
            }
        }
        
        return $out;
    }
}