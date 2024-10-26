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
 * @coversDefaultClass \damix\engines\orm\Orm
 * @group pgsql
 * @group bdd
 */
final class PgsqlMethodTest extends TestCase
{
	
	public static function setUpBeforeClass(): void
    {
		\damix\engines\databases\Db::clear();
		\damix\engines\settings\Setting::clear();
		self::loadDataBase();
	}

    public static function tearDownAfterClass(): void
    {
        \damix\engines\settings\Setting::clear();
		\damix\engines\databases\Db::clear();
    }
	
	private static function loadDataBase() : void
	{
		$config = \damix\engines\settings\Setting::get('profile');
		
		$config->set( 'myprofiltest', 'database', 'thinkpi' );
		$config->set( 'myprofiltest', 'driver', 'pgsql' );
		$config->set( 'myprofiltest', 'host', 'localhost' );
		$config->set( 'myprofiltest', 'port', '5432' );
		$config->set( 'myprofiltest', 'user', 'postgres' );
		$config->set( 'myprofiltest', 'password', 'Panini' );
		$config->set( 'myprofiltest', 'persistent', 'on' );
		$config->set( 'myprofiltest', 'force_encoding', 'on' );
		$config->set( 'myprofiltest', 'charset', 'UTF8' );
		$config->set( 'myprofiltest', 'schema', 'monschema' );
		$config->set( 'database', 'default', 'myprofiltest' );
	}

	/**
     * @covers ::getSQL
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\PgsqlDbConnection
	 * @covers \damix\engines\logs\Log
	 * @covers \damix\engines\logs\LogBase
	 * @covers \damix\engines\orm\Orm
	 * @covers \damix\engines\orm\OrmBaseFactory
	 * @covers \damix\engines\orm\OrmBaseProperties
	 * @covers \damix\engines\orm\OrmCompiler
	 * @covers \damix\engines\orm\OrmGenerator
	 * @covers \damix\engines\orm\OrmSelector
	 * @covers \damix\engines\orm\conditions\OrmCondition
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversPgsql
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\method\OrmMethod
	 * @covers \damix\engines\orm\method\OrmMethodCompiler
	 * @covers \damix\engines\orm\method\OrmMethodGenerator
	 * @covers \damix\engines\orm\method\OrmMethodSelector
	 * @covers \damix\engines\orm\request\OrmRequest
	 * @covers \damix\engines\orm\request\OrmRequestSelect
	 * @covers \damix\engines\orm\request\structure\OrmColumn
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmFrom
	 * @covers \damix\engines\orm\request\structure\OrmGroups
	 * @covers \damix\engines\orm\request\structure\OrmLimits
	 * @covers \damix\engines\orm\request\structure\OrmOrders
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\GeneratorContent
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xFile
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\tools\xmlDocument
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingSelector	 
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\logs\LogFile
	 * @covers \damix\engines\logs\LogMessage
	 * @covers \damix\engines\logs\LogSelector
	 * @covers \damix\engines\orm\OrmBaseStructure
	 * @covers \damix\engines\orm\OrmGeneratorStructure
	 * @covers \damix\engines\orm\OrmStructureSelector
	 * @covers \damix\engines\orm\defines\OrmDefines
	 * @covers \damix\engines\orm\defines\OrmDefinesBase
	 * @covers \damix\engines\orm\defines\OrmDefinesCompiler
	 * @covers \damix\engines\orm\defines\OrmDefinesGenerator
	 * @covers \damix\engines\orm\defines\OrmDefinesSelector
	 * @covers tobool
	 */
    public function testPgsqlOrmMethod(): void
    {
		// /** SELECT  */
		$orm = \damix\engines\orm\Orm::get('video~tormacteur' );
		$request = $orm->selectRequest();
		
		$cs = new \damix\engines\orm\conditions\OrmConditions();
		$cs->add( $orm->getConditions( 'select' ) );
		$sql = $request->execute($cs);
		$model = 'SELECT acteur.idacteur AS acteur_idacteur, acteur.nom AS acteur_nom, acteur.prenom AS acteur_prenom, acteur.naissance AS acteur_naissance, acteur.style AS acteur_style FROM thinkpi.monschema.acteur AS acteur;';
		$this->assertEquals($sql, $model);
		
		
	
		// /** GET  */
		$orm = \damix\engines\orm\Orm::get('video~tormacteur' );
		$c = $orm->getConditionsClear( 'get' );
        $c->addString( 'idacteur', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, '5' );
		$request = $orm->getRequest();
        $cs = new \damix\engines\orm\conditions\OrmConditions();
		$cs->add( $c );
		$sql = $request->execute($cs);
		$model = 'SELECT acteur.idacteur AS acteur_idacteur, acteur.nom AS acteur_nom, acteur.prenom AS acteur_prenom, acteur.naissance AS acteur_naissance, acteur.style AS acteur_style FROM thinkpi.monschema.acteur AS acteur WHERE idacteur = \'5\';';
		$this->assertEquals($sql, $model);
	}
	
