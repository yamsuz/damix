<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\datatables;


class Datatable
{
    static protected $_singleton=array();
    
    public static function get( string $selector ) : ?DatatableBase
    {
        $sel = new DatatableSelector( $selector );

		if(! isset(Datatable::$_singleton[$selector])){
			Datatable::$_singleton[$selector] = Datatable::create( $sel );
		}
		
		$obj = Datatable::$_singleton[$selector];
		if( $obj === null )
		{
			throw new \damix\core\exception\CoreException( 'The Datatable does not exists');
		}
		return $obj;
    }

    public static function create( DatatableSelector $selector ) : ?DatatableBase
    {
        $classname = $selector->getFullNamespace();
        
		if( ! class_exists($classname,false ) )
        {
			if( DatatableCompiler::compile( $selector ) )
            {
                $temp = $selector->getTempPath();
                require_once( $temp );
            }
            else
            {
                return null;
            }
		}
        $obj = new $classname();
       
        return $obj;
    }
    
    public static function saveUser( array $params ) : void
    {        
        $selector = $params['selector'] ?? null;
        
        if( ! $selector )
        {
            return;
        }
        
        $obj = Datatable::get( $selector );
        $datatableselector = new DatatableSelector( $selector );
        
        $filename = $datatableselector->getFileUserGroup();
        $dom = new \damix\engines\tools\xmldocument();
       
        if( ! $filename )
		{
			$filename = $datatableselector->getFileDefault();
		}
		
        if( $filename )
        {
            $dom->load( $filename );
        }
        
		$child = $dom->firstChild;
		$child->setAttribute( 'version', '1.0' );
		$child->setAttribute( 'driver', 'datatable' );
		$child->setAttribute( 'completion', 'replace' );

        
        Datatable::filelistcompletion( $dom, $params );
        
        $dom->save( $datatableselector->getFileUser() );
    }

    public static function saveDefault( array $params ) : void
    {        
        $selector = $params['selector'] ?? null;
       
        if( ! $selector )
        {
            return;
        }
        
        $obj = Datatable::get( $selector );
        $datatableselector = new DatatableSelector( $selector );
        
        $filename = $datatableselector->getFileDefault();
        $dom = new \damix\engines\tools\xmldocument();
        
        if( $filename )
        {
            $dom->load( $filename );
        }
        else
        {
            $dom = \damix\engines\tools\xmldocument::createDocument('compiler');
            $child = $dom->firstChild;
            $child->setAttribute( 'version', '1.0' );
            $child->setAttribute( 'driver', 'datatable' );
            $child->setAttribute( 'completion', 'replace' );
        }
        
        Datatable::filelistdefault( $dom, $params );
        
        
        
        $dom->save( $datatableselector->getFileDefault() );
    }
    
    private static function filelistdefault(\damix\engines\tools\xmldocument $dom, array $params, bool $default = false) : void
    {
        $name = $params['name'];
        
        $item = $dom->xPath( '/compiler/screen/list[@name="' . $name . '"]' );
 
        if( $item && $item->length == 0 )
        {
            $listescreen = $dom->xPath( '/compiler/screen' );
            if( $listescreen && $listescreen->length == 0 )
            {
                $screen = $dom->addElement( 'screen', $dom->firstChild, array( 'name' => 'screen', 'default' => 'default' ) );
            }
            else
            {
                $screen = $listescreen->item(0);
            }
            
            $list = $dom->addElement( 'list', $screen, array('name' => $name ) );
        }
        else
        {
            $list = $item->item(0);
        }
        
        $list->setAttribute( 'visible', '1' );
        
        Datatable::filelistcompletionsetting($dom, $list, $params);
        Datatable::filelistcompletionfilters($dom, $list, $params);
        Datatable::filelistcompletiondatatable($dom, $list, $params);
      
    }
    
    private static function filelistcompletion(\damix\engines\tools\xmldocument $dom, array $params, bool $default = false) : void
    {
        $name = $params['name'];
        
        $item = $dom->xPath( '/compiler/screen/list[@name="' . $name . '"]' );
 
        if( $item && $item->length == 0 )
        {
            $listescreen = $dom->xPath( '/compiler/screen' );
            if( $listescreen && $listescreen->length == 0 )
            {
                $screen = $dom->addElement( 'screen', $dom->firstChild, array( 'completion' => 'screen', 'default' => 'default' ) );
            }
            else
            {
                $screen = $listescreen->item(0);
            }
            
            $list = $dom->addElement( 'list', $screen, array('name' => $name ) );
        }
        else
        {
            $list = $item->item(0);
        }
        
        $list->setAttribute( 'visible', '1' );
        
        Datatable::filelistcompletionsetting($dom, $list, $params);
        Datatable::filelistcompletionfilters($dom, $list, $params);
        Datatable::filelistcompletiondatatable($dom, $list, $params);
      
    }
    
