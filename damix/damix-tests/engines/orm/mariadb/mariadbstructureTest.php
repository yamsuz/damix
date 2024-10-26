<?php
/**
* @package      damix
* @Module       damix-tests
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

use PHPUnit\Framework\TestCase;


/**
 * @coversDefaultClass \damix\engines\orm\request\ormrequest
 * @group mariadb
 * @group bdd
 */
final class MariadbStructureTest extends TestCase
{
	public static array $bdd;
	
	public static function setUpBeforeClass(): void
    {
		\damix\engines\settings\Setting::clear();
		self::$bdd = self::loadDataBase();
	}

    public static function tearDownAfterClass(): void
    {
        \damix\engines\settings\Setting::clear();
    }
	
	/**
     * @covers ::query
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\MariadbDbConnection
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversMariadb
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\request\OrmRequestCreate
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmIndex
	 * @covers \damix\engines\orm\request\structure\OrmSchema
	 * @covers \damix\engines\orm\request\structure\OrmStructure
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
	 * @coversNothing \damix\engines\databases\drivers\MariadbDbResultSet
	 */
    public function testMariaDbCreateTable(): void
    {
		$schema = self::$bdd['schema'];
		$table1 = self::$bdd['table1'];
		$fields1 = self::$bdd['fields1'];
		
		foreach( $fields1 as $field )
		{
			$table1->addField($field);
		}

		$table2 = self::$bdd['table2'];
		$fields2 = self::$bdd['fields2'];
		
		foreach( $fields2 as $field )
		{
			$table2->addField($field);
		}
		
		$create = new \damix\engines\orm\request\ormrequestcreate();
		$create->setTable( $table1 );
		$sql = $create->getSQL();
		
		$model = 'CREATE TABLE `table1` ( `id` bigint(20) unsigned AUTO_INCREMENT, `maChaine` varchar(255) NULL DEFAULT NULL, `monInt` int(10) NULL DEFAULT NULL, `maDate` date NULL DEFAULT NULL, `monTime` time NULL DEFAULT NULL, `maDatetime` datetime NULL DEFAULT NULL, `monDecimal` decimal(20,6) NULL DEFAULT NULL, `monNull` bit(1) NULL DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB CHARSET=UTF8 ROW_FORMAT=DYNAMIC;';
		$this->assertEquals($sql, $model);
		
		
		$create = new \damix\engines\orm\request\ormrequestcreate();
		$create->setTable( $table2 );
		$create->setIgnore( true );
		$sql = $create->getSQL();
		
		$model = 'CREATE TABLE IF NOT EXISTS `table2` ( `id` bigint(20) unsigned, PRIMARY KEY (`id`) ) ENGINE=InnoDB CHARSET=UTF8 ROW_FORMAT=DYNAMIC;';
		$this->assertEquals($sql, $model);
    }

