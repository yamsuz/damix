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
 * @coversDefaultClass \damix\engines\orm\request\ormrequestcreate
 */
final class OrmTest extends TestCase
{
	private static string $definetmp;
	private static string $defineDefault;
	
	public static function setUpBeforeClass(): void
    {
    	\damix\engines\settings\Setting::clear();
	}

    public static function tearDownAfterClass(): void
    {
        // if( file_exists( self::$definetmp ) )
		// {
			// rename( self::$definetmp, self::$defineDefault );
		// }
		
		$sel = new \damix\engines\orm\defines\OrmDefinesSelector();
		if( file_exists( $sel->getTempPath() ))
		{
			unlink( $sel->getTempPath());
		}
    }
	
	/**
     * @covers ::query
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\orm\defines\OrmDefines
	 * @covers \damix\engines\orm\defines\OrmDefinesBase
	 * @covers \damix\engines\orm\defines\OrmDefinesCompiler
	 * @covers \damix\engines\orm\defines\OrmDefinesGenerator
	 * @covers \damix\engines\orm\defines\OrmDefinesSelector
	 * @covers \damix\engines\tools\GeneratorContent
	 * @covers \damix\engines\tools\xFile
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\tools\xmlDocument
	 */
    public function testOrmDefines(): void
    {
		$define = \damix\engines\orm\defines\OrmDefines::get();
		
		$this->assertEquals($define->get('ACTEUR'), 'video~tormacteur');
		$this->assertEquals($define->getClasse('ACTEUR'), 'video~clsacteur');
		// $this->assertEquals($define->getDefines(), array( 'ACTEUR' => array( 'selector' => 'video~tormacteur', 'class' => 'video~clsacteur'), 'CONSULTANT' => array( 'selector' => 'video~tormconsultant', 'class' => 'video~clsconsultant')));
    }
	
	/**
     * @covers ::query
	 * @covers \damix\engines\orm\Orm
	 * @covers \damix\engines\orm\OrmBaseRecord
	 */
    public function testMariadbOrmRecord(): void
    {
		$orm = \damix\engines\orm\Orm::get('video~tormacteur');
		$record = $orm->createRecord();
		
		$this->assertEquals( $record->getProperty('idacteur'), array( 'name' => 'idacteur' ));
		$this->assertEquals( $record->getProperty('nom'), array('name' => 'nom' ));
		
	}

	

}

