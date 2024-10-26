<?php
/**
* @package      damix
* @Module       core
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\core\response;

class ResponseBaseHtml
	extends ResponseBase
{
	protected string $_bodyTpl = '';
	public \damix\engines\template\Template $Tpl ;
	protected array $linkJS = array();
	protected array $linkCSS = array();
	public string $htmlversion = 'HTML5';
	public string $title = '';
	public array $bodyAttributes = array();
	public array $meta = array();
	
	public function __construct()
    {
		parent::__construct();
		
		if( ! empty( $this->_bodyTpl ) )
		{
			$this->Tpl = \damix\engines\template\Template::get( $this->_bodyTpl );
		}
    }
	
	public function setBodyTpl( string $bodytpl ) : void
	{
		$this->_bodyTpl = $bodytpl;
		
		$this->Tpl = \damix\engines\template\Template::get( $this->_bodyTpl );
	}
	
	public function setTitle(string $title) : void
	{
		$this->title = $title;
	}
	
	public function addCssLink( string $link ) : void
	{
		$this->linkCSS[] = $link;
	}
	
	public function addJsLink( string $link ) : void
	{
		$this->linkJS[] = $link;
	}
	
	protected function doAfterActions() : void
	{
	}
	
	public function output() : void
	{
		$this->doAfterActions();
		
		$this->sendHttpHeaders();
		
		$content = $this->getDoctype();
		$content .= "<html lang=\"fr\">\n";
		$content .= "<head>\n";
		$content .= $this->sendHtmlHead();
		$content .= "</head>\n";
		$content .= '<body';
		
		foreach( $this->bodyAttributes as $name => $value )
		{
			$content.= ' ' . $name . '="' . htmlspecialchars($value) . '"';
		}
		
		$content .= ">\n";
		
		if( ! empty( $this->_bodyTpl ) )
		{
			$content .= $this->Tpl->fetch();
		}
		
		$content .= "</body>\n";
		$content .= "</html>";
		print $content;
	}
	
	public function setBodyAttributes(array $attributes) : void
	{
		$this->bodyAttributes = array_merge($this->bodyAttributes, $attributes);
	}
	
	public function setBodyAttribute(string $name, string $value) : void
	{
		$this->bodyAttributes[ $name ] = $value;
	}
	
	public function getBodyAttribute(string $name) : string
	{
		return $this->bodyAttributes[ $name ] ?? '';
	}
	
	protected function getDoctype() : string
	{
		$doctype = match( strtoupper( $this->htmlversion ) )
		{
			'HTML4' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
			'HTML5' => '<!DOCTYPE html>',
			default => '<!DOCTYPE html>',
		};
		
		return $doctype . "\r\n";
	}
	
	protected function outputMeta() : string
	{
		$html='';
		foreach( $this->meta as $meta )
		{
			$html .= '<meta ';
			foreach( $meta as $name => $value )
			{
				$html .= $name . '="' .  htmlspecialchars($value) . '" ';
			}
			$html .= "/>\r\n";
		}
		
		return $html;
	}
	
	public function addMeta( array $meta ) : void
	{
		$this->meta[] = $meta;
	}
	
	protected function sendHtmlHead() : string
	{
		$out = array();
		$out[] = $this->outputMeta();
		if( ! empty( $this->title ) )
		{
			$out[] = '<title>'.htmlspecialchars($this->title)."</title>\n";
		}
		foreach( $this->linkCSS as $link )
		{
			$out[] = '<link type="text/css" href="'. $link .'" rel="stylesheet" />';
		}
		
		\damix\engines\scripts\Javascript::addToResponse( $this );
		
		foreach( $this->linkJS as $link )
		{
			$out[] = '<script type="text/javascript" src="'. $link .'" ></script>';
		}
		
		return implode ("\r\n", $out);
	}
}