	/**
     * @covers ::getSQL
	 */
    public function testPgsqlSOrmMethod(): void
    {
		/** SORM  */
		$orm = \damix\engines\orm\Orm::get('video~sormvideoliste:videoliste' );
		$c = $orm->getConditionsClear( 'videoliste' );
        $c->addString( 'titre', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, 'monFilm d\'aujourd\'hui' );
		$request = $orm->videolisteRequest();
		
		$cs = new \damix\engines\orm\conditions\OrmConditions();
		$cs->add( $c );
		$sql = $request->execute($cs);
		
		$model = 'SELECT film.idfilm AS film_idfilm, film.titre AS film_titre, film.annee AS film_annee, film.duree AS film_duree, film.resume AS film_resume, film.idgenre AS film_idgenre, film.sortiesalle AS film_sortiesalle, film.sortiedate AS film_sortiedate, film.interdit AS film_interdit, genre2.id AS genre2_idgenre, genre2.code AS genre2_code FROM thinkpi.monschema.film AS film LEFT JOIN thinkpi.monschema.genre AS genre2 ON genre2.id = film.idgenre WHERE titre = \'monFilm d\'\'aujourd\'\'hui\';';
		$this->assertEquals($sql, $model);
		
		
		$orm = \damix\engines\orm\Orm::get('video~sormvideoliste:videoliste' );
		$c = $orm->getConditionsClear( 'videoliste' );
        $c->addString( array( 'table' => 'film', 'field' => 'idfilm'), \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, '5' );
		$request = $orm->videolisteRequest();
		
		$cs = new \damix\engines\orm\conditions\OrmConditions();
		$cs->add( $c );
		$sql = $request->execute($cs);
		
		$model = 'SELECT film.idfilm AS film_idfilm, film.titre AS film_titre, film.annee AS film_annee, film.duree AS film_duree, film.resume AS film_resume, film.idgenre AS film_idgenre, film.sortiesalle AS film_sortiesalle, film.sortiedate AS film_sortiedate, film.interdit AS film_interdit, genre2.id AS genre2_idgenre, genre2.code AS genre2_code FROM thinkpi.monschema.film AS film LEFT JOIN thinkpi.monschema.genre AS genre2 ON genre2.id = film.idgenre WHERE film.idfilm = \'5\';';
		$this->assertEquals($sql, $model);
		
		
		$orm = \damix\engines\orm\Orm::get('video~sormvideoliste:statfilmacteur' );
		$c = $orm->getConditionsClear( 'statfilmacteur' );
        $c->addString( array( 'table' => 'film', 'field' => 'idfilm'), \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, '5' );
		$request = $orm->statfilmacteurRequest();
		$cs = new \damix\engines\orm\conditions\OrmConditions();
		$cs->add( $c );
		$sql = $request->execute( $cs );
		
		$model = 'SELECT acteur.nom AS acteur_nom, acteur.prenom AS acteur_prenom, SUM(role.idrole) AS acteur_nbfilm FROM thinkpi.monschema.acteur AS acteur LEFT JOIN thinkpi.monschema.role AS role ON role.idacteur = acteur.idacteur WHERE film.idfilm = \'5\' GROUP BY role.idrole;';
		$this->assertEquals($sql, $model);
	}
	
