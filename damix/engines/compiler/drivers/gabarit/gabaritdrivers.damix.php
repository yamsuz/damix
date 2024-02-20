<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit;

class gabaritDrivers
    extends \damix\engines\compiler\CompilerDriver
{
    protected ?string $_defaultelement = 'all';
    protected ?string $_defaultattribute = 'all';
    protected ?string $_defaultfunction = 'all';
    protected string $_id = '';
    protected bool $compilerjs = true;
    
    public function __construct()
    {
        parent::__construct();
        $this->_id = uniqid();
    }
    
    public function init()
    {
        $this->content->addProperty( '_id', $this->content->quote( $this->_id ), 'string', false, 'protected' );
        $this->content->addProperty( '_html', $this->content->quote( '' ), 'string', false, 'protected' );
        $this->content->addProperty( '_javascript', $this->content->quote( '' ), 'string', false, 'protected' );
        $this->content->addProperty( '_properties', 'array()', 'array', false, 'protected' );
        $this->content->addProperty( '_gabname', $this->content->quote(  preg_replace( '/~/', '', $this->selector->_selector ) ), 'string', false, 'protected' );
						
		$this->content->appendFunction( 'getHtml', array('$params = array()'), array( '$this->mergeParameters($params);'), 'public', 'void');
		$this->content->appendFunction( 'getHtml', array('$params = array()'), array( '$this->inithtml();'), 'public', 'void');
        $this->content->addFunction( 'inithtml', array(), array( '' ), 'protected');
        $this->content->addFunction( 'initjavascript', array(), array( '' ), 'protected');
						
        $this->getHtml();
        $this->getJs();
        $this->createJSFile();
        $this->content->addConstructInit( 'initjavascript', array( '$this->initjavascript();' ) );
    }
    
    private function getJs()
    {
        $content = array();
        
        $content[] = '$out = \'\';';
        $content[] = '$out .= \'(!\' . $var . \'.id ?\' . $var . \'.id = "\' . $this->_id. \'":null);\';';

        $content[] = 'foreach( $this->_properties as $name => $prop)';
        $content[] = '{';
        $content[] = '$out .= $var . \'.form.push({"name":"\'. $name . \'","id":"\'. (isset( $prop[\'id\'] ) ? $prop[\'id\'] : \'\') . \'"});\';';
        $content[] = '}';
        $content[] = '$out .= $this->_javascript;';
        $content[] = 'return $out;';
        
        $this->content->addFunction( 'getJavascript', array('$var'), $content, 'public');
    }
    
    private function createJSFile()
    {
        $content = array();
        
        $content[] = '$gabname = \'gab\' . $this->_gabname ;';
        $content[] = '$xJavascriptSelector = new \damix\engines\scripts\JavascriptSelector( \'gabarit~' . $this->selector->_selector  . '\' );';
        
        $content[] = '$path = $xJavascriptSelector->getTempPath();';
        
        $content[] = '$out = \'var \' . $gabname . \' = new xFormsManager();\';';
        $content[] = '$out .= $this->getJavascript($gabname);';
        
        $content[] = '\damix\engines\tools\xFile::write( $path, $out );';
        $content[] = '\damix\engines\scripts\Javascript::link( \'gabarit~' . $this->selector->_selector  . '\' );';
       
        $this->content->addFunction( 'createJSFile', array(), $content, 'public');
    }
    
    private function getHtml()
    {
        $content = array();
        $content[] = '$out = \'\';';
        $content[] = 'if( $this->compilerjs )';
        $content[] = '$out .= \'<div id="\'. $this->_id .\'">\';';
        $content[] = '$out .= $this->_html;';
        $content[] = 'if( $this->compilerjs )';
        $content[] = '$out .= \'</div>\';';
        $content[] = 'return $out;';
        
        $this->content->appendFunction( 'getHtml', array('$params = array()'), $content, 'public');
    }
    
    public function writefile( $xcompilerselector )
    {
		$this->content->writeLine( '<?php' );
        $this->content->writeLine( 'namespace gabarit;' );
        $this->content->writeLine( '' );
        $this->content->writeLine( 'class ' . $xcompilerselector->getClassName() );
        $this->content->tab( 1 );
        $this->content->writeLine( 'extends \damix\engines\views\gabarits\GabaritBase' );
        $this->content->tab( -1 );
        $this->content->writeLine( '{' );
		
        $this->generateText($xcompilerselector);
		
		$this->content->writeLine( '}' );
		
        \damix\engines\tools\xfile::write( $xcompilerselector->getTempPath(), $this->content->getText() );
        
    }


	public function generateText($xcompilerselector )
	{
        $data = $this->content->getData();
        foreach( $data as $name => $elt )
        {
            $ligne = '$this->'. $name .' = ' . $this->content->quote( implode('', $elt  )) . ';';
            $this->content->addFunction( 'inithtml', array(), array( $ligne ), 'public');
        }
        
        $properties = $this->content->getProperties();
        
        foreach( $properties as $name => $info )
        {
            $this->content->tab( 1 );
            $this->content->writeLine( $info['visibility'] . ' $' . $name . ' = ' . $info['value'] . ';' );
            
            $this->content->tab( -1 );
        }

        $this->content->tab( 1 );

        $this->content->writeLine( '' );
        $construct = $this->content->getConstruct();
        $this->content->writeLine( 'public function __construct( $params = array() )' );
        $this->content->writeLine( '{' );
        $this->content->tab( 1 );
        $this->content->writeLine( '$this->_parameters = $params;' );
        $this->content->writeLine( 'parent::__construct();' );
        foreach( $construct as $name => $info )
        {
            $this->content->writeLine( implode( ';', $info ) );
        }
        $this->content->tab( -1 );
        $this->content->writeLine( '}' );
        $this->content->tab( -1 );
                
        $functions = $this->content->getFunction();
        foreach( $functions as $name => $info )
        {
            $this->content->tab( 1 );
            $this->content->writeLine( $info['visibility'] . ' function ' . $name . '(' . implode( ',', $info['params'] ). ')' );
            $this->content->writeLine( '{' );
            $this->content->tab( 1 );
            foreach( $info['content'] as $content )
            {
                if( is_array( $content ) )
                {
					foreach($content as $c)
					{
						$this->content->writeLine( $c );
					}
                }
				else
				{
					$this->content->writeLine( $content );
				}
            }
            $this->content->tab( -1 );
            $this->content->writeLine( '}' );
            
            $this->content->tab( -1 );
        }
	}
}