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
 * @coversDefaultClass \damix\engines\locales\Locale
 */
final class localeTest extends TestCase
{
	public static function setUpBeforeClass(): void
    {
		\damix\engines\settings\Setting::clear();
	}
	
	/**
	 * @covers ::get
	 */
    public function testLocale(): void
    {
		$config = \damix\engines\settings\Setting::get('default');
		
		$config->set( 'general', 'langue', 'fr_FR' );
		$locale = \damix\engines\locales\Locale::get('video~lclformat.tiers.fiche.suppression', array(5, 'monClient'));
		$model = 'Voulez-vous supprimer le tiers monClient, fiche n°5 ?';
		$this->assertEquals( $model, $locale );
		
		
		$config->set( 'general', 'langue', 'en_EN' );
		
		$locale = \damix\engines\locales\Locale::get('video~lclformat.tiers.fiche.suppression', array(5, 'monClient'));
		$model = 'Would you like delete the customer monClient with the number n°5?';
		$this->assertEquals( $model, $locale );
		
    }

	
}