	/**
     * @covers ::getSQL
	 */
    public function testPgsqlSOrmSubrequest(): void
    {
		/** SORM  */
		$orm = \damix\engines\orm\Orm::get('video~sormstats' );
		$cvideoliste = $orm->getConditionsClear( 'videoliste' );
        $cvideoliste->addString( array( 'field' => 'annee'), \damix\engines\orm\conditions\OrmOperator::ORM_OP_GTEQ, '1970' );
		
		$cfilmannee = $orm->getConditionsClear( 'videoliste', 'film_annee' );
        $cfilmannee->addString( array( 'field' => 'idfilm'), \damix\engines\orm\conditions\OrmOperator::ORM_OP_LTEQ, '50' );
		
		$request = $orm->videolisteRequest();
		$cs = new \damix\engines\orm\conditions\OrmConditions();
		$cs->add( $cvideoliste );
		$cs->add( $cfilmannee, 'film_annee' );
		$sql = $request->execute( $cs );
		
		$model = 'SELECT MIN(stat.annee) AS film_annee FROM (SELECT film.annee AS annee FROM thinkpi.monschema.film AS film LEFT JOIN thinkpi.monschema.genre AS genre ON genre.id = film.idgenre WHERE annee >= \'50\') AS stat WHERE annee >= \'1970\';';
		$this->assertEquals($sql, $model);
	}
	
	/**
     * @covers ::getSQL
	 */
    public function testPgsqlCreate(): void
    {
		$orm = \damix\engines\orm\Orm::getStructure('video~tormacteur' );
		$table = $orm->getTable();
		$create = new \damix\engines\orm\request\ormrequestcreate();
		$create->setTable( $table );
		$create->setIgnore( true );
		$sql = $create->getSQL();
		
		$model = 'DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = \'acteur_style\') THEN CREATE TYPE monschema.acteur_style AS enum(\'classic\',\'action\',\'sf\'); END IF; END $$; CREATE TABLE IF NOT EXISTS thinkpi.monschema.acteur ( idacteur SERIAL NOT NULL, nom varchar(255) NULL DEFAULT NULL, prenom varchar(255) NULL DEFAULT NULL, naissance date NULL DEFAULT NULL, style monschema.acteur_style NULL DEFAULT NULL, CONSTRAINT acteur_pkey PRIMARY KEY (idacteur) ) TABLESPACE PG_DEFAULT;';
		$this->assertEquals($sql, $model);
		
	}
	
	/**
     * @covers ::getSQL
	 */
	public function testPgsqlAlterIndex(): void
    {
		$schema = \damix\engines\orm\request\structure\OrmSchema::newSchema('monschema');
		$table1 = \damix\engines\orm\request\structure\OrmTable::newTable('table1');
		$schema->addTable( $table1 );
		
		$alter = new \damix\engines\orm\request\ormrequestalter();
		$alter->setTable( $table1 );
		$ormindex = new \damix\engines\orm\request\structure\OrmIndex();
		$ormindex->setName( 'fk_table1_nom' );
		$ormindex->setIgnore(true);
		$ormindex->setIndexType( \damix\engines\orm\request\structure\OrmIndexType::ORM_INDEX );
		$ormfield = new \damix\engines\orm\request\structure\OrmField();
		$ormfield->setName('nom');
		$ormindex->addField( $ormfield, \damix\engines\orm\request\structure\OrmOrderWay::WAY_ASC );
		$alter->IndexAdd( $ormindex );
	
		$sql = $alter->getSQL();
		$model = 'CREATE INDEX fk_table1_nom ON monschema.table1 ( nom asc );';
		$this->assertEquals($sql, $model);
		
		
		$alter = new \damix\engines\orm\request\ormrequestalter();
		$alter->setTable( $table1 );
		$ormindex = new \damix\engines\orm\request\structure\OrmIndex();
		$ormindex->setName( 'fk_table1_nom' );
		$ormindex->setIgnore(true);
		$ormindex->setIndexType( \damix\engines\orm\request\structure\OrmIndexType::ORM_UNIQUE );
		$ormfield = new \damix\engines\orm\request\structure\OrmField();
		$ormfield->setName('nom');
		$ormindex->addField( $ormfield, \damix\engines\orm\request\structure\OrmOrderWay::WAY_ASC );
		$alter->IndexAdd( $ormindex );
		$sql = $alter->getSQL();
		$model = 'CREATE UNIQUE INDEX fk_table1_nom ON monschema.table1 ( nom asc );';
		$this->assertEquals($sql, $model);
	}
	