    private static function filelistcompletionsetting(\damix\engines\tools\xmldocument $dom, $list, array $params) : void
    {
        $item = $dom->xPath( 'settings', $list );
        if( $item && $item->length == 0 )
        {
            $settings = $dom->addElement( 'settings', $list );
        }
        else
        {
            $settings = $item->item(0);
        }
        $value = '1';
        $liste = $dom->xPath( 'setting[@name="autoload"]', $settings );
        if( $liste && $liste->length == 0 )
        {
            $autoload = $dom->addElement( 'setting', $settings, array('name' => 'autoload') );
        }
        else
        {
            $autoload = $liste->item(0);
			$value = $autoload->getAttribute( 'value' );
        }
        $autoload->setAttribute( 'value', $value );
    }
    
    private static function filelistcompletionfilters($dom, $list, $params) : void
    {
        $item = $dom->xPath( 'filters', $list );
        if( $item && $item->length == 0 )
        {
            $filters = $dom->addElement( 'filters', $list );
        }
        else
        {
            $filters = $item->item(0);
        }
        
        $dom->removeElement( $filters->childNodes, $filters );
        $column = 0;
        $row = 0;
        if( isset( $params['filter'] ) )
        {
            foreach( $params['filter'] as $data )
            {
                $filter = $dom->addElement( 'filter', $filters );
				
				if( !$data['rows'] > 0 )
				{
					$data['rows'] = 0;
				}
				if( !$data['cols'] > 0 )
				{
					$data['cols'] = $column + 1;
				}
                $filter->setAttribute( 'name', $data['field']);
                $filter->setAttribute( 'header', $data['header']);
                $filter->setAttribute( 'locale', $data['locale']);
                $filter->setAttribute( 'row', $data['rows']);
                $filter->setAttribute( 'column', $data['cols']);
                $filter->setAttribute( 'group', $data['group']);
                $filter->setAttribute( 'datatype', $data['datatype']);
                $filter->setAttribute( 'selector', $data['selector']);
                $filter->setAttribute( 'operator', $data['operator']);
                $filter->setAttribute( 'defaultvalue1', $data['defaultvalue1']);
                $filter->setAttribute( 'defaultvalue2', $data['defaultvalue2']);
                $filter->setAttribute( 'null', $data['null']);
                $filter->setAttribute( 'from', 'orm');
				if( $data['datatype'] == 'select' )
				{
					$filter->setAttribute( 'multiple', 'true');
				}
                
                if( $row < $data['rows'] )
                {
                    $row = $data['rows'];
                }
                if( $column < $data['cols'] )
                {
                    $column = $data['cols'];
                }
            }
        }
        $filters->setAttribute( 'cols', $column + 1);
        $filters->setAttribute( 'rows', $row + 1);

    }
    
    private static function filelistcompletiondatatable($dom, $list, $params) : void
    {
        $item = $dom->xPath( 'datatable', $list );
        if( $item && $item->length == 0 )
        {
            $datatable = $dom->addElement( 'datatable', $list );
        }
        else
        {
            $datatable = $item->item(0);
        }
        
        $item = $dom->xPath( 'columns', $datatable );
        if( $item && $item->length == 0 )
        {
            $columns = $dom->addElement( 'columns', $datatable );
        }
        else
        {
            $columns = $item->item(0);
        }

        $remove = $dom->removeElement( $columns->childNodes, $columns );
        
        if( isset( $params['data'] ) )
        {
            foreach( $params['data'] as $data )
            {
				$column = null;
				foreach( $remove as $i => $node )
				{
					try
					{
						if( $node->hasAttribute( 'name' ) )
						{
							if( $node->getAttribute( 'name' ) == $data['field' ] )
							{
								$columns->appendChild( $node );
								$column = $node;
								unset( $remove[$i] );
								continue;
							}
						}
					}
					catch( Exception $e)
					{
					}
				}
				if( $column === null )
				{
					$column = $dom->addElement( 'column', $columns );
				}
             
                $column->setAttribute( 'name', $data['field']);
                $column->setAttribute( 'header', $data['header']);
                $column->setAttribute( 'datatype', $data['datatype']);
                $column->setAttribute( 'order', $data['order']);
                $column->setAttribute( 'from', $data['from']);
                $column->setAttribute( 'sort', $data['sort']);
                $column->setAttribute( 'locale', $data['locale']);
                $column->setAttribute( 'visible', $data['visible'] ?? 0);
				if( isset( $data['functions'] ) )
				{
					if( isset( $data['functions']['content'] ) && $data['functions']['content'] != '' )
					{
						$content = $dom->xPath( 'functions/content', $column );
						if( $content->length == 1 )
						{
							$content = $content->item(0);
						}
						else
						{
							$functions = $dom->xPath( 'functions', $column );
							if( $functions->length == 0 )
							{
								$functions = $dom->addElement( 'functions', $column );
							}
							else
							{
								$functions = $functions->item(0);
							}
							$content = $dom->addElement( 'content', $functions );
						}
						
						$content->textContent = $data['functions']['content'];
					}
				}
				
            }
			
			foreach( $remove as $i => $node )
			{
				$columns->appendChild( $node );
				if(  $node instanceof DOMNode )
				{
					$node->setAttribute( 'visible', 0);
				}
			}
        }
    }
}