	/**
     * @covers ::query
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\MariadbDbConnection
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversMariadb
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\request\OrmRequestCreate
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmIndex
	 * @covers \damix\engines\orm\request\structure\OrmSchema
	 * @covers \damix\engines\orm\request\structure\OrmStructure
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
	 * @coversNothing \damix\engines\databases\drivers\MariadbDbResultSet
	 */
    public function testMariaDbAlterTable(): void
    {
		$schema = self::$bdd['schema'];
		$table = self::$bdd['table1'];
		$fields1 = self::$bdd['fields1'];
		$fields2 = self::$bdd['fields2'];
		$indexes = self::$bdd['indexes'];
		
		$alter = new \damix\engines\orm\request\ormrequestalter();
		$alter->setTable( $table );
		
		foreach( $fields1 as $name => $field )
		{
			switch( $name )
			{
				case 'maChaine':
					$alter->fieldAdd($field);
					break;
				case 'monInt':
					$alter->fieldAdd($field, 'monNull');
					break;
				case 'maDate':
					$alter->fieldModify($field, 'monTime');
					break;
				case 'monTime':
					$alter->fieldModify($field, 'maChaine');
					break;
				case 'maDatetime':
					$alter->fieldDelete($field);
					break;
				case 'monDecimal':
					$alter->fieldDelete($field);
					break;
				case 'monNull':
					break;
			}
		}
		$sql = $alter->getSQL();
		
		$model = 'ALTER TABLE `table1` ADD `maChaine` varchar(255) NULL DEFAULT NULL, ADD `monInt` int(10) NULL DEFAULT NULL AFTER `monNull`, MODIFY `maDate` date NULL DEFAULT NULL AFTER `monTime`, MODIFY `monTime` time NULL DEFAULT NULL AFTER `maChaine`, DROP `maDatetime`, DROP `monDecimal`;';
		$this->assertEquals($sql, $model);
		
		
		$alter = new \damix\engines\orm\request\ormrequestalter();
		$alter->setTable( $table );
		foreach( $indexes as $index )
		{
			$alter->IndexAdd( $index );
		}
		
		$sql = $alter->getSQL();
		
		$model = 'ALTER TABLE `table1` ADD INDEX `IDX_monIndex` ( `maChaine` desc ), ADD INDEX `IDX_monIndex2` ( `maChaine` (10) asc, `monInt` asc );';
		$this->assertEquals($sql, $model);
				
		$alter = new \damix\engines\orm\request\ormrequestalter();
		$alter->setTable( $table );
		foreach( $indexes as $index )
		{
			$alter->IndexRemove( $index );
		}
		$sql = $alter->getSQL();
		
		$model = 'ALTER TABLE `table1` DROP INDEX IDX_monIndex, DROP INDEX IDX_monIndex2;';
		$this->assertEquals($sql, $model);
		
		
		$alter = new \damix\engines\orm\request\ormrequestalter();
		$contraint = new \damix\engines\orm\request\structure\OrmContraint();
		$alter->setTable( $table );
		$contraint->setName( 'FK_table1_monInt' );
		$contraint->setForeign( $fields1['monInt'] );
		$contraint->setReference( $fields2['id'] );
		$alter->ContraintAdd( $contraint );
		$sql = $alter->getSQL();
		
		$model = 'ALTER TABLE `table1` ADD CONSTRAINT `FK_table1_monInt` FOREIGN KEY (`monInt`) REFERENCES `table2` (`id`);';
		$this->assertEquals($sql, $model);
		
		
		$alter = new \damix\engines\orm\request\ormrequestalter();
		$contraint = new \damix\engines\orm\request\structure\OrmContraint();
		$alter->setTable( $table );
		$contraint->setName( 'FK_table1_monInt' );
		$contraint->setForeign( $fields1['monInt'] );
		$contraint->setReference( $fields2['id'] );
		$contraint->setUpdate( \damix\engines\orm\request\structure\OrmContraintType::ORM_CASCADE );
		$contraint->setDelete( \damix\engines\orm\request\structure\OrmContraintType::ORM_SETNULL );
		$alter->ContraintAdd( $contraint );
		$sql = $alter->getSQL();
		
		$model = 'ALTER TABLE `table1` ADD CONSTRAINT `FK_table1_monInt` FOREIGN KEY (`monInt`) REFERENCES `table2` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;';
		$this->assertEquals($sql, $model);
    }
	
	/**
     * @covers ::query
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\MariadbDbConnection
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversMariadb
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\request\OrmRequestCreate
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmIndex
	 * @covers \damix\engines\orm\request\structure\OrmSchema
	 * @covers \damix\engines\orm\request\structure\OrmStructure
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
	 * @coversNothing \damix\engines\databases\drivers\MariadbDbResultSet
	 */
    public function testMariaDbDropTable(): void
    {
		$schema = self::$bdd['schema'];
		$table = self::$bdd['table1'];
		$fields = self::$bdd['fields1'];
		
		
		$drop = new \damix\engines\orm\request\OrmRequestDrop();
		$drop->setTable('table1');
		$sql = $drop->getSQL();
		
		
		$model = 'DROP TABLE IF EXISTS `table1`;';
		$this->assertEquals($sql, $model);
		
    }
		
