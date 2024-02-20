<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\stored;


class OrmStored
{
    static protected $_singleton=array();
    
    public static function getProcedures() : OrmStoredBase
    {
        $sel = new OrmStoredSelector(OrmStoredSelector::STORAGE_PROCEDURE);
		
		if(! isset(OrmStored::$_singleton['procedure'])){
			OrmStored::$_singleton['procedure'] = OrmStored::create( $sel );
		}
		return OrmStored::$_singleton['procedure'];
    }
    
    public static function getFunctions() : OrmStoredBase
    {
        $sel = new OrmStoredSelector(OrmStoredSelector::STORAGE_FUNCTION);
		
		if(! isset(OrmStored::$_singleton['function'])){
			OrmStored::$_singleton['function'] = OrmStored::create( $sel );
		}
		return OrmStored::$_singleton['function'];
    }
    
    public static function getFunctionsSelector() : OrmStoredSelector
    {
        $sel = new OrmStoredSelector(OrmStoredSelector::STORAGE_FUNCTION);
		
		return $sel;
    }
    
    public static function getProceduresSelector() : OrmStoredSelector
    {
        $sel = new OrmStoredSelector(OrmStoredSelector::STORAGE_PROCEDURE);
		
		return $sel;
    }
    
    public static function geEventsSelector() : OrmStoredSelector
    {
        $sel = new OrmStoredSelector(OrmStoredSelector::STORAGE_EVENT);
		
		return $sel;
    }
    
    public static function getTriggersSelector() : OrmStoredSelector
    {
        $sel = new OrmStoredSelector(OrmStoredSelector::STORAGE_TRIGGER);
		
		return $sel;
    }

    private static function create( $selector ) : OrmStoredBase
    {
        $classname = '\\' . $selector->getFullNamespace();
        
		if( ! class_exists($classname,false ) )
        {
			OrmStoredCompiler::compile( $selector );
            $temp = $selector->getTempPath();
            require_once( $temp );
		}
        
        $obj = new $classname();
        return $obj;
    }
	
	public static function CreateFunctions() : bool
    {
        $sel = self::getFunctionsSelector();
        
        $sel->getFiles();
        $files = $sel->files;
        
        $dom = new \damix\engines\tools\xmldocument();
        foreach( $files as $file )
        {
            if( $dom->load( $file[ 'filename' ] ) )
            {
                $liste = $dom->xPath( '/storages/function' );
        
                foreach( $liste as $function )
                {
                    $content = array();
                    $params = array();
                    $parameters = $dom->xPath( 'parameters/parameter', $function );
                    $return = $dom->xPath( 'return/parameter', $function )->item( 0 );
                    
                    $obj = new \damix\engines\orm\request\OrmRequestStored();
					$schema = \damix\engines\orm\request\structure\OrmSchema::newSchema( $function->getAttribute( 'schema' ) );
                    $obj->setSchema( $schema );
                    $obj->setName( $function->getAttribute( 'name' ) );
                    $obj->setSqlType(\damix\engines\orm\request\structure\OrmStructureType::SQL_FUNCTION_STORED_DELETE);
                    foreach( $parameters as $parameter )
                    {
                        $field = new \damix\engines\orm\request\structure\OrmField();
                        $field->setRealName( $parameter->getAttribute( 'name' ) );
                        $field->setDatatype( \damix\engines\orm\request\structure\OrmDataType::cast( $parameter->getAttribute( 'type' ) ));
                        $field->setUnsigned(tobool( $parameter->getAttribute( 'unsigned' )));
                        $field->setSize( intval($parameter->getAttribute( 'size' ) ));
                        $field->setPrecision( intval($parameter->getAttribute( 'precision' ) ));
                        $obj->addParameter( $field );
                    }
                    
                    $field = new \damix\engines\orm\request\structure\OrmField();
                    $field->setDatatype( \damix\engines\orm\request\structure\OrmDataType::cast( $return->getAttribute( 'type' ) ));
					$field->setSize( intval($return->getAttribute( 'size' ) ));
					$field->setPrecision( intval($return->getAttribute( 'precision' ) ));
                    $field->setUnsigned(tobool( $return->getAttribute( 'unsigned' )));
                    $obj->setReturn( $field );
                    $obj->setContent( file_get_contents( $sel->getPathContent( $function->getAttribute( 'name' ) ) ) );
					$obj->executeNonQuery();
					
                    $obj->setSqlType(\damix\engines\orm\request\structure\OrmStructureType::SQL_FUNCTION_STORED_CREATE);
                    $obj->executeNonQuery();
                }
            }
        }
		
		return true;
    }
	
