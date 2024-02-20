<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm;

class Orm
{
	static protected $_singleton=array();
	
	public static function get( string $selector, string $profil = '' ) : \damix\engines\orm\OrmBaseFactory
    {
		$unique = $selector . $profil;
		
		if(! isset(Orm::$_singleton[$unique])){
            $sel = new OrmSelector( $selector );
			Orm::$_singleton[$unique] = Orm::create( $sel, $profil );
		}
		return Orm::$_singleton[$unique];
    }
	
	public static function inc( string $selector, string $profil = '' ) : void
    {
		$unique = $selector . $profil;
		
		if(! isset(Orm::$_singleton[$unique])){
            $sel = new OrmSelector( $selector );
			Orm::$_singleton[$unique] = Orm::create( $sel, $profil );
		}
    }
	
	public static function create( OrmSelector $selector, string $profil = ''  ) : \damix\engines\orm\OrmBaseFactory
    {
        $classname = '\\' . $selector->getNamespace() . '\\' . $selector->getFactoryClassName();
       
		if( ! class_exists($classname,false ) )
        {
			OrmCompiler::compile( $selector );
            $temp = $selector->getTempPath();
			if( file_exists( $temp ) )
			{
				include( realpath($temp) );
			}
			else
			{
				throw new \damix\core\exception\OrmException( 'Le fichier Orm n\'existe pas : ' . $selector->toString() );
			}
		}
        
        $obj = new $classname();
		$obj->setProfile( $profil );
        return $obj;
    }
	
	public static function getStructure( string $selector ) : OrmBaseStructure
    {
		if(! isset(Orm::$_singleton[ $selector.'struct']))
		{
			$sel = new OrmStructureSelector( $selector );
			Orm::$_singleton[$selector.'struct'] = Orm::createStucture( $sel );
		}
		return Orm::$_singleton[$selector.'struct'];
    }
	
	public static function createStucture( OrmStructureSelector $selector ) : OrmBaseStructure
    {
        $classname = $selector->getFullNamespace();
        
		if( ! class_exists( $classname, false ) )
        {
			OrmCompiler::compileStucture( $selector );
			
			if( file_exists( $selector->getTempPath() ) )
			{
				require_once( $selector->getTempPath() );
			}
		}
        
        $obj = new $classname();
        $obj->selector = $selector;
        return $obj;
    }
	
	public static function getDefine( string $value ) : array|null
    {
		if( preg_match( '/[{\[]([a-z0-9A-Z_]*)[}\]]:([a-z0-9A-Z_]*)/', $value, $out ) )
        {
            $define = \damix\engines\orm\defines\OrmDefines::get();

            $orm = \damix\engines\orm\Orm::getStructure( $define->get( $out[1] ) );
            
            $field = $orm->getProperty( $out[2] );
            
            return array( 
                'define' => $define,
                'orm' => $orm,
                'field' => $field,
            );
        }
		elseif( preg_match( '/^{([a-z0-9A-Z_]*)}$/', $value, $out ) )
        {
            $define = \damix\engines\orm\defines\OrmDefines::get();
            
            $orm = \damix\engines\orm\Orm::getStructure( $define->get( $out[1] ) );
            
            
            return array( 
                'define' => $define,
                'orm' => $orm,
                'field' => null,
            );
        }
		elseif( preg_match( '/^([a-z0-9A-Z_]*~[a-z0-9A-Z_]*)$/', $value, $out ) )
        {
            $orm = \damix\engines\orm\Orm::getStructure( $out[1]  );
            
            return array( 
                'orm' => $orm,
                'field' => null,
            );
        }
		elseif( preg_match( '/^([a-z0-9A-Z_]*~[a-z0-9A-Z_]*):([a-z0-9A-Z_]*)$/', $value, $out ) )
        {
            $orm = \damix\engines\orm\Orm::getStructure( $out[1]  );
            
            $field = $orm->getProperty( $out[2] );
            
            return array( 
                'orm' => $orm,
                'field' => $field,
            );
        }
		
        
        return null;
    }
}