	/**
     * @covers ::query
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\MariadbDbConnection
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversMariadb
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\request\OrmRequestCreate
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmIndex
	 * @covers \damix\engines\orm\request\structure\OrmSchema
	 * @covers \damix\engines\orm\request\structure\OrmStructure
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
	 * @coversNothing \damix\engines\databases\drivers\MariadbDbResultSet
	 */
    public function testMariaDbInsert(): void
    {
		$schema = self::$bdd['schema'];
		$table = self::$bdd['table1'];
		$fields1 = self::$bdd['fields1'];
			
		
		$insert = new \damix\engines\orm\request\OrmRequestInsert();
		$insert->setTable( 'table1' );
		$insert->setIgnore( true);
		
		$row = $insert->newRow();
		$row->addValue( 'id', 'id', \damix\engines\orm\request\structure\OrmDataType::ORM_BIGINT, true );
		$row->addValue( 'maChaine', 'maChaine0', \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR, false );
		$row->addValue( 'monInt', 15, \damix\engines\orm\request\structure\OrmDataType::ORM_INT, false );
		$row->addValue( 'maDate', new \damix\engines\tools\xDate('2023-04-19 15:07:42'), \damix\engines\orm\request\structure\OrmDataType::ORM_DATE, false );
		$row->addValue( 'monTime', new \damix\engines\tools\xDate('2023-04-19 15:07:42'), \damix\engines\orm\request\structure\OrmDataType::ORM_TIME, false );
		$row->addValue( 'maDatetime', new \damix\engines\tools\xDate('2023-04-19 15:07:42'), \damix\engines\orm\request\structure\OrmDataType::ORM_DATETIME, false);
		$row->addValue( 'monDecimal', 140.16, \damix\engines\orm\request\structure\OrmDataType::ORM_DECIMAL, false);
		$row->addValue( 'monNull', false, \damix\engines\orm\request\structure\OrmDataType::ORM_BOOL, false );
		$insert->addRow($row);
	
		$row = $insert->newRow();
		$row->addValue( 'id', 'id', \damix\engines\orm\request\structure\OrmDataType::ORM_BIGINT, true );
		$row->addValue( 'maChaine', 'maChaine1', \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR, false );
		$row->addValue( 'monInt', 16, \damix\engines\orm\request\structure\OrmDataType::ORM_INT, false );
		$row->addValue( 'maDate', new \damix\engines\tools\xDate('2023-04-19 15:07:42'), \damix\engines\orm\request\structure\OrmDataType::ORM_DATE, false );
		$row->addValue( 'monTime', new \damix\engines\tools\xDate('2023-04-19 15:07:42'), \damix\engines\orm\request\structure\OrmDataType::ORM_TIME, false );
		$row->addValue( 'maDatetime', new \damix\engines\tools\xDate('2023-04-19 15:07:42'), \damix\engines\orm\request\structure\OrmDataType::ORM_DATETIME, false);
		$row->addValue( 'monDecimal', null, \damix\engines\orm\request\structure\OrmDataType::ORM_DECIMAL, false);
		$row->addValue( 'monNull', true, \damix\engines\orm\request\structure\OrmDataType::ORM_BOOL, false );
		$insert->addRow($row);
	
		$sql = $insert->getSQL();
		
		$model = 'INSERT IGNORE INTO `table1` ( `id`, `maChaine`, `monInt`, `maDate`, `monTime`, `maDatetime`, `monDecimal`, `monNull` ) VALUES (NULL, \'maChaine0\', \'15\', \'2023-04-19\', \'15:07:42\', \'2023-04-19 15:07:42\', \'140.16\', b\'0\'), (NULL, \'maChaine1\', \'16\', \'2023-04-19\', \'15:07:42\', \'2023-04-19 15:07:42\', NULL, b\'1\');';
		$this->assertEquals($sql, $model);
	}
		
