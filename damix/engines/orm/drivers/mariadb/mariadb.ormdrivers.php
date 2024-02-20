<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\drivers;


class OrmDriversMariadb
    extends OrmDriversBase
{
	public function isSchema() : bool 
	{
		return false;
	}
	
    protected function getRequestSQLCreateOption(\damix\engines\orm\request\structure\OrmTable $table) : string
	{
		$out = array();
		
		$engine = $table->getOption('mariadb', 'engine');
		if( ! empty( $engine ) )
		{
			$out[] = 'ENGINE=' . $engine['value'] ;
		}
		else
		{
			$engine = $this->_cnx->getProfileValue('engine');
			if( ! empty( $engine ) )
			{
				$out[] = 'ENGINE=' . $engine ;
			}
		}
		
		$charset = $table->getOption('mariadb', 'charset');
		if( ! empty( $charset ) )
		{
			$out[] = 'CHARSET=' . $charset['value'] ;
		}
		else
		{
			$charset = $this->_cnx->getProfileValue('charset');
			if( ! empty( $charset ) )
			{
				$out[] = 'CHARSET=' . $charset ;
			}
		}
		
        $out[] = 'ROW_FORMAT=DYNAMIC';
		
		return trim(implode(' ', $out ));
	}
	
	public function getRequestSQLCreateTriggerHeader(\damix\engines\orm\request\OrmRequestStored $stored) : string
    {
		$stored = $this->request;
		
        $table = $stored->getTable();
		
		$event = match($stored->getEvent())		
			{
				\damix\engines\orm\request\structure\OrmTriggerEvent::ORM_BEFORE => 'BEFORE',
				\damix\engines\orm\request\structure\OrmTriggerEvent::ORM_AFTER => 'AFTER',
			};
		
		$action = match($stored->getAction())	
			{
				\damix\engines\orm\request\structure\OrmTriggerAction::ORM_INSERT => 'INSERT',
				\damix\engines\orm\request\structure\OrmTriggerAction::ORM_UPDATE => 'UPDATE',
				\damix\engines\orm\request\structure\OrmTriggerAction::ORM_DELETE => 'DELETE',
			};
		
        $sql = array();
        $sql[] = 'CREATE TRIGGER '. $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector();
        $sql[] = $event . ' '. $action .' ON '. $this->getTableName( $table );
        $sql[] = 'FOR EACH ROW';
      
        $sql = implode("\n", $sql );
        return $sql;
    }
	
	
	protected function getRequestSQLCreateProcedureHeader(\damix\engines\orm\request\OrmRequestStored $stored) : string
	{
		$sql = array();
		$sql[] = 'CREATE PROCEDURE ' . $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector() . '(';
        foreach( $stored->getParameters() as $field )
        {
			$sqlparams[] = $this->getFieldProtector() . $field->getRealname() . $this->getFieldProtector() . ' ' . $this->getFieldDatatype($field);
        }
        
        $sql[] = implode(', ', $sqlparams );
        
        $sql[] = ')';
		$sql[] = 'DETERMINISTIC';
		
		$sql = implode("\n", $sql );
        return $sql;
	}
	
	protected function getRequestSQLCreateFunctionHeader(\damix\engines\orm\request\OrmRequestStored $stored) : string
	{
		$sql = array();
		$sql[] = 'CREATE FUNCTION ' . $this->getFieldProtector() . $stored->getName() . $this->getFieldProtector() . '(';
        foreach( $stored->getParameters() as $field )
        {
			$sqlparams[] = $this->getFieldProtector() . $field->getRealname() . $this->getFieldProtector() . ' ' . $this->getFieldDatatype($field);
        }
        
        $sql[] = implode(', ', $sqlparams );
        
        $sql[] = ')';
        
        $sql[] = 'RETURNS ' . $this->getFieldDatatype( $stored->getReturn() );
        $sql[] = 'DETERMINISTIC ';
		
		$sql = implode("\n", $sql );
        return $sql;
	}
}