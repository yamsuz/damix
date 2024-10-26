<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\combo;

class OrmComboGenerator
    extends \damix\engines\tools\GeneratorContent
{
    private array $parameters = array();
	private array $_dom = array();
    private \damix\engines\tools\xmlDocument $_document;
    
    public function generate( $selector, $params )
    {
		$this->parameters = $params;
        if( $this->open( $selector ) )
        {
            $this->_document = \damix\engines\tools\xmldocument::createDocument('combos');
            
            foreach( $this->_dom as $dom )
            {
                $this->compilefiles( $this->_document->childNodes[0], $dom['xml']->childNodes[0] );
            }
            
            $this->writeLine( '<?php' );
            $this->writeLine( 'namespace orm\combo;' );
            $this->writeLine( '' );

            
            $this->generatefile( $selector );
            $this->writefile( $selector );
            
            
            \damix\engines\tools\xfile::write( $selector->getTempPath(), $this->getText() );
            return true;
        }
        return false;
    }
    
	public function clearTemp( $selector, $params )
	{
		$this->parameters = $params;
		
		$orm = $selector->getPart( 'resource' ) ;
        if( $this->open( $selector ) )
        {
			foreach( $this->_dom as $dom )
            {
				$combos = $dom['xml']->childNodes[0];
				foreach( $combos->childNodes as $combo )
				{
					if( $combo->getAttribute( 'orm' ) == $orm )
					{
						$selcombo = new OrmComboSelector( $combo->getAttribute( 'name' ) );
						if( $selcombo )
						{
							\damix\engines\tools\xfile::deleteDir( dirname( $selcombo->getTempPath()) );
						}
					}
				}
			}
		}
	}
	
    protected function compilefiles( $general, $dom )
    {
        $completion = 'completion';
        
        foreach( $dom->childNodes as $node )
        {
            if( $node instanceof \DOMElement )
            {
                if( ! $node->hasAttribute( $completion ) )
                {
                    $new = $node->cloneNode();
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
    	
    protected function open( $selector )
    {
        foreach( $selector->files as $files )
        {
            $dom = new \damix\engines\tools\xmldocument();
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
                $dom = new \damix\engines\tools\xmldocument();            
                if( $dom->load( $files[ 'filename' ] ) )
                {
                    if( $this->_driver !== null )
                    {
                        $this->_driver = $dom->getAttribute( $dom->documentElement, 'driver' );
                    }
                    else
                    {
                        throw new \Exception('Error driver');
                    }
                    
                    $this->_dom[] = array( 
                                'version' => $dom->getAttribute( $dom->documentElement, 'version', '1.0' ), 
                                'xml' => $dom
                                );
                }
            }
        }
        
        return count( $this->_dom ) > 0;
    }
    
    private function generatefile( $selector )
    {
        $dom = $this->_document;
        $liste = $dom->xPath( '/combos/combo[@name="'. $selector->getPart( 'resource' ) .'"]' );
        
        $tablecontent = array();
        
        
        if( $liste->length > 0)
        {
            if( $combo = $liste->item( 0 ) )
            {
				$params = array();
				if( preg_match('/~/', $combo->getAttribute( 'orm' ) ))
				{
					$params['ormselector'] = $combo->getAttribute( 'orm' );
				}
				else
				{
					$define = \damix\engines\orm\defines\OrmDefines::get();
					$params['ormselector'] = $define->get( $combo->getAttribute( 'orm' ) );
				}
                
                $structure = \damix\engines\orm\Orm::getStructure( $params['ormselector'] );
                
				
				
				$params['value'] = $combo->getAttribute( 'value' );
                $params['color'] = $combo->getAttribute( 'color' );
                $params['firstempty'] = $combo->getAttribute( 'firstempty' );
                $params['remotedata'] = tobool($combo->getAttribute( 'remotedata' ));
                $params['rowcount'] = intval( $selector-> _parameters[ 'limit' ]['rowcount'] ?? $combo->getAttribute( 'rowcount' ));
                $params['offset'] = intval( $selector-> _parameters[ 'limit' ]['offset'] ??  $combo->getAttribute( 'offset' ) );
                $params['function'] = $combo->getAttribute( 'function' );
                $params['parent'] = $combo->getAttribute( 'parent' );
                
                $data = $structure->getProperty( $params['value'] );
                
				$tablecontent = array();
                if( isset( $data['locale'] ) )
                {
					$tablecontent[] = '$this->_title = ' . $this->quote($data['locale']) . ';';
                }
                else
                {
					$tablecontent[] = '$this->_title = ' . $this->quote('') . ';';
                }
                $tablecontent[] = '$this->_color = ' . $this->quote($params['color']) . ';';
                $tablecontent[] = '$this->_orm = ' . $this->quote($params['ormselector']) . ';';
                $tablecontent[] = '$this->_value = ' . $this->quote($params['value']) . ';';
                $tablecontent[] = '$this->_function = ' . $this->quote($params['function']) . ';';
                $tablecontent[] = '$this->_parent = ' . $this->quote($params['parent']) . ';';
                $tablecontent[] = '$this->_remotedata = ' . ($params['remotedata'] ? 'true' : 'false') . ';';
                $tablecontent[] = '$this->_firstempty = ' . (tobool( $params['firstempty'] ) ? 'true' : 'false') . ';';
                $tablecontent[] = '$this->_rowcount = ' . $params['rowcount'] . ';';
                $tablecontent[] = '$this->_offset = ' . $params['offset'] . ';';

                
				$this->appendFunction( 'propertyinit', array(), $tablecontent, 'protected', 'void');
        
				$listeorder = $dom->xPath( 'orders/order', $combo );
		
				foreach( $listeorder as $order )
				{
					$optioncontent = '$this->_property[\'orders\'][] = array(\'property\' => \''. $order->getAttribute( 'property' ) .'\', \'way\' => \''. $order->getAttribute( 'way' ) .'\' );';
					$this->appendFunction( 'propertyinit', array(), array( $optioncontent ), 'protected');
				}
				
				$listedisplay = $dom->xPath( 'options/display', $combo );
				foreach( $listedisplay as $dis )
				{
					$optioncontent =  '$this->_property[\'option\'][] = array(\'type\' => \''. $dis->getAttribute( 'type' ) .'\', \'value\' => \''. $dis->getAttribute( 'name' ) .'\' );';
					
					$this->appendFunction( 'propertyinit', array(), array( $optioncontent ), 'protected');
				}
				
                if( ! $params['remotedata'] )
				{
					$this->loadhtml( $combo, $params );
				}
            }
        }
        
        $this->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
    }
    
    
	private function loadhtml( \DomNode $combo, array $params ) : void
	{
		$dom = $this->_document;
		$optioncontent = array();
		$obj = \damix\engines\orm\Orm::get( $params['ormselector'] );
		$o = $obj->getOrdersClear( $params['function'] );
		$c = $obj->getConditionsClear( $params['function'] );
		$l = $obj->getLimits( $params['function'] );
		
		if( $params['rowcount'] > 0 )
		{
			$l->RowCount = $params['rowcount'];
			$l->Offset = $params['offset'];
		}
		
		$listefilters = $dom->xPath( 'filters/filter', $combo );
		foreach( $listefilters as $filter)
		{
			switch( $filter->getAttribute( 'type' ) )
			{
				case 'string':
					$c->addFieldString( $filter->getAttribute( 'property' ), $filter->getAttribute( 'operator' ), $filter->getAttribute( 'value' ), 'combo' );
					break;
			}
		}
		
		$listedisplay = $dom->xPath( 'options/display', $combo );
		$listeorder = $dom->xPath( 'orders/order', $combo );
		
		foreach( $listeorder as $order )
		{
			$ormorder = new \damix\engines\orm\request\structure\OrmOrder();

			$ormorder->setColumn( $order->getAttribute( 'property' ) );
			$ormorder->setWay(\damix\engines\orm\request\structure\OrmOrderWay::cast($order->getAttribute( 'way' )) );
			
			$o->add( $ormorder );
		}
		
		$listedata = $obj->{$params['function']}();
		$html = '';
		if( $params['firstempty'] && !(isset( $this->parameters['null'] ) && $this->parameters['null']) )
		{
			$html .= '<option></option>';
		}
		elseif( isset( $this->parameters['null'] ) && $this->parameters['null'] )
		{
			$html .= '<option value="#null#">'. \damix\engines\locales\Locale::get( 'damix~lclcore.filter.combo.null' ) .'</option>';
		}
		$htmlgroupe = array();
		$htmlparent = array();
		$params['value'] = $this->getProperty( $params['value'] );
		foreach( $listedata as $info )
		{
			$display = '';
			foreach( $listedisplay as $dis )
			{
				switch( $dis->getAttribute( 'type' ) )
				{
					case 'property':
						$display .= $info->{$dis->getAttribute( 'name' )};
						break;
					case 'reference':
						if( $struct = \damix\engines\orm\Orm::getDefine( $dis->getAttribute( 'name' )))
						{
							$field = $struct['field'];

							if( $field )
							{
								$params['display'] = $struct['orm']->realname . '_' . $field['realname'];
								$display .= $info->{$params['display']};
							}
						}
					
						break;
					case 'string':
						$display .= $dis->getAttribute( 'name' );
						break;
				}
			}
			
			
			$optioncontent = array();
			$optioncontent[] = '$this->_data[] = array(\'value\' => \''. $info->{$params['value']} .'\', \'display\' => \''. preg_replace( '/\'/', '\\\'', $display ) .'\', \'idparent\' => \''. ($params['parent'] != '' ? $info->{$params['parent']} : '' ) .'\' );';
			
			if( $params['parent'] == '' || $info->{$params['parent']} == '' )
			{
				$htmlparent[ $info->{$params['value']} ] = array( 'value' => $info->{$params['value']},
							'label' => preg_replace( '/\'/', '\\\'', $display ),
							'parent' => ($params['parent'] != '' ? $info->$parent : '' ),
							);
			}
			else
			{
				$htmlgroupe[ $info->$parent ][] = array( 'value' => $info->$value,
							'label' => preg_replace( '/\'/', '\\\'', $display ),
							'parent' => $info->$parent,
							);
			}
		}

		$default = $this->parameters['default'] ?? null;
		foreach( $htmlparent as $elt )
		{
			
			if( isset( $htmlgroupe[ $elt['value'] ] ) )
			{
				$html .= '<optgroup label="' . $elt['label'] . '">';
				foreach( $htmlgroupe[ $elt['value'] ] as $data )
				{
					$html .= '<option value="'. $data[ 'value' ] .'">'. $data[ 'label' ] .'</option>';
				}
				$html .= '</optgroup>';
			}
			else
			{
				$html .= '<option value="'. $elt[ 'value' ] .'"'. ($default && $elt[ 'value' ] == $default ? ' selected="selected"' : '').'>'. $elt[ 'label' ] .'</option>';
			}
		}
		
		
		$this->appendFunction( 'propertyinit', array(), $optioncontent, 'protected');
		$optioncontent = array(
						'$this->_html = \''. $html .'\';'
						);
		
		$this->appendFunction( 'propertyinit', array(), $optioncontent, 'protected');
		 
		

	}
	
	public function getProperty(string $value): string
	{
		if( $struct = \damix\engines\orm\Orm::getDefine( $value ))
		{
			$field = $struct['field'];

			if( $field )
			{
				return $struct['orm']->realname . '_' . $field['realname'];
			}
		}
		return $value;
	}
	
	
    private function writefile( $selector )
    {
        $this->writeLine( 'class ' . $selector->getClassName() );
        $this->tab( 1 );
        $this->writeLine( 'extends \damix\engines\orm\combo\OrmComboBase' );
        $this->tab( -1 );
        $this->writeLine( '{' );
        $this->tab( 1 );
        
        $this->tab( -1 );
        
        
        $this->writecontent();
        
        
        $this->writeLine( '}' );
    }
    
}