	public static function CreateProcedures() : bool
    {
		$sel = self::getProceduresSelector();
        
        $sel->getFiles();
        $files = $sel->files;
        
        $dom = new \damix\engines\tools\xmldocument();
        foreach( $files as $file )
        {
            if( $dom->load( $file[ 'filename' ] ) )
            {
                $liste = $dom->xPath( '/storages/procedure' );
        
                foreach( $liste as $function )
                {
                    $content = array();
                    $params = array();
                    $parameters = $dom->xPath( 'parameters/parameter', $function );
                    
                    $obj = new \damix\engines\orm\request\OrmRequestStored();
					$schema = \damix\engines\orm\request\structure\OrmSchema::newSchema( $function->getAttribute( 'schema' ) );
                    $obj->setSchema( $schema );
                    $obj->setName( $function->getAttribute( 'name' ) );
                    $obj->setSqlType(\damix\engines\orm\request\structure\OrmStructureType::SQL_PROCEDURE_STORED_DELETE);
                    foreach( $parameters as $parameter )
                    {
                        $field = new \damix\engines\orm\request\structure\OrmField();
                        $field->setRealName( $parameter->getAttribute( 'name' ) );
                        $field->setDatatype( \damix\engines\orm\request\structure\OrmDataType::cast( $parameter->getAttribute( 'type' ) ));
                        $field->setUnsigned(tobool( $parameter->getAttribute( 'unsigned' )));
                        $field->setSize( intval($parameter->getAttribute( 'size' ) ));
                        $field->setPrecision( intval($parameter->getAttribute( 'precision' ) ));
                        $obj->addParameter( $field );
                    }
                    // \damix\engines\logs\log::log( $sel->getPathContent( $function->getAttribute( 'name' ) ) );
                    $obj->setContent( file_get_contents( $sel->getPathContent( $function->getAttribute( 'name' ) ) ) );
					$obj->executeNonQuery();
					
                    $obj->setSqlType(\damix\engines\orm\request\structure\OrmStructureType::SQL_PROCEDURE_STORED_CREATE);
                    $obj->executeNonQuery();
                }
            }
        }
        
		return true;
    }
	
	public static function CreateEvents() : bool
    {
        $sel = self::geEventsSelector();
        
        $sel->getFiles();
        $files = $sel->files;
        
        $dom = new \damix\engines\tools\xmldocument();
        foreach( $files as $file )
        {
            if( $dom->load( $file[ 'filename' ] ) )
            {
                $liste = $dom->xPath( '/storages/event' );
        
                foreach( $liste as $event )
                {
                    $content = array();
                    $params = array();
                    $parameters = $dom->xPath( 'parameters/parameter', $event );
                    
                    $obj = new \damix\engines\orm\request\OrmRequestStored();
                    $obj->setName( $event->getAttribute( 'name' ) );
                    $obj->setSqlType(\damix\engines\orm\request\structure\OrmStructureType::SQL_EVENT_STORED_DELETE);
					$obj->setIntervalValeur( $event->getAttribute( 'intervalvaleur' ) );
                    $obj->setIntervalUnite( $event->getAttribute( 'intervalunite' ) );
					
                    // \damix\engines\logs\log::log( $sel->getPathContent( $function->getAttribute( 'name' ) ) );
                    $obj->setContent( file_get_contents( $sel->getPathContent( $event->getAttribute( 'name' ) ) ) );
					$obj->executeNonQuery();
					
                    $obj->setSqlType(\damix\engines\orm\request\structure\OrmStructureType::SQL_EVENT_STORED_CREATE);
                    $obj->executeNonQuery();
                }
            }
        }
		
		
		return true;
    }
	
	public static function CreateTrigger() : bool
    {
        $ormdefinesbase = \damix\engines\orm\defines\OrmDefines::get();
		
		foreach( $ormdefinesbase->getDefines() as $define )
		{
			$orm = \damix\engines\orm\Orm::get( $define['selector'] );
			$orm->createTrigger();
		}
        
		return true;
    }
}