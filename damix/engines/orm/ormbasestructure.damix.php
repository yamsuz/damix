<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm;

class OrmBaseStructure
{
	public string $name = '';
    public string $realname = '';
    public string $schema = '';
    public bool $versionning = false;
    public OrmStructureSelector $selector;
	
	protected array $_properties = array();
    protected array $_foreignkeys = array();
    protected array $_triggers = array();
    protected array $_events = array();
    protected array $_indexes = array();
    protected array $_options = array();
	
	public function __construct()
    {
    }
	
	public function getProperty( $name ) : array|null
    {
        return $this->_properties[ $name ] ?? null;
    }

    public function getProperties() : array
    {
        return $this->_properties;
    }
    public function getIndexes() : array
    {
        return $this->_indexes;
    }
    public function getForeignKeys() : array
    {
        return $this->_foreignkeys;
    }
    public function getTriggers() : array
    {
        return $this->_triggers;
    }
    public function getEvents() : array
    {
        return $this->_events;
    }
	
	public function getTable() : \damix\engines\orm\request\structure\OrmTable
	{
		$table = \damix\engines\orm\request\structure\OrmTable::newTable($this->realname);
		$schema = \damix\engines\orm\request\structure\OrmSchema::newSchema( $this->schema );
		$schema->addTable( $table );

		foreach( $this->getProperties() as $property )
		{
			$field = new \damix\engines\orm\request\structure\OrmField();
			$field->setName($property['name']);
			$field->setRealname($property['realname']);
			$field->setDatatype(\damix\engines\orm\request\structure\OrmDataType::cast($property['datatype']));
			$field->setSize($property['size']);
			$field->setPrecision($property['precision']);
			$field->setNull($property['null']);
			$field->setAutoincrement( $property['autoincrement'] );
			$field->setUnsigned( $property['unsigned'] );
			$field->setDefault( $property['default'] );
			$field->setEnumerate( preg_split('/;/', $property['enumerate'] ) );
			$table->addField($field);
		}
		
		foreach( $this->primarykey as $pk )
		{
			$table->addPrimaryKey( $table->getField( $pk ) );
		}
		$table->setReference( $this->selector->getPart( 'module' ) . '~' . $this->selector->getPart( 'resource' ) );
		$table->setOptions($this->_options);
		// \damix\engines\logs\log::dump( $this  );
		
		return $table;
	}
}