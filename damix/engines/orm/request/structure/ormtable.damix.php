<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmTable
	extends OrmStructure
{
    public string $name;
    public string $realname;
    public OrmSchema $schema;
	protected string $reference;
	protected string $internal;
    private array $options = array();
    private array $primarykey = array();
    private array $fields = array();
    private array $index = array();
	
	public function addPrimaryKey( OrmField $pk ) : void
	{
		$this->primarykey[] = $pk;
		$pk->setPrimaryKey( true );
	}
	
	public function setReference(string $value) : void
	{
		$this->reference = $value;
	}
	
	public function getReference() : string
	{
		return $this->reference;
	}
	
	public function isReference() : bool
	{
		return !empty( $this->reference );
	}
	
	public function setInternal(string $value) : void
	{
		$this->internal = $value;
	}
	
	public function getInternal() : string
	{
		return $this->internal ?? '';
	}
	
	public function isInternal() : bool
	{
		return !empty( $this->internal );
	}
	
	public function getPrimaryKey() : array
	{
		return $this->primarykey;
	}
	
	public function getIndex() : array
	{
		return $this->index;
	}
	
	public function addIndex(OrmIndex $index) : void
	{
		$this->index[] = $index;
	}
	
	public function setRealname(string $value) : void
	{
		if( ! empty( $value ) )
		{
			$this->realname = $value;
		}
	}
	public function getRealname() : string
	{
		return $this->realname;
	}
	
	public function setName(string $value) : void
	{
		$this->name = $value;
		if( empty( $this->realname ) )
		{
			$this->realname = $value;
		}
	}
	public function getName() : string
	{
		return $this->name;
	}
	
	public function setSchema(OrmSchema $schema) : void
	{
		$this->schema = $schema;
	}
	
	public function getSchema() : ?OrmSchema
	{
		return $this->schema ?? null;
	}
		
	public function addField(OrmField $field) : void
	{
		$field->setPosition( count( $this->fields ) );
		$field->setTable( $this );
		$this->fields[ $field->getName() ] = $field;
	}
		
	public function removeField(OrmField $field) : void
	{
		unset($this->fields[ $field->getName() ]);
	}
		
	public function getField(string $name) : ?OrmField
	{
		return $this->fields[ $name ] ?? null;
	}
	
	public function getFields() : array
	{
		return $this->fields;
	}
	
	public function getOption(string $driver, string $name) : array
	{
		if( isset( $this->options[$driver][ $name ] ))
		{
			return $this->options[$driver][ $name ];
		}
		if( isset( $this->options['*'][ $name ] ))
		{
			return $this->options['*'][ $name ];
		}
		return array();
	}
	
	public function setOption(string $name, string $value) : void
	{
		$this->options[ $name ] = $value;
	}
	
	public function setOptions(array $option) : void
	{
		$this->options = array_merge( $this->options, $option );
	}

	public static function newTable(string $name, string $realname = '', string $charset = '' ) : OrmTable
	{
		$table = new OrmTable();
		$table->setName($name);
		$table->setRealname($realname);
		return $table;
	}	
}