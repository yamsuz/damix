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
 * @coversDefaultClass \damix\engines\authentificate\AuthBase
 */
final class AuthDbTest extends TestCase
{
	public static function setUpBeforeClass(): void
    {
		\damix\engines\settings\Setting::clear();
		
		$config = \damix\engines\settings\Setting::get('default');
		$config->set( 'auth', 'driver', 'db' );
		
		$auth = \damix\engines\authentificate\Auth::get();
		$auth->setup();
		$auth->userDelete('test');
	}

    public static function tearDownAfterClass(): void
    {
		$auth = \damix\engines\authentificate\Auth::get();
		$auth->userDelete('test');
		
		$auth = \damix\engines\authentificate\Auth::get();
		$auth->setup();
		$auth->userDelete('test');
		
        \damix\engines\settings\Setting::clear();
    }
	
	/**
     * @covers ::userNew
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\authentificate\Auth
	 * @covers \damix\engines\authentificate\AuthBase
	 * @covers \damix\engines\authentificate\AuthSelector
	 * @covers \damix\engines\authentificate\AuthFile
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
     */
    public function testUserCreated(): void
    {
		$auth = \damix\engines\authentificate\Auth::get();
		$create = $auth->userNew('test', 'toto');
        $this->assertTrue( $create);
		
		$create = $auth->userNew('test', 'toto');
        $this->assertFalse( $create);
		
    }
	
	/**
     * @covers ::userDelete
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\authentificate\Auth
	 * @covers \damix\engines\authentificate\AuthBase
	 * @covers \damix\engines\authentificate\AuthSelector
	 * @covers \damix\engines\authentificate\AuthFile
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
     */
    public function testUserDelete(): void
    {
		$auth = \damix\engines\authentificate\Auth::get();
		$auth->userNew('test', 'toto');
		
		
		$delete = $auth->userDelete('test');
        $this->assertTrue( $delete );
		
		$delete = $auth->userDelete('test');
        $this->assertFalse( $delete );
		
    }

	/**
     * @covers ::cryptPassword
	 * @covers \damix\core\Selector
	 * @covers \damix\engines\authentificate\Auth
	 * @covers \damix\engines\authentificate\AuthBase
	 * @covers \damix\engines\authentificate\AuthSelector
	 * @covers \damix\engines\tools\xTools
	 * @covers \damix\engines\settings\Setting
	 * @covers \damix\engines\settings\SettingBase
	 * @covers \damix\engines\settings\SettingCompiler
	 * @covers \damix\engines\settings\SettingSelector
     */
    public function testCryptPassword(): void
    {
		$config = \damix\engines\settings\Setting::get('default');
		$config->set( 'auth', 'password_salt', '$2a$07$usesomesillystringforsalt$' );
		
        $string = 'test';
		
        $auth = \damix\engines\authentificate\Auth::get();
		$crypt = $auth->cryptPassword($string);
        $this->assertSame('qqmFURKkrDRuRIJ1XVz22', $crypt);
		
    }

   
}



