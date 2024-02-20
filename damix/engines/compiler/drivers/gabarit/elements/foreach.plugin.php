<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\compiler\drivers\gabarit\elements;

class gabaritelementforeach
    extends \damix\engines\compiler\GabaritElementAll
{
    protected $_autoClose = true;
    private $_foreachName;
    private $_driver;
    private $_domNode;
    private $_xcompilercontentelement;
    
	public function beforeRead( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node, \DOMNode $child ) : void
	{
		$classname = $driver->classname;
		$this->_driver = new $classname();
		$this->_driver->_driver = $driver->_driver;
		$this->_driver->_tags = $driver->_tags;
		$this->_driver->_attr = $driver->_attr;
		$this->_driver->_func = $driver->_func;
		$this->_driver->_cfunc = $driver->_cfunc;
		
		$this->_domNode = $child->cloneNode(true);
		
		foreach( $child->childNodes as $childnode)
		{
			$child->removeChild($childnode);
		}
	}
	
    public function read( \damix\engines\compiler\CompilerDriver $driver, \damix\engines\compiler\CompilerContentElement $node ) : void
    {
		$this->_driver->parentDriver = $this;
        $this->_foreachName = $node->getAttrValue( 'name' );
		$node->removeAttributes( 'name' );
		 
		$this->_xcompilercontentelement = new \damix\engines\compiler\CompilerContentElement();
		
		$this->_driver->read( $this->_xcompilercontentelement, $this->_domNode );
    }
	
	public function write( \xcompiler\xcompilerdriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj )
    {        
        $driver->content->addData( '_html', '__QUOTE__ . $this->foreach_' . $this->_foreachName . '() . __QUOTE__' );
		
		$this->_driver->write( $this->_xcompilercontentelement, $this->_domNode );
		$this->_driver->generateText($this->_driver->selector);


		$params = array();
		$content = array();
		$content[] = '$liste = $this->getParam(\'' . $this->_foreachName  . '\');';
		$content[] = '$out = \'\';';
		
		$content[] = 'foreach($liste as $'. $this->childExecute() .'){';
		
		$content[] = '$out .= ' . $this->_driver->content->quote( implode( '', $this->_driver->content->getData()['_html'] ) ) . ';';

		$content[] = '}';
		$content[] = 'return $out;';
		$driver->content->addFunction( 'foreach_' . $this->_foreachName, $params, $content, 'public');
    }
	
	public function afterwriteAttribute( \xcompiler\xcompilerdriver $driver, \damix\engines\compiler\CompilerContentElement $node, object $obj )
	{
	}
	
	
	public function childExecute()
	{
		return '_child' . $this->_foreachName;
	}
    
}