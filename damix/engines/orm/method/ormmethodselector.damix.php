<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

namespace damix\engines\orm\method;


class OrmMethodSelector
	extends \damix\engines\orm\OrmSelector
{
    protected array $_partselector = array( 'module', 'resource', 'function' );
    protected ?\damix\engines\orm\OrmBaseFactory $_factory = null;
    
    public function setFactory( \damix\engines\orm\OrmBaseFactory $c )
    {
        $this->_factory = $c;
    }
    
    public function getFactory() : \damix\engines\orm\OrmBaseFactory
    {
        return $this->_factory;
    }
    
    public function getTempPath() : string
    {
        $dir = implode( DIRECTORY_SEPARATOR, $this->_part );
        
        
        $filename = $this->_directorytemp . '..' . DIRECTORY_SEPARATOR . 'donotdelete' . DIRECTORY_SEPARATOR . 'orm' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'methods' . DIRECTORY_SEPARATOR . $this->getClassName() . $this->_extensiontemp;
        
        return strtolower($filename);
    }
    
    public function getTempPathExecute() : string
    {
        $dir = implode( DIRECTORY_SEPARATOR, $this->_part );
        
        $filename = $this->_directorytemp . '..' . DIRECTORY_SEPARATOR . 'donotdelete' . DIRECTORY_SEPARATOR . 'orm' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'optz' . DIRECTORY_SEPARATOR . $this->getPart( 'module' ) .'_' . $this->getPart( 'resource' ) .'_' . $this->getPart( 'function' ) . '_' . $this->getHashCode() . $this->_extensiontemp;
        
		// \damix\engines\logs\log::log( $this->_factory->getConnection()->getDatabase() );
		// \damix\engines\logs\log::log( $filename );
        
        return strtolower($filename);
    }
    
    public function getClassName() : string
    {
        return 'cOrmMethod_'. $this->getPart( 'module' ) .'_' . $this->getPart( 'resource' ) .'_' . $this->getPart( 'function' );
    }
    
    public function getHashCode() : string
    {
        $hash = array();
        if( $this->_factory )
        {
            $hash[] = $this->_factory->getConnection()->getDatabase();
            $conditionsall = $this->_factory->getConditionsAll( $this->getPart( 'function' ) );
		
			foreach( $conditionsall as $conditions )
			{
				$hash[] = $conditions->getHashData();
			}
			$group = $this->_factory->getGroups( $this->getPart( 'function' ) );
            $hash[] = $group->getHashData();
            $order = $this->_factory->getOrders( $this->getPart( 'function' ) );
            $hash[] = $order->getHashData();
            $limit = $this->_factory->getLimits( $this->getPart( 'function' ) );
            $hash[] = $limit->getHashData();
        }
		$hash[] = $this->getPart( 'module' );
		$hash[] = $this->getPart( 'resource' );
		$hash[] = $this->getPart( 'function' );
		// \damix\engines\logs\log::dump( $hash );
		return md5( serialize( $hash ) );
    }
    
    public function getExecuteClassName() : string
    {
        return 'cOrmExecuteMethod_'. $this->getPart( 'module' ) .'_' . $this->getPart( 'resource' ) .'_' . $this->getPart( 'function' ) . $this->getHashCode();
    }
    
}