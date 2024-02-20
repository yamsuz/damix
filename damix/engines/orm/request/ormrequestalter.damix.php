<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request;


class OrmRequestAlter
	extends OrmRequest
{
    protected array $fields = array();
    protected array $indexes = array();
    protected array $constraints = array();
	
	
	public function __construct()
	{
		$this->sqltype = \damix\engines\orm\request\structure\OrmStructureType::SQL_ALTER;
		$this->structure = new \damix\engines\orm\request\structure\OrmTable();
	}
	
	public function getIndexes() : array
	{
		return $this->indexes;
	}
	
	public function getFields() : array
	{
		return $this->fields;
	}
	
	public function getConstraints() : array
	{
		return $this->constraints;
	}
	
	public function fieldAdd(\damix\engines\orm\request\structure\OrmField $field, ?string $after = null)
	{
		$this->fields[] = array( 'type' => 'add', 'field' => $field, 'after' => $after );
	}
	
	public function fieldModify(\damix\engines\orm\request\structure\OrmField $field, ?string $after = null)
	{
		$this->fields[] = array( 'type' => 'modify', 'field' => $field, 'after' => $after);
	}
	
	public function fieldDelete(\damix\engines\orm\request\structure\OrmField $field)
	{
		$this->fields[] = array( 'type' => 'delete', 'field' => $field, 'after' => null );
	}
	
	public function IndexAdd(\damix\engines\orm\request\structure\OrmIndex $index)
	{
		$this->indexes[] = array( 'type' => 'add', 'index' => $index );
	}
	
	public function IndexRemove(\damix\engines\orm\request\structure\OrmIndex $index)
	{
		$this->indexes[] = array( 'type' => 'delete', 'index' => $index );
	}
	
	public function clearAll()
	{
		$this->fields = array();
		$this->indexes = array();
		$this->constraints = array();
	}
	
	public function ContraintAdd(\damix\engines\orm\request\structure\OrmContraint $constraint)
	{
		$this->constraints[] = array( 'type' => 'add', 'constraint' => $constraint );
	}
	
	public function ContraintRemove(\damix\engines\orm\request\structure\OrmContraint $constraint)
	{
		$this->constraints[] = array( 'type' => 'delete', 'constraint' => $constraint );
	}
}