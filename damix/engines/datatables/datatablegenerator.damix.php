<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\datatables;

class DatatableGenerator
   extends \damix\engines\tools\GeneratorContent
{
	private array $_dom = array();
	private string $name;
	private \damix\engines\orm\OrmBaseProperties $orm;
	private \damix\engines\tools\xmlDocument $_document;
    
	public function generate( DatatableSelector $selector ) : bool
    {
        if( $this->open( $selector ) )
        {
            $this->_document = \damix\engines\tools\xmlDocument::createDocument('datatable');
            
            foreach( $this->_dom as $dom )
            {
                $this->compilefiles( $this->_document->childNodes[0], $dom['xml']->childNodes[0] );
            }
            
            $this->generateheader();
            $this->generateorm();
            $this->generatescreen();
			
            $this->writeLine( '<?php' );
            $this->writeLine( 'namespace '. $selector->getNamespace() .';' );
            $this->writeLine( '' );
			$this->writeLine( 'class ' . $selector->getClassName() );
			$this->tab( 1 );
			$this->writeLine( 'extends \damix\engines\datatables\DatatableBase' );
			$this->tab( -1 );
			$this->writeLine( '{' );
            $this->writecontent();
			$this->writeLine( '}' );
			
			
            
            \damix\engines\tools\xfile::write( $selector->getTempPath(), $this->getText() );
            return true;
        }
		
		// \damix\engines\logs\log::dump( $selector );
		return false;
    }
    
    protected function open( DatatableSelector $selector ) : bool
    {
        foreach( $selector->files as $files )
        {
            $dom = new \damix\engines\tools\xmlDocument();
            if( $dom->load( $files[ 'filename' ] ) )
            {
                $this->_dom[] = array( 
                            'version' => $dom->getAttribute( $dom->documentElement, 'version', '1.0' ), 
                            'xml' => $dom
                            );
            }
        }
        
        if( property_exists( $selector, 'completion' ) )
        {
            foreach( $selector->completion as $files )
            {
                $dom = new \damix\engines\tools\xmlDocument();
                if( $dom->load( $files[ 'filename' ] ) )
                {                    
                    $this->_dom[] = array( 
                                'version' => $dom->getAttribute( $dom->documentElement, 'version', '1.0' ), 
                                'xml' => $dom
                                );
                }
            }
        }
        
        return count( $this->_dom ) > 0;
    }
	
	protected function compilefiles( \DOMElement $general, \DOMElement $dom ) : void
    {
        $completion = 'completion';
        
        foreach( $dom->childNodes as $node )
        {
            if( $node instanceof \DOMElement )
            {
                if( ! $node->hasAttribute( $completion ) )
                {
                    $new = $node->cloneNode();
					$new->nodeValue = $node->nodeValue;
                    $new = $this->_document->importNode($new, true);
                    $general->appendChild( $new );

                    $this->compilefiles( $new, $node );
                }
                else
                {
                    $query = '//'. $node->nodeName .'[@name="'. $node->getAttribute( $completion ) .'"]' ;
                    $item = $this->_document->xPath( $query );
                    if( $item && $item->length == 1)
                    {
                        $this->completioncopy( $item[0], $node );
                    }
                }
            }
        }
    }
	
	
	protected function generateheader() : void
	{
		$dom = $this->_document;
        $headers = $dom->xPath( '/datatable/headers/header' );
        
		foreach( $headers as $header )
		{
			$this->addProperty( $header->getAttribute( 'name' ), $this->quote( $header->getAttribute( 'value' ) ), 'string', false, 'protected' );
		}
	}
	
	protected function generateorm() : void
	{
		$dom = $this->_document;
        $orm = $dom->xPath( '/datatable/orm' )->item(0);
        
		
		
		$this->orm = \damix\engines\orm\method\OrmMethod::get( $orm->getAttribute( 'selector' ) );
		$sel = new \damix\engines\orm\OrmSelector( $orm->getAttribute( 'selector' ) );
		
		$ormconditions = $dom->xPath( 'ormconditions/ormcondition', $orm );
		$content = array();
		
		$content[] = '$this->ormselector =  array( ' . $this->quote( 'module' ) . ' => ' . $this->quote( $sel->getPart( 'module' ) ) . ', ' . $this->quote( 'resource' ) . ' => ' . $this->quote( $sel->getPart( 'resource' ) ) . ', ' . $this->quote( 'function' ) . ' => ' . $this->quote( $sel->getPart( 'function' ) ) . ');';
		
		foreach( $ormconditions as $ormcondition )
		{
			switch( $ormcondition->getAttribute( 'type' ) )
			{
				case 'groupbegin':
				case 'groupend':
					$content[] = '$this->condition[] = array(\'type\' => \''. $ormcondition->getAttribute( 'type' ) .'\' );';
					break;
				case 'logic':
					$content[] = '$this->condition[] = array(\'type\' => \''. $ormcondition->getAttribute( 'type' ) .'\', \'value\' => \''. $ormcondition->getAttribute( 'value' ) .'\' );';
					break;
				case 'field':
				case 'string':
					$content[] = '$this->condition[] = array(\'type\' => \''. $ormcondition->getAttribute( 'type' ) .'\', \'table\' => \''. $ormcondition->getAttribute( 'table' ) .'\', \'property\' => \''. $ormcondition->getAttribute( 'property' ) .'\', \'operator\' => \''. $ormcondition->getAttribute( 'operator' ) .'\', \'datatype\' => \''. $ormcondition->getAttribute( 'datatype' ) .'\', \'value1\' => \''. $ormcondition->getAttribute( 'value1' ) .'\', \'value2\' => \''. $ormcondition->getAttribute( 'value2' ) .'\', \'group\' => \''. $ormcondition->getAttribute( 'group' ) .'\' );';
					break;
				case 'js':
					$content[] = '$this->condition[] = array(\'type\' => \''. $ormcondition->getAttribute( 'type' ) .'\', \'table\' => \''. $ormcondition->getAttribute( 'table' ) .'\', \'property\' => \''. $ormcondition->getAttribute( 'property' ) .'\', \'operator\' => \''. $ormcondition->getAttribute( 'operator' ) .'\', \'datatype\' => \''. $ormcondition->getAttribute( 'datatype' ) .'\', \'value1\' => \''. $ormcondition->getAttribute( 'value1' ) .'\', \'value2\' => \''. $ormcondition->getAttribute( 'value2' ) .'\', \'group\' => \''. $ormcondition->getAttribute( 'group' ) .'\', \'name\' => \''. $ormcondition->getAttribute( 'name' ) .'\' );';
					break;
			}
		}
		
		$ormorders = $dom->xPath( 'ormorders/ormorder', $orm );
		foreach( $ormorders as $ormorder )
		{
			$content[] = '$this->orders[ ' . $this->quote( $ormorder->getAttribute( 'screen' ) ) . ' ][] = array( \'name\' => \''. $ormorder->getAttribute( 'name' ) .'\', \'way\' => \''. $ormorder->getAttribute( 'way' ) .'\' );';
		}
		
        $this->appendFunction( 'propertyinit', array(), $content, 'protected', 'void');
        
        $this->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
		
	}
	
	protected function generatescreen() : void
	{
		$dom = $this->_document;
        $screen = $dom->xPath( '/datatable/screen' )->item(0);
        $this->name = $screen->getAttribute( 'default' );
		$this->addProperty( 'default', $this->quote( $screen->getAttribute( 'default' ) ), 'string', false, 'protected' );
		
		$content = array();
		
		$setting = $dom->xPath( 'list/settings/setting[@name="autoload"]', $screen )->item(0);
		
		$this->addProperty( 'autoload', tobool( $setting->getAttribute( 'value' ) ) ? 'true' : 'false', 'bool', false, 'protected' );
		
		$filters = $dom->xPath( 'list/filters', $screen )->item(0);
		
		$this->writefilters( $filters );
		
		$columns = $dom->xPath( 'list/datatable/columns', $screen )->item(0);
		
		$this->writedatatable( $columns );
		
	}
	
	private function writefilters( \DOMElement $filters ): void
	{
		$colorder = array();
		$properties = $this->orm->getProperties();
        foreach( $filters->childNodes as $filter)
        {
			if( $filter->nodeType != XML_ELEMENT_NODE )
			{
				continue;
			}
			
			
			$name = '';
			$datatype = '';
			$locale = '';
			$header = '';
			$group = '';
			$operator = '';
			$defaultvalue1 = '';
			$defaultvalue2 = '';
			$null = '';
			$order = '';
			$multiple = '';
			$enumerate = '';
			
            $from = $this->_document->getAttribute( $filter, 'from' );
			if( $from === 'orm' )
			{
				$ref = $this->_document->getAttribute( $filter, 'ref' );
				if( $struct = \damix\engines\orm\Orm::getDefine( $ref ))
				{
					$orm = $struct['orm'];
					$table = $struct['orm']->name;
					$field = $struct['field'];

					if( $field )
					{
						$name = $field['name'];
						$format = ( empty( $field['format'] ) ? $field['datatype'] : $field['format'] );
						$locale = $field['locale'];
						$combo = $field['combo'];
						$enumerate = $field['enumerate'];
					}
					
					if( ! empty( $combo ) )
					{
						$format = 'select';
					}
				}
				
			}
			$name = $this->_document->getAttribute( $filter, 'name', $name );
			$datatype = $this->_document->getAttribute( $filter, 'datatype', $format );
			$locale = $this->_document->getAttribute( $filter, 'locale', $locale );
			$header = $this->_document->getAttribute( $filter, 'header', $header );
			$group = $this->_document->getAttribute( $filter, 'group', $group );
			$operator = $this->_document->getAttribute( $filter, 'operator', $operator );
			$defaultvalue1 = $this->_document->getAttribute( $filter, 'defaultvalue1', $defaultvalue1 );
			$defaultvalue2 = $this->_document->getAttribute( $filter, 'defaultvalue2', $defaultvalue2 );
			$null = $this->_document->getAttribute( $filter, 'null', $null );
			$order = $this->_document->getAttribute( $filter, 'order', $order );
			$multiple = $this->_document->getAttribute( $filter, 'multiple', $multiple );
			
			$ormfield = $properties[ $name ] ?? null;
            if( $ormfield )
            {
				$table = $ormfield['table'];
				$combo = $ormfield['combo'];
			}
			
			if( empty( $header ) )
			{
				$header = \damix\engines\locales\Locale::get( $locale );
			}
			
			$content = array();
			$content[] = '$this->filters[ \'' . $this->name .'\' ][ '. $this->quote( $name ) .' ] = array(';
			
			$content[] = '\'name\' => '. $this->quote( $name ) .', ';
			$content[] = '\'header\' => '. $this->quote( $header ) .', ';
			$content[] = '\'locale\' => '. $this->quote( $locale ) .', ';
			$content[] = '\'group\' => '. $this->quote( $group ) .', ';
			$content[] = '\'operator\' => '. $this->quote( $operator ) .', ';
			$content[] = '\'datatype\' => '. $this->quote( $datatype ) .', ';
			$content[] = '\'defaultvalue1\' => '. $this->quote( $filter->getAttribute( 'defaultvalue1' ) ) .', ';
			$content[] = '\'defaultvalue2\' => '. $this->quote( $filter->getAttribute( 'defaultvalue2' ) ) .', ';
			$content[] = '\'null\' => '. $this->quote( $filter->getAttribute( 'null' ) ) .', ';
			$content[] = '\'from\' => '. $this->quote( $filter->getAttribute( 'from' ) ) .', ';
			$content[] = '\'order\' => '. $this->quote( $filter->getAttribute( 'order' ) ) .', ';
			$content[] = '\'table\' => '. $this->quote( $table ) .', ';
			$content[] = '\'field\' => '. $this->quote( $name ) .', ';
			$content[] = '\'selector\' => '. $this->quote( $combo ) .', ';
			$content[] = '\'html\' => '. $this->quote( $this->getHtml( array( 'name' => $name, 'placeholder' => $header, 'value1' => $defaultvalue1, 'operator' => $operator, 'multiple' => $multiple, 'locale' => $locale, 'datatype' => $datatype, 'null' => $null, 'selector' => $combo, 'enumerate' => $enumerate, 'class' => 'form-control xdatafiltre' ) ) ) .', ';
		  
			$content[] = ');';

			$colorder[] = array( 'order'=> intval( $filter->getAttribute( 'order' ) ), 'data' => $content );
		
        }
        
        usort($colorder, function ($a, $b){
                    return ($a['order'] <= $b['order']) ? -1 : 1;
                });
        
        foreach( $colorder as $content )
        {
            $this->appendFunction( 'propertyinit', array(), $content['data'], 'protected', 'void');
        }
	}
	
	private function getHtml( array $filter ) : string
    {
        $html = '';
		switch( $filter['datatype'] )
        {
            case 'date':
				$zone = \damix\engines\settings\Setting::getValue('default', 'formcontrols', 'zonecontroldate');
				$html = \damix\engines\zones\Zone::get( $zone, $filter);
                break;
            case 'select':
                $zone = \damix\engines\settings\Setting::getValue('default', 'formcontrols', 'zonecontrolselect');
				$html = \damix\engines\zones\Zone::get( $zone, $filter);
                break;
            case 'bool':
				$zone = \damix\engines\settings\Setting::getValue('default', 'formcontrols', 'zonecontrolbool');
				$html = \damix\engines\zones\Zone::get( $zone, $filter);
                break;
            case 'enum':
				$zone = \damix\engines\settings\Setting::getValue('default', 'formcontrols', 'zonecontrolenum');
				$html = \damix\engines\zones\Zone::get( $zone, $filter);
                break;
            case 'number':
				$zone = \damix\engines\settings\Setting::getValue('default', 'formcontrols', 'zonecontrolnumber');
				$html = \damix\engines\zones\Zone::get( $zone, $filter);
                break;
            case 'string':
            default:
				$zone = \damix\engines\settings\Setting::getValue('default', 'formcontrols', 'zonecontrolstring');
				$html = \damix\engines\zones\Zone::get( $zone, $filter);
                break;
        }
        return $html;
    }
	
	private function writedatatable( \DOMElement $columns ): void
    {
		$colorder = array();
		$contentorder = array();
		
		$properties = $this->orm->getProperties();
		
		foreach( $columns->childNodes as $column )
		{
			if( $column->nodeType != XML_ELEMENT_NODE )
			{
				continue;
			}
			
			$name = '';
			$format = '';
			$locale = '';
			if( $column->getAttribute( 'from' ) === 'orm' )
			{
				if( $struct = \damix\engines\orm\Orm::getDefine( $column->getAttribute( 'ref' ) ))
				{
					$orm = $struct['orm'];
					$table = $struct['orm']->name;
					$field = $struct['field'];
					
					if( $field )
					{
						$name = $field['name'];
						$format = ( empty( $field['format'] ) ? $field['datatype'] : $field['format'] );
						$locale = $field['locale'];
					}
					
					$name = $properties[ $table . '_' . $name ]['alias'] ?? $name;
				}
				
			}
			$name = $this->_document->getAttribute( $column, 'name', $name );
			$datatype = $this->_document->getAttribute( $column, 'datatype', $format );
			$locale = $this->_document->getAttribute( $column, 'locale', $locale );
			$header = $this->_document->getAttribute( $column, 'header' );
			
			if( empty( $header ) && ! empty( $locale ))
			{
				$header = \damix\engines\locales\Locale::get( $locale );
			}
			
			$content = array();
			$content[] = '$this->datatable[ \'' . $this->name .'\' ][] = array(';
			
			$content[] = '\'name\' => '. $this->quote( $name ) .', ';
			$content[] = '\'header\' => '.  $this->quote( $header ) .', ';
			$content[] = '\'locale\' => '.  $this->quote( $locale ) .', ';
			$content[] = '\'datatype\' => '.  \damix\engines\orm\request\structure\OrmDataType::castToGenerate( $datatype ) .', ';
			$content[] = '\'from\' => '.  $this->quote( $column->getAttribute( 'from' ) ) .', ';
			$content[] = '\'order\' => '.  \damix\engines\tools\xTools::parseInt( $column->getAttribute( 'order' ) ) .', ';
			$content[] = '\'width\' => '.  $this->quote( $column->getAttribute( 'width' ) ) .', ';
			$content[] = '\'couleur\' => '.  $this->quote( $column->getAttribute( 'couleur' ) ) .', ';
			$content[] = '\'badge\' => '.  $this->quote( $column->getAttribute( 'badge' ) ) .', ';
			$content[] = '\'visible\' => '. ( tobool( $column->getAttribute( 'visible' ) ) ? 'true' : 'false' ) .', ';
			
			if( $column->hasChildNodes() )
			{
				foreach( $column->childNodes as $elt )
				{
					if( $elt->nodeType != XML_ELEMENT_NODE )
					{
						continue;
					}
					if( $elt->name == 'functions' )
					{
						$content[] = '\'functions\' => array( ';
						foreach( $elt->childNodes as $functions )
						{
							if( $functions->nodeType != XML_ELEMENT_NODE )
							{
								continue;
							}
							$formules = $functions->firstChildren();
							if( $formules !== null )
							{
								$content[] = $this->quote( $functions->name ) . ' => ' . $this->quote( $formules->text ) .', ';
							}
						}
						$content[] = '), ';
					}
				}
			}
			
			$content[] = ');';
			
			if( $column->hasAttribute( 'sort' ) && $column->getAttribute( 'sort' ) != '' )
			{
				$contentorder[] = '$this->orders[ \'' . $this->name .'\' ][] = array( ';
				$contentorder[] = '\'name\' => '.  $this->quote( $column->getAttribute( 'name' ) ) .', ';
				$contentorder[] = '\'way\' => '.  $this->quote( $column->getAttribute( 'sort' ) ) .' ';
				$contentorder[] = ');';
			}
		
			$colorder[] = array( 'order'=> intval( $column->getAttribute( 'order' ) ), 'data' => $content );
		}
		
		usort($colorder, function ($a, $b){
				if ($a['order'] == $b['order']) {
					return 0;
				}
				return ($a['order'] < $b['order']) ? -1 : 1;
			});
		foreach( $colorder as $content )
		{
			$this->appendFunction( 'propertyinit', array(), $content['data'], 'protected', 'void');
		}
		$this->appendFunction( 'propertyinit', array(), $contentorder, 'protected', 'void');
	
		$this->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
    }
	
}