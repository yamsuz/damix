<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandAppcreate
	extends DamixCommands
{
	private string $application;
	
	public function execute(array $params)
	{
		$this->application = $params['a'] ?? null;
		
		if( $this->application === null )
		{
			self::display( 'Le nom de l\'application est obligatoire.' );
			return;
		}
		
		$racine = $this->directory . $this->application;
		if(! self::createDir( $racine ) )
		{
			self::display( 'L\'application existe déjà.' );
			return;
		}
		
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'acl');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'css');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'iconfont');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'javascript');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'ormdefines');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'ormstored' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'mariadb' . DIRECTORY_SEPARATOR . 'event');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'ormstored' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'mariadb' . DIRECTORY_SEPARATOR . 'event');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'ormstored' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'mariadb' . DIRECTORY_SEPARATOR . 'function');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'ormstored' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'mariadb' . DIRECTORY_SEPARATOR . 'procedure');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'ormstored' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'mariadb' . DIRECTORY_SEPARATOR . 'trigger');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->application . DIRECTORY_SEPARATOR . 'classes');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->application . DIRECTORY_SEPARATOR . 'controllers');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->application . DIRECTORY_SEPARATOR . 'locales' . DIRECTORY_SEPARATOR . 'fr_Fr');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->application . DIRECTORY_SEPARATOR . 'orm' . DIRECTORY_SEPARATOR . 'sorm');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->application . DIRECTORY_SEPARATOR . 'orm' . DIRECTORY_SEPARATOR . 'torm');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->application . DIRECTORY_SEPARATOR . 'templates');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->application . DIRECTORY_SEPARATOR . 'zones');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'logs');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'config');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'plugins');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'responses');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'temp');
		self::createDir( $racine . DIRECTORY_SEPARATOR . 'www');


		self::copy( $this->dirtemplate . DIRECTORY_SEPARATOR . 'var', $racine . DIRECTORY_SEPARATOR . 'var' );
		self::copy( $this->dirtemplate . DIRECTORY_SEPARATOR . 'www', $racine . DIRECTORY_SEPARATOR . 'www' );
		self::copy( $this->dirtemplate . DIRECTORY_SEPARATOR . 'responses', $racine . DIRECTORY_SEPARATOR . 'responses');
		self::copy( $this->dirtemplate . DIRECTORY_SEPARATOR . 'module', $racine . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->application );
		self::copy( $this->dirtemplate . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'css', $racine . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'css' );
		self::copy( $this->dirtemplate . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'javascript', $racine . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'javascript' );
		
		$filename = $racine . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'default.cfg.xml';
		$dom = new \damix\engines\tools\xmlDocument();
		$dom->load( $filename );
		
		$section = $dom->xPath( '/setting/section[@name="general"]' )->item(0);		
		$this->noteadd( $dom, $section, 'config', array( 'name' => 'startmodule', 'value' => $this->application ));
		$this->noteadd( $dom, $section, 'config', array( 'name' => 'startaction', 'value' => 'welcome' ));

		$url = $dom->xPath( '/setting/section[@name="url"]' )->item(0);	
		$this->noteadd( $dom, $url, 'config', array( 'name' => 'basepath', 'value' => $this->application ));
		
        $xml = $dom->save($filename);
		
		
		
		// DamixCommands::run( array( 'e' => 'createcontroller', 'a' => $this->application, 'm' => $this->application, 'c' => 'welcome') );
		
		$this->createapplicationinit();
		$this->createControllerWelcome();
		$this->createZoneWelcome();
		$this->createTemplateWelcome();
		$this->createTemplateHome();
		$this->createapplicationevent();
		$this->createapplicationacl();
		$this->createormdefine();
	}
	
	private function noteadd( \damix\engines\tools\xmlDocument $dom, \DOMNode $parent, string $name, array $attribute) : \DOMNode
	{
		
		$node = $dom->xPath( $name . '[@name="'. $attribute['name'] . '"]', $parent )->item(0);
		if( ! $node )
		{
			$node = $dom->addElement( $name, $parent, $attribute );
		}
		else
		{
			foreach( $attribute as $attrname => $attrvalue )
			{
				$dom->setAttribute( $node, $attrname, $attrvalue );
			}
		}
		
		return $node;
	}
	
	public static function createDir(string $dir) : bool
	{
		if(!file_exists($dir)){
			self::createDir(dirname($dir));
            @mkdir($dir);
			
			return true;
		}
		return false;
	}
	
	public static function copy(string $source, string $destination ) : void
	{
		if( is_dir( $source ) )
		{
			self::createDir( $destination );
            $objects = scandir($source);
            if( sizeof($objects) > 0 )
            {
                foreach( $objects as $file )
                {
                    if( $file == "." || $file == ".." )
                        continue;
                    
                    if( is_dir( $source . DIRECTORY_SEPARATOR . $file ) )
                    {
                        self::copy( $source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file );
                    }
                    else
                    {
                        copy( $source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file );
                    }
                }
            }
		}
		elseif( is_file($source) )
        {
            copy($source, $destination);
        }
	}
	private function createormdefine()
	{
		$filename = $this->directory . $this->application . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'ormdefines' . DIRECTORY_SEPARATOR . 'ormdefines.xml';
		$dom = \damix\engines\tools\xmlDocument::createDocument('defines');
		$dom->preserveWhiteSpace = false;
		$node = $dom->firstChild;
		$dom->setAttribute( $node, 'version', '1.0');
		$dom->formatOutput = true;
        $xml = $dom->save($filename);

	}
	private function createapplicationevent()
	{
		$filename = $this->directory . $this->application . DIRECTORY_SEPARATOR . 'events.evt.xml';
		$dom = \damix\engines\tools\xmlDocument::createDocument('compiler');
		$dom->preserveWhiteSpace = false;
		$node = $dom->firstChild;
		$dom->setAttribute( $node, 'version', '1.0');
		$dom->formatOutput = true;
        $xml = $dom->save($filename);

	}
	private function createapplicationacl()
	{
		$filename = $this->directory . $this->application .  DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'acl' . DIRECTORY_SEPARATOR . 'default.xml';
		$dom = \damix\engines\tools\xmlDocument::createDocument('acls');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
        $xml = $dom->save($filename);
	}
	
	private function createapplicationinit()
	{
		$content = array();
		$content[] = '<?php';
		$content[] = '';
		$content[] = 'function my_autoload_register($class) {';
		$content[] = '    $class = preg_replace( \'/\\\\\/\', DIRECTORY_SEPARATOR, $class);';
		$content[] = '    $filename = __DIR__ . DIRECTORY_SEPARATOR . \'..\' . DIRECTORY_SEPARATOR . \'..\' . DIRECTORY_SEPARATOR . strtolower($class) . \'.damix.php\';';
		$content[] = '    if( is_readable( $filename ) ){';
		$content[] = '        require_once( $filename );';
		$content[] = '    }';
		$content[] = '}';
		$content[] = '';
		$content[] = 'spl_autoload_register(\'my_autoload_register\');';
		$content[] = '';

		$content[] = '\damix\application::init(';
		$content[] = '		nameApp : \'' . $this->application . '\', ';
		$content[] = '		pathConfig : realpath(__DIR__ . DIRECTORY_SEPARATOR . \'configuration\' ) . DIRECTORY_SEPARATOR, ';
		$content[] = '		pathCore : realpath(__DIR__ . DIRECTORY_SEPARATOR . \'..\' . DIRECTORY_SEPARATOR . \'..\' . DIRECTORY_SEPARATOR . \'damix\' . DIRECTORY_SEPARATOR . \'core\') . DIRECTORY_SEPARATOR, ';
		$content[] = '		pathApp : __DIR__ . DIRECTORY_SEPARATOR, ';
		$content[] = '		pathTemp : __DIR__ . DIRECTORY_SEPARATOR . \'temp\' . DIRECTORY_SEPARATOR';
		$content[] = '		);';

		$content[] = 'require_once \damix\application::getPathCore() . \'..\' . DIRECTORY_SEPARATOR . \'engines\' . DIRECTORY_SEPARATOR . \'monkey\' . DIRECTORY_SEPARATOR . \'monkeyinclude.damix.php\';';

	
		$filename = $this->directory . $this->application . DIRECTORY_SEPARATOR . 'application.init.php';
		
		file_put_contents($filename, implode( "\r\n", $content ));
	}
	
	private function createControllerWelcome()
	{
		$content = array();
		
		$content[] = '<?php';
		$content[] = 'declare(strict_types=1);';
		$content[] = '';
		$content[] = 'namespace ' . $this->application . '\\' . $this->application.';';
		$content[] = '';
		$content[] = 'class welcome';
		$content[] = '	extends \damix\core\controllers\Controller{';
		$content[] = '';
		$content[] = '	public function index(){';
		$content[] = '		$rep = $this->getResponse(\'html\');';
		$content[] = '';
		$content[] = '		$rep->setBodyTpl( \'' . $this->application . '~home\' );';
		$content[] = '		$rep->Tpl->assignZone( \'MAIN\', \'' . $this->application . '~welcome\', array() );';
		$content[] = '';
		$content[] = '		return $rep;';
		$content[] = '	}';
		$content[] = '}';
		
		$filename = $this->directory . $this->application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->application . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'welcome.class.php';
		
		file_put_contents($filename, implode( "\r\n", $content ));
	}
	
	private function createZoneWelcome()
	{
		$content = array();
		
		$content[] = '<?php';
		$content[] = 'declare(strict_types=1);';
		$content[] = '';
		$content[] = 'namespace ' . $this->application . '\\' . $this->application.';';
		$content[] = '';
		$content[] = 'class welcomeZone';
		$content[] = '	extends \damix\engines\zones\ZoneBase{';
		$content[] = '';
		$content[] = '	protected string $tplSelector = \'' . $this->application . '~welcome\';';
		$content[] = '';
		$content[] = '	protected function prepareTpl() : void {';
		$content[] = '	}';
		$content[] = '}';
		
		$filename = $this->directory . $this->application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->application . DIRECTORY_SEPARATOR . 'zones' . DIRECTORY_SEPARATOR . 'welcome.class.php';
		
		file_put_contents($filename, implode( "\r\n", $content ));
	}
	
	private function createTemplateWelcome()
	{
		$content = array();
		
		$content[] = 'Hello World';
		
		$filename = $this->directory . $this->application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->application . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'welcome.tpl';
		
		file_put_contents($filename, implode( "\r\n", $content ));
	}
	
	private function createTemplateHome()
	{
		$content = array();
		
		$content[] = '{$MAIN}';
		
		$filename = $this->directory . $this->application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->application . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'home.tpl';
		
		file_put_contents($filename, implode( "\r\n", $content ));
	}
}