	/**
     * @covers ::getSQL
	 */
    public function testPgsqlcreateTrigger(): void
    {
		$tormacteur = \damix\engines\orm\Orm::get( 'video~tormacteur' );
		$tormacteur->createTable( true );
		
		$orm = \damix\engines\orm\Orm::getStructure('video~tormacteur' );
		$table = $orm->getTable();
		
		$sel = new \damix\engines\orm\stored\OrmStoredSelector(\damix\engines\orm\stored\OrmStoredSelector::STORAGE_TRIGGER);
		
		
		$obj = new \damix\engines\orm\request\OrmRequestStored();
		$obj->setName( 'before_update_acteur' );
		$obj->setAction( \damix\engines\orm\request\structure\OrmTriggerAction::ORM_UPDATE );
		$obj->setEvent( \damix\engines\orm\request\structure\OrmTriggerEvent::ORM_BEFORE );
		$obj->setTable( $table );
		$obj->setSchema( $table->getSchema() );
		$content = file_get_contents( $sel->getPathContent( 'before_update_acteur' ) );
		$obj->setContent( $content );

		$obj->setSqlType(\damix\engines\orm\request\structure\OrmStructureType::SQL_TRIGGER_STORED_DELETE);
		$result = $obj->executeNonQuery();
		$this->assertEquals($result, 0);
	
		$obj->setSqlType(\damix\engines\orm\request\structure\OrmStructureType::SQL_TRIGGER_STORED_CREATE);
		$result = $obj->executeNonQuery();
		
		$this->assertEquals($result, 0);
	}
	
	/**
     * @covers ::query
	 */
    public function testPgsqlFactoryBase(): void
    {
		$tormacteur = \damix\engines\orm\Orm::get( 'video~tormacteur' );
		$tormrole = \damix\engines\orm\Orm::get( 'video~tormrole' );
		
		$tormrole->createTable(true);
		$result = $tormacteur->createTable(true);
		
		
		$this->assertEquals( true, $result );
		
		$record = $tormacteur->createRecord();
		$record->nom = 'maValeur';
		
		$nb = $tormacteur->insert( $record );
		$pk = $record->idacteur;
		$this->assertEquals( 1, $nb );
		
		$record = $tormacteur->get( $pk );
		$record->nom = 'maValeur2';
		$nb = $tormacteur->update( $record );
		$this->assertEquals( 1, $nb );
		
		$nb = $tormacteur->delete( $pk );
		$this->assertEquals( 1, $nb );
		
		$tormrole->dropForeignKey(true);
		$result = $tormacteur->dropTable();
		$this->assertEquals( true, $result );
	}
	
	/**
     * @covers ::query
	 */
    public function testPgsqlPropertyBase(): void
    {
		$orm = \damix\engines\orm\Orm::get( 'video~tormfilm' );
		$result = $orm->createTable(true);
		$result = $orm->alterTable(true);
		$icon = base64_decode('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');
		
		$clsfilm = \damix\core\classes\Classe::get( 'video~clsfilm' );
		$clsfilm->idfilm = null;
		$clsfilm->titre = 'maValeur';
		$clsfilm->annee = 2000;
		$clsfilm->duree = new \damix\engines\tools\xDate('2023-04-19 15:07:42');
		$clsfilm->dureerush = new \damix\engines\tools\xDate('2023-04-19 15:07:42');
		$clsfilm->resume = 'sdfqsqsf ';
		$clsfilm->interdit = 'douze';
		$clsfilm->sortiedate = new \damix\engines\tools\xDate('1995-07-04');
		$clsfilm->nbacteur = 15;
		$clsfilm->nbfigurant = 50;
		$clsfilm->budget = 159.6578;
		$clsfilm->recette = 641321.654;
		$clsfilm->depense = 41356.782;
		$clsfilm->total = $clsfilm->recette + $clsfilm->depense;
		$clsfilm->icon = $icon;
		$clsfilm->bigaffiche = $icon;
		$clsfilm->idgenre = null;
		$clsfilm->complement = 'Mon complément';
		
		$result = $clsfilm->save();
		$this->assertTrue( $result );
		
		$id = $clsfilm->idfilm;
		$this->assertGreaterThan( 0, $id );
		$clsfilm->annee = 2010;
		$result = $clsfilm->save();
		$this->assertTrue( $result );
		
		$result = $clsfilm->delete( $id );
		$this->assertTrue( $result );
		
		
		$clsfilm->clear();
		$clsfilm->setArrayFormat(array( 
				'titre' => 'monFilm', 
				'annee' => 1980, 
				'time_duree' => new \damix\engines\tools\xDate('2023-04-19 15:07:43'),
				'date_sortiedate' => new \damix\engines\tools\xDate('1995-07-04'),
				'date_dureerush' => new \damix\engines\tools\xDate('1995-07-04 23:41:16'),
				'resume' => 'mon Résumé',
				'idagence' => null,
				'nbacteur' => 15,
				'nbfigurant' => 50,
				'budget' => 159.6578,
				'recette' => 641321.654,
				'depense' => 41356.782,
				'total' => 682678.436,
				'icon' => $icon,
				'bigaffiche' => $icon,
				'complement' => 'Mon complément n°2',
				));
		$result = $clsfilm->save();
		$this->assertTrue( $result );
		
		$result = $clsfilm->getFormatArray();
		$this->assertGreaterThan( 0, count( $result ));
		
		$result = $clsfilm->getFormatObject();
		$this->assertGreaterThan( 0, is_object( $result ));
	}
	
