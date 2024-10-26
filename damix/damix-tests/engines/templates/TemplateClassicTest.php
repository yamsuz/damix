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
 * @coversDefaultClass \damix\engines\template\drivers\template\TemplateGenerator
 * @group monkey
 */
final class TemplateClassicTest extends TestCase
{
	public static function setUpBeforeClass(): void
    {
    	\damix\engines\settings\Setting::clear();
	}
	
	/**
     * @covers ::parseScript
	 * @covers \damix\engines\monkey\MonkeyEnvironment
	 * @covers \damix\engines\template\Template
	 * @covers \damix\engines\template\drivers\template\TemplateGenerator
	 * @covers \damix\core\Selector
	 */
    public function testParseScriptFunction(): void
    {
		$template = new \damix\engines\template\Template();
		$obj = new \damix\engines\template\drivers\template\TemplateGenerator();
		$parseScript = self::getMethod('\damix\engines\template\drivers\template\TemplateGenerator', 'compileContent');
		
		$content = array();
		
		$content[] = array( 'src' => 'mon prénom est : {$name}', 'dest' => 'mon prénom est : <?php echo $t->parameters[\'name\']; ?>');
		$content[] = array( 'src' => '{for ($i = 0; $i < 10; $i++ )}{$i}{/for}', 'dest' => '<?php for($t->parameters[\'i\'] = 0; $t->parameters[\'i\'] < 10; $t->parameters[\'i\']++ ): ?><?php echo $t->parameters[\'i\']; ?><?php endfor; ?>');
		$content[] = array( 'src' => '{foreach $tab as $info}{$info}<br/>{/foreach}', 'dest' => '<?php foreach($t->parameters[\'tab\'] as $t->parameters[\'info\']): ?><?php echo $t->parameters[\'info\']; ?><br/><?php endforeach; ?>');
		$content[] = array( 'src' => '{* TEST *}', 'dest' => '');
		$content[] = array( 'src' => '{if $nom == \'toto\'}inst1{else}inst2{/if}', 'dest' => '<?php if($t->parameters[\'nom\'] == \'toto\'): ?>inst1<?php else: ?>inst2<?php endif; ?>');

		$content[] = array( 'src' => '{zone \'monModule~maZone\', array( \'param1\' => \'Value1\', \'param2\' => \'Value2\' )}', 'dest' => '<?php \damix\engines\zones\Zone::get( \'monModule~maZone\', array( \'param1\' => \'Value1\', \'param2\' => \'Value2\' )); ?>');
		
		
		foreach( $content as $info )
		{
			$result = $parseScript->invokeArgs($obj, array($info['src']));
			
			$this->assertEquals($info['dest'], preg_replace('/\r\n/', '', $result));
		}
    }
	/**
     * @covers ::parseScript
 	 * @covers \damix\engines\monkey\MonkeyEnvironment
	 * @covers \damix\engines\monkey\Evaluator
	 * @covers \damix\engines\monkey\Lexer
	 * @covers \damix\engines\monkey\MonkeyInteger
	 * @covers \damix\engines\monkey\Parser
	 * @covers \damix\engines\monkey\ParserTracer
	 * @covers \damix\engines\monkey\Token
	 * @covers \damix\engines\template\Template
	 * @covers \damix\engines\template\drivers\template\TemplateGenerator
	 * @covers \damix\core\Selector
	 */
    public function testParseScriptMonkey(): void
    {
		$template = new \damix\engines\template\Template();
		$obj = new \damix\engines\template\drivers\template\TemplateGenerator();
		$parseScript = self::getMethod('\damix\engines\template\drivers\template\TemplateGenerator', 'compileContent');
		
		$content = array();
		

		$content[] = array( 'src' => '{script}5+1{/script}', 'dest' => '6');
		$content[] = array( 'src' => '{execute}let a = 5+1;let b = a + 2;return b;{/execute}', 'dest' => '<?php $line = \'let a = 5+1;let b = a + 2;return b;\';print monkeyscripttemplate($line);?>');
		$content[] = array( 'src' => '{execute}return somme( 5, 6 );{/execute}', 'dest' => '<?php $line = \'return somme( 5, 6 );\';print monkeyscripttemplate($line);?>');
		
		foreach( $content as $info )
		{
			$result = $parseScript->invokeArgs($obj, array($info['src']));
			
			$this->assertEquals($info['dest'], preg_replace('/\r\n/', '', $result));
		}
    }

	
	protected static function getMethod($classname, $name) {
		$class = new ReflectionClass($classname);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}
   
}