	/**
     * @covers ::query
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\MariadbDbConnection
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversMariadb
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\request\OrmRequestCreate
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmIndex
	 * @covers \damix\engines\orm\request\structure\OrmSchema
	 * @covers \damix\engines\orm\request\structure\OrmStructure
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
	 * @coversNothing \damix\engines\databases\drivers\MariadbDbResultSet
	 */
    public function testMariaDbUpdate(): void
    {
		$schema = self::$bdd['schema'];
		$table = self::$bdd['table1'];
		$fields1 = self::$bdd['fields1'];
			
		
		$update = new \damix\engines\orm\request\OrmRequestUpdate();
		$update->setTable( 'table1' );
		
		$update->addValue( 'maChaine', 'maNouvelleChaine', \damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR, false); 
		$update->addValue( 'monInt', 14, \damix\engines\orm\request\structure\OrmDataType::ORM_INT, false); 
		$c = $update->getConditions();
		$c->addGroupBegin();
		$c->addInt( 'id', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 1);
		$c->addString( 'maChaine', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'maChaine');
		$c->addGroupEnd();
	
		$sql = $update->getSQL();
		
		$model = 'UPDATE `table1` SET `maChaine` = \'maNouvelleChaine\', `monInt` = \'14\' WHERE ( `id` = \'1\' AND `maChaine` = \'maChaine\' );';
		$this->assertEquals($sql, $model);
		
	}
	
	/**
     * @covers ::query
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\MariadbDbConnection
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversMariadb
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\request\OrmRequestCreate
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmIndex
	 * @covers \damix\engines\orm\request\structure\OrmSchema
	 * @covers \damix\engines\orm\request\structure\OrmStructure
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
	 * @coversNothing \damix\engines\databases\drivers\MariadbDbResultSet
	 */
    public function testMariaDbDelete(): void
    {
		$schema = self::$bdd['schema'];
		$table = self::$bdd['table1'];
		$fields1 = self::$bdd['fields1'];
			
		
		$delete = new \damix\engines\orm\request\OrmRequestDelete();
		$delete->setTable( 'table1' );
		
		$c = $delete->getConditions();
		$c->addGroupBegin();
		$c->addInt( 'id', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 1);
		$c->addString( 'maChaine', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'maChaine');
		$c->addGroupEnd();
	
		$sql = $delete->getSQL();
		
		$model = 'DELETE FROM `table1` WHERE ( `id` = \'1\' AND `maChaine` = \'maChaine\' );';
		$this->assertEquals($sql, $model);
		
	}
	