	/**
     * @covers ::query
	 */
    public function testPgsqlProcedureStored(): void
    {
		$result = \damix\engines\orm\stored\OrmStored::CreateProcedures();
		$this->assertTrue( $result );
		
		$procedures = \damix\engines\orm\stored\OrmStored::getProcedures();
		$result = $procedures->TEST(2023);
		
		$this->assertTrue( $result );
	}

	/**
     * @covers ::query
	 */
    public function testPgsqlFunctionStored(): void
    {
		$result = \damix\engines\orm\stored\OrmStored::CreateFunctions();
		$this->assertTrue( $result );
		
		$functions = \damix\engines\orm\stored\OrmStored::getFunctions();
		$date = $functions->easter(2023);
		
		$this->assertEquals( '2023-04-09', $date->format(\damix\engines\tools\xDate::ISO_8601_D) );
	}

	/**
     * @covers ::query
	 */
    public function testMariadbEventStored(): void
    {
		$this->expectException( \damix\core\exception\OrmException::class );
		$result = \damix\engines\orm\stored\OrmStored::CreateEvents();
	}
	
	/**
     * @covers ::getSQL
	 * @covers \damix\engines\databases\Db
	 * @covers \damix\engines\databases\DbConnection
	 * @covers \damix\engines\databases\DbSelector
	 * @covers \damix\engines\databases\drivers\MariadbDbConnection
	 * @covers \damix\engines\logs\Log
	 * @covers \damix\engines\logs\LogBase
	 * @covers \damix\engines\orm\Orm
	 * @covers \damix\engines\orm\OrmBaseFactory
	 * @covers \damix\engines\orm\OrmBaseProperties
	 * @covers \damix\engines\orm\OrmCompiler
	 * @covers \damix\engines\orm\OrmGenerator
	 * @covers \damix\engines\orm\OrmSelector
	 * @covers \damix\engines\orm\conditions\OrmCondition
	 * @covers \damix\engines\orm\drivers\OrmDrivers
	 * @covers \damix\engines\orm\drivers\OrmDriversBase
	 * @covers \damix\engines\orm\drivers\OrmDriversMariadb
	 * @covers \damix\engines\orm\drivers\OrmDriversSelector
	 * @covers \damix\engines\orm\method\OrmMethod
	 * @covers \damix\engines\orm\method\OrmMethodCompiler
	 * @covers \damix\engines\orm\method\OrmMethodGenerator
	 * @covers \damix\engines\orm\method\OrmMethodSelector
	 * @covers \damix\engines\orm\request\OrmRequest
	 * @covers \damix\engines\orm\request\OrmRequestSelect
	 * @covers \damix\engines\orm\request\structure\OrmColumn
	 * @covers \damix\engines\orm\request\structure\OrmField
	 * @covers \damix\engines\orm\request\structure\OrmFrom
	 * @covers \damix\engines\orm\request\structure\OrmGroups
	 * @covers \damix\engines\orm\request\structure\OrmLimits
	 * @covers \damix\engines\orm\request\structure\OrmOrders
	 * @covers \damix\engines\orm\request\structure\OrmTable
	 * @covers \damix\engines\tools\GeneratorContent
	 * @covers \damix\engines\tools\xDate
	 * @covers \damix\engines\tools\xFile
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\tools\xmlDocument
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingSelector	 
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\logs\LogFile
	 * @covers \damix\engines\logs\LogMessage
	 * @covers \damix\engines\logs\LogSelector
	 * @covers \damix\engines\orm\OrmBaseStructure
	 * @covers \damix\engines\orm\OrmGeneratorStructure
	 * @covers \damix\engines\orm\OrmStructureSelector
	 * @covers \damix\engines\orm\defines\OrmDefines
	 * @covers \damix\engines\orm\defines\OrmDefinesBase
	 * @covers \damix\engines\orm\defines\OrmDefinesCompiler
	 * @covers \damix\engines\orm\defines\OrmDefinesGenerator
	 * @covers \damix\engines\orm\defines\OrmDefinesSelector
	 * @covers tobool
	 */
    public function testPgsqlOrmInfoSchema(): void
    {
		/** TABLES  */
		$driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();
		$schema = \damix\engines\orm\request\structure\OrmSchema::newSchema('monschema');
		$table1 = \damix\engines\orm\request\structure\OrmTable::newTable('table1');
		$schema->addTable( $table1 );

		$request = $driver->SchemaTable( $table1 );
		$sql = $driver->getSQL($request);
		$model = 'SELECT TABLES.TABLE_CATALOG AS TABLE_CATALOG, TABLES.TABLE_SCHEMA AS TABLE_SCHEMA, TABLES.TABLE_NAME AS TABLE_NAME, TABLES.TABLE_TYPE AS TABLE_TYPE FROM information_schema.TABLES AS TABLES WHERE TABLE_SCHEMA = \'monschema\' AND TABLE_NAME = \'table1\';';
		$this->assertEquals($sql, $model);
		
		/** COLUMNS  */
		$driver = \damix\engines\orm\drivers\OrmDrivers::getDriver();
		$request = $driver->SchemaColonne( $table1 );
		$sql = $driver->getSQL($request);
		$model = 'SELECT COLUMNS.TABLE_CATALOG AS TABLE_CATALOG, COLUMNS.TABLE_SCHEMA AS TABLE_SCHEMA, COLUMNS.TABLE_NAME AS TABLE_NAME, COLUMNS.COLUMN_NAME AS COLUMN_NAME, COLUMNS.ORDINAL_POSITION AS ORDINAL_POSITION, COLUMNS.COLUMN_DEFAULT AS COLUMN_DEFAULT, COLUMNS.IS_NULLABLE AS IS_NULLABLE, COLUMNS.DATA_TYPE AS DATA_TYPE, COLUMNS.CHARACTER_MAXIMUM_LENGTH AS CHARACTER_MAXIMUM_LENGTH, COLUMNS.CHARACTER_OCTET_LENGTH AS CHARACTER_OCTET_LENGTH, COLUMNS.NUMERIC_PRECISION AS NUMERIC_PRECISION, COLUMNS.NUMERIC_SCALE AS NUMERIC_SCALE, COLUMNS.DATETIME_PRECISION AS DATETIME_PRECISION, COLUMNS.CHARACTER_SET_NAME AS CHARACTER_SET_NAME, COLUMNS.DATA_TYPE AS COLUMN_TYPE, KCU.CONSTRAINT_TYPE AS COLUMN_KEY, CASE WHEN POSITION(\'nextval\' in column_default ) >= 0 THEN \'AUTO_INCREMENT\' ELSE \'0\' END AS EXTRA FROM information_schema.COLUMNS AS COLUMNS LEFT JOIN (SELECT TCO.CONSTRAINT_TYPE AS CONSTRAINT_TYPE, KCU.TABLE_SCHEMA AS TABLE_SCHEMA, KCU.TABLE_NAME AS TABLE_NAME, KCU.COLUMN_NAME AS COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE AS KCU JOIN information_schema.TABLE_CONSTRAINTS AS TCO ON KCU.constraint_schema = TCO.constraint_schema AND KCU.constraint_name = TCO.constraint_name AND TCO.constraint_type = \'PRIMARY KEY\') AS KCU ON COLUMNS.table_schema = KCU.table_schema AND COLUMNS.table_name = KCU.table_name AND COLUMNS.column_name = KCU.column_name WHERE COLUMNS.TABLE_SCHEMA = \'monschema\' AND COLUMNS.TABLE_NAME = \'table1\';';
		$this->assertEquals($sql, $model);
	}
	
}