	/**
     * @covers ::query
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\MariadbDbConnection
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversMariadb
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\request\OrmRequestCreate
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmIndex
	 * @covers \damix\engines\orm\request\structure\OrmSchema
	 * @covers \damix\engines\orm\request\structure\OrmStructure
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
	 * @coversNothing \damix\engines\databases\drivers\MariadbDbResultSet
	 */
    public function testMariaDbSelect(): void
    {
		$schema = self::$bdd['schema'];
		$table = self::$bdd['table1'];
		$fields1 = self::$bdd['fields1'];
		
		$select = new \damix\engines\orm\request\OrmRequestSelect();
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$column->setColumnField( '', 'table1', 'maChaine' );
		$select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'length', 'longueur' );
		$formula->addParameterField( 'table1', 'maChaine' );
		$select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'length', 'stringValue' );
		$value = new \damix\engines\orm\request\structure\OrmValue();
		$value->setValue('CeciEstUneChaine') ;
		$formula->addParameterValue( $value );
		$select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'left', 'fctLeft' );
		$value = new \damix\engines\orm\request\structure\OrmValue();
		$value->setValue('CeciEstUneChaine') ;
		$formula->addParameterValue( $value );
		$value = new \damix\engines\orm\request\structure\OrmValue();
		$value->setValue(5) ;
		$formula->addParameterValue( $value );
		$c = $select->addDisplay($column);
		
		$table = \damix\engines\orm\request\structure\OrmTable::newTable( 'table1' );
		$join = $select->addJoin( 'from', $table, 'table1' );
		$table = \damix\engines\orm\request\structure\OrmTable::newTable( 'table2' );
		$c = $select->addJoin( 'left', $table, 'table2' );
		$c->addGroupBegin();
		$c->addConditionField('table1', 'monInt', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'table2', 'id');
		$c->addLogic( \damix\engines\orm\conditions\OrmOperator::ORM_OP_AND );
		$c->addConditionString('table1', 'monInt', \damix\engines\orm\conditions\OrmOperator::ORM_OP_GTEQ, '5');
		$c->addGroupEnd();
		
		$c = $select->getConditions();
		$c->addGroupBegin();
		$c->addInt( array( 'table' => 'table1', 'field' => 'id'), \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 1);
		$c->addString( 'maChaine', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'maChaine');
		$c->addGroupEnd();
		
		$column = new \damix\engines\orm\request\structure\OrmGroup();
		$column->setColumnField( '', 'table1', 'maChaine' );
		$select->addGroupBy($column);
		
		$order = new \damix\engines\orm\request\structure\OrmOrder();
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$column->setColumnField( '', 'table1', 'maChaine', 'maChaine' );
		$order->setColumn($column);
		$order->setWay(\damix\engines\orm\request\structure\OrmOrderWay::WAY_ASC);
		$select->addOrderBy($order);
	
	
		$h = $select->getHaving();
		$formula = $column->setColumnFormula( 'sum', 'mysum' );
		$formula->addParameterField( 'table1', 'monInt' );
		$h->addString($formula, \damix\engines\orm\conditions\OrmOperator::ORM_OP_GTEQ, '5');
		
		$sql = $select->getSQL();
		
		$model = 'SELECT `table1`.`maChaine` AS `maChaine`, LENGTH(`table1`.`maChaine`) AS `longueur`, LENGTH(\'CeciEstUneChaine\') AS `stringValue`, LEFT(\'CeciEstUneChaine\', \'5\') AS `fctLeft` FROM `table1` AS `table1` LEFT JOIN `table2` AS `table2` ON ( `table1`.`monInt` = `table2`.`id` AND `table1`.`monInt` >= \'5\' ) WHERE ( `table1`.`id` = \'1\' AND `maChaine` = \'maChaine\' ) GROUP BY `table1`.`maChaine` HAVING SUM(`table1`.`monInt`) >= \'5\' ORDER BY `table1`.`maChaine` ASC;';
		$this->assertEquals($sql, $model);
		
	}
	
	/**
     * @covers ::getSQL
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\MariadbDbConnection
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversMariadb
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\request\OrmRequestCreate
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmIndex
	 * @covers \damix\engines\orm\request\structure\OrmSchema
	 * @covers \damix\engines\orm\request\structure\OrmStructure
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
	 * @coversNothing \damix\engines\databases\drivers\MariadbDbResultSet
	 */
    public function testMariaDbSelectFonctionString(): void
    {
		$schema = self::$bdd['schema'];
		$table = self::$bdd['table1'];
		$fields1 = self::$bdd['fields1'];
		
		$select = new \damix\engines\orm\request\OrmRequestSelect();
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'right', 'droite' );
		$formula->addParameterField( 'table1', 'maChaine' );
		$formula->addParameterValue( 5 );
		$select->addDisplay($column);
		
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'left', 'gauche' );
		$formula->addParameterField( 'table1', 'maChaine' );
		$formula->addParameterValue( 5 );
		$select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'lower', 'minuscule' );
		$formula->addParameterField( 'table1', 'maChaine' );
		$select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'upper', 'majuscule' );
		$formula->addParameterField( 'table1', 'maChaine' );
		$select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'length', 'longueur' );
		$formula->addParameterField( 'table1', 'maChaine' );
		$select->addDisplay($column);
		
		$table = \damix\engines\orm\request\structure\OrmTable::newTable( 'table1' );
		$join = $select->addJoin( 'from', $table, 'table1' );
		
		$sql = $select->getSQL();
		
		$model = 'SELECT RIGHT(`table1`.`maChaine`, \'5\') AS `droite`, LEFT(`table1`.`maChaine`, \'5\') AS `gauche`, LOWER(`table1`.`maChaine`) AS `minuscule`, UPPER(`table1`.`maChaine`) AS `majuscule`, LENGTH(`table1`.`maChaine`) AS `longueur` FROM `table1` AS `table1`;';
		$this->assertEquals($sql, $model);
	}
	
	/**
     * @covers ::getSQL
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\MariadbDbConnection
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversMariadb
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\request\OrmRequestCreate
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmIndex
	 * @covers \damix\engines\orm\request\structure\OrmSchema
	 * @covers \damix\engines\orm\request\structure\OrmStructure
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
	 * @coversNothing \damix\engines\databases\drivers\MariadbDbResultSet
	 */
    public function testMariaDbSelectFonctionNumeric(): void
    {
		$schema = self::$bdd['schema'];
		$table = self::$bdd['table1'];
		$fields1 = self::$bdd['fields1'];
		
		$select = new \damix\engines\orm\request\OrmRequestSelect();
		
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'round', 'arrondi' );
		$formula->addParameterField( 'table1', 'monDecimal' );
		$formula->addParameterValue( 5 );
		$select->addDisplay($column);
		
		$table = \damix\engines\orm\request\structure\OrmTable::newTable( 'table1' );
		$join = $select->addJoin( 'from', $table, 'table1' );
		
		$sql = $select->getSQL();
		
		$model = 'SELECT ROUND(`table1`.`monDecimal`, \'5\') AS `arrondi` FROM `table1` AS `table1`;';
		$this->assertEquals($sql, $model);
	}
	
	/**
     * @covers ::query
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\MariadbDbConnection
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversMariadb
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\request\OrmRequestCreate
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmIndex
	 * @covers \damix\engines\orm\request\structure\OrmSchema
	 * @covers \damix\engines\orm\request\structure\OrmStructure
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
	 * @coversNothing \damix\engines\databases\drivers\MariadbDbResultSet
	 */
    public function testMariaDbSelectFonctionDatetime(): void
    {
		$schema = self::$bdd['schema'];
		$table = self::$bdd['table1'];
		$fields1 = self::$bdd['fields1'];
		
		$select = new \damix\engines\orm\request\OrmRequestSelect();
		
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'adddate', 'add5day' );
		$formula->addParameterField( 'table1', 'maDate' );
		$formula->addParameterValue( 5 );
		$formula->addParameterValue( 'DAY' );
		$c = $select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'adddate', 'add1day' );
		$formula->addParameterValue( new \damix\engines\tools\xDate('2023-04-19 15:07:42') );
		$formula->addParameterValue( 1 );
		$formula->addParameterValue( 'DAY' );
		$c = $select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'now', 'nowdate' );
		$c = $select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'curdate', 'currentdate' );
		$c = $select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'datediff', 'diffdate' );
		$formula->addParameterValue( new \damix\engines\tools\xDate('2023-04-19 15:07:42') );
		$formula->addParameterField( 'table1', 'maDate' );
		$c = $select->addDisplay($column);
		
		$table = \damix\engines\orm\request\structure\OrmTable::newTable( 'table1' );
		$join = $select->addJoin( 'from', $table, 'table1' );
		
		$sql = $select->getSQL();
		
		$model = 'SELECT ADDDATE(`table1`.`maDate`, INTERVAL \'5\' DAY) AS `add5day`, ADDDATE(\'2023-04-19 15:07:42\', INTERVAL \'1\' DAY) AS `add1day`, NOW() AS `nowdate`, CURDATE() AS `currentdate`, DATEDIFF(\'2023-04-19 15:07:42\', `table1`.`maDate`) AS `diffdate` FROM `table1` AS `table1`;';
		$this->assertEquals($sql, $model);
		
	}
	
	
	/**
     * @covers ::query
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\MariadbDbConnection
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversMariadb
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\request\OrmRequestCreate
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmIndex
	 * @covers \damix\engines\orm\request\structure\OrmSchema
	 * @covers \damix\engines\orm\request\structure\OrmStructure
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
	 * @coversNothing \damix\engines\databases\drivers\MariadbDbResultSet
	 */
    public function testMariaDbSelectFonctionControl(): void
    {
		$schema = self::$bdd['schema'];
		$table = self::$bdd['table1'];
		$fields1 = self::$bdd['fields1'];
		
		$select = new \damix\engines\orm\request\OrmRequestSelect();
		
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'if', 'si' );
		$formula->addParameterField( 'table1', 'monInt' );
		$formula->addParameterOperator( \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ );
		$formula->addParameterValue( 5 );
		$formula->addParameterField( 'table1', 'monInt' );
		$formula->addParameterField( 'table1', 'monDecimal' );
		$c = $select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'ifnull', 'ifisnull' );
		$formula->addParameterField( 'table1', 'monInt' );
		$formula->addParameterField( 'table1', 'monDecimal' );
		$c = $select->addDisplay($column);

		$table = \damix\engines\orm\request\structure\OrmTable::newTable( 'table1' );
		$join = $select->addJoin( 'from', $table, 'table1' );
		
		$sql = $select->getSQL();
		
		$model = 'SELECT IF(`table1`.`monInt` = \'5\', `table1`.`monInt`, `table1`.`monDecimal`) AS `si`, IFNULL(`table1`.`monInt`, `table1`.`monDecimal`) AS `ifisnull` FROM `table1` AS `table1`;';
		$this->assertEquals($sql, $model);
		
	}
	
	/**
     * @covers ::query
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\MariadbDbConnection
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversMariadb
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\request\OrmRequestCreate
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmIndex
	 * @covers \damix\engines\orm\request\structure\OrmSchema
	 * @covers \damix\engines\orm\request\structure\OrmStructure
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
	 * @coversNothing \damix\engines\databases\drivers\MariadbDbResultSet
	 */
    public function testMariaDbSelectFonctionAgregate(): void
    {
		$schema = self::$bdd['schema'];
		$table = self::$bdd['table1'];
		$fields1 = self::$bdd['fields1'];
		
		$select = new \damix\engines\orm\request\OrmRequestSelect();
		
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'avg', 'myAVG' );
		$formula->addParameterField( 'table1', 'monDecimal' );
		$c = $select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'count', 'mycount' );
		$formula->addParameterField( 'table1', 'monInt' );
		$c = $select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'min', 'mymin' );
		$formula->addParameterField( 'table1', 'monInt' );
		$c = $select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'max', 'mymax' );
		$formula->addParameterField( 'table1', 'monInt' );
		$c = $select->addDisplay($column);
		
		$column = new \damix\engines\orm\request\structure\OrmColumn();
		$formula = $column->setColumnFormula( 'sum', 'mysum' );
		$formula->addParameterField( 'table1', 'monInt' );
		$c = $select->addDisplay($column);
		
		$table = \damix\engines\orm\request\structure\OrmTable::newTable( 'table1' );
		$join = $select->addJoin( 'from', $table, 'table1' );
		
		$sql = $select->getSQL();
		
		$model = 'SELECT AVG(`table1`.`monDecimal`) AS `myAVG`, COUNT(`table1`.`monInt`) AS `mycount`, MIN(`table1`.`monInt`) AS `mymin`, MAX(`table1`.`monInt`) AS `mymax`, SUM(`table1`.`monInt`) AS `mysum` FROM `table1` AS `table1`;';
		$this->assertEquals($sql, $model);
	}
	
	
	private static function loadDataBase() : array
	{
		$config = \damix\engines\settings\Setting::get('profile');
		
		$config->set( 'myprofiltest', 'database', 'mydatabase1' );
		$config->set( 'myprofiltest', 'driver', 'mariadb' );
		$config->set( 'myprofiltest', 'host', 'localhost' );
		$config->set( 'myprofiltest', 'port', '3306' );
		$config->set( 'myprofiltest', 'user', 'root' );
		$config->set( 'myprofiltest', 'password', '' );
		$config->set( 'myprofiltest', 'persistent', 'on' );
		$config->set( 'myprofiltest', 'force_encoding', 'on' );
		$config->set( 'myprofiltest', 'engine', 'InnoDB' );
		$config->set( 'myprofiltest', 'charset', 'UTF8' );
		$config->set( 'database', 'default', 'myprofiltest' );
		
		
		$fields1 = array();
		$fields2 = array();
		$indexes = array();
		$schema = \damix\engines\orm\request\structure\OrmSchema::newSchema('monschema');
		$table1 = \damix\engines\orm\request\structure\OrmTable::newTable('table1');
		$table2 = \damix\engines\orm\request\structure\OrmTable::newTable('table2');
		$schema->addTable( $table1 );
		$schema->addTable( $table2 );
		
		$field = new \damix\engines\orm\request\structure\OrmField();
		$field->setName('id');
		$field->setDatatype(\damix\engines\orm\request\structure\OrmDataType::ORM_BIGINT);
		$field->setNull( true );
		$field->setAutoincrement( true );
		$field->setUnsigned( true );
		$table1->addField($field);
		$table1->addPrimaryKey( $field );
		
		$field = new \damix\engines\orm\request\structure\OrmField();
		$field->setName('maChaine');
		$field->setDatatype(\damix\engines\orm\request\structure\OrmDataType::ORM_VARCHAR);
		$field->setSize( 255 );
		$field->setNull( true );
		$field->setDefault( null );		
		$fields1[$field->getName()] = $field;
		
		
		$field = new \damix\engines\orm\request\structure\OrmField();
		$field->setName('monInt');
		$field->setDatatype(\damix\engines\orm\request\structure\OrmDataType::ORM_INT);
		$field->setNull( true );
		$field->setDefault( null );		
		$fields1[$field->getName()] = $field;
		
		$field = new \damix\engines\orm\request\structure\OrmField();
		$field->setName('maDate');
		$field->setDatatype(\damix\engines\orm\request\structure\OrmDataType::ORM_DATE);
		$field->setNull( true );
		$field->setDefault( null );
		$fields1[$field->getName()] = $field;
		
		$field = new \damix\engines\orm\request\structure\OrmField();
		$field->setName('monTime');
		$field->setDatatype(\damix\engines\orm\request\structure\OrmDataType::ORM_TIME);
		$field->setNull( true );
		$field->setDefault( null );
		$fields1[$field->getName()] = $field;
		
		$field = new \damix\engines\orm\request\structure\OrmField();
		$field->setName('maDatetime');
		$field->setDatatype(\damix\engines\orm\request\structure\OrmDataType::ORM_DATETIME);
		$field->setNull( true );
		$field->setDefault( null );
		$fields1[$field->getName()] = $field;
		
		$field = new \damix\engines\orm\request\structure\OrmField();
		$field->setName('monDecimal');
		$field->setDatatype(\damix\engines\orm\request\structure\OrmDataType::ORM_DECIMAL);
		$field->setSize( 20 );
		$field->setPrecision( 6 );
		$field->setNull( true );
		$field->setDefault( null );
		$fields1[$field->getName()] = $field;
		
		$field = new \damix\engines\orm\request\structure\OrmField();
		$field->setName('monNull');
		$field->setDatatype(\damix\engines\orm\request\structure\OrmDataType::ORM_BOOL);
		$field->setNull( true );
		$field->setDefault( null );
		$fields1[$field->getName()] = $field;
		
		
		$index = new \damix\engines\orm\request\structure\OrmIndex();
		$index->setName( 'IDX_monIndex' );
		$index->setIndexType( \damix\engines\orm\request\structure\OrmIndexType::ORM_INDEX );
		$index->addField( $fields1[ 'maChaine' ], \damix\engines\orm\request\structure\OrmOrderWay::WAY_DESC );
		$indexes[] = $index;
		
		$index = new \damix\engines\orm\request\structure\OrmIndex();
		$index->setName( 'IDX_monIndex2' );
		$index->setIndexType( \damix\engines\orm\request\structure\OrmIndexType::ORM_INDEX );
		$index->addField( $fields1[ 'maChaine' ], \damix\engines\orm\request\structure\OrmOrderWay::WAY_ASC, 10 );
		$index->addField( $fields1[ 'monInt' ] );
		$indexes[] = $index;
		
		
		$field = new \damix\engines\orm\request\structure\OrmField();
		$field->setName('id');
		$field->setDatatype(\damix\engines\orm\request\structure\OrmDataType::ORM_BIGINT);
		$field->setNull( true );
		$field->setUnsigned( true );
		$table2->addField($field);
		$table2->addPrimaryKey( $field );
		$fields2[$field->getName()] = $field;
		
		
		return array( 'schema' => $schema, 'table1' => $table1, 'table2' => $table2, 'fields1' => $fields1, 'fields2' => $fields2, 'indexes' => $indexes);
	}
	
	
}

