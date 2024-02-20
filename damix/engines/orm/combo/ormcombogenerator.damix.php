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
        $optioncontent = array();
        
        if( $liste->length > 0)
        {
            if( $combo = $liste->item( 0 ) )
            {
				if( preg_match('/~/', $combo->getAttribute( 'orm' ) ))
				{
					$ormselector = $combo->getAttribute( 'orm' );
				}
				else
				{
					$define = \damix\engines\orm\defines\OrmDefines::get();
					$ormselector = $define->get( $combo->getAttribute( 'orm' ) );
				}
                
                $structure = \damix\engines\orm\Orm::getStructure( $ormselector );
                
                $value = $combo->getAttribute( 'value' );
                $color = $combo->getAttribute( 'color' );
                $firstempty = $combo->getAttribute( 'firstempty' );
                $rowcount = intval( $selector-> _parameters[ 'limit' ]['rowcount'] ?? $combo->getAttribute( 'rowcount' ));
                $offset = intval( $selector-> _parameters[ 'limit' ]['offset'] ??  $combo->getAttribute( 'offset' ) );
                $function = $combo->getAttribute( 'function' );
                $parent = $combo->getAttribute( 'parent' );
                
                $data = $structure->getProperty( $value );
                
				$tablecontent = array();
                if( isset( $data['locale'] ) )
                {
					$tablecontent[] = '$this->_title = ' . $this->quote($data['locale']) . ';';
                }
                else
                {
					$tablecontent[] = '$this->_title = ' . $this->quote('') . ';';
                }
                $tablecontent[] = '$this->_color = ' . $this->quote($color) . ';';
                $tablecontent[] = '$this->_orm = ' . $this->quote($ormselector) . ';';
                $tablecontent[] = '$this->_value = ' . $this->quote($value) . ';';
                $tablecontent[] = '$this->_function = ' . $this->quote($function) . ';';
                $tablecontent[] = '$this->_parent = ' . $this->quote($parent) . ';';
                $tablecontent[] = '$this->_firstempty = ' . (tobool( $firstempty ) ? 'true' : 'false') . ';';
                $tablecontent[] = '$this->_rowcount = ' . $rowcount . ';';
                $tablecontent[] = '$this->_offset = ' . $offset . ';';

                
				$this->appendFunction( 'propertyinit', array(), $tablecontent, 'protected', 'void');
        
				
                
                $obj = \damix\engines\orm\Orm::get( $ormselector );
                $o = $obj->getOrdersClear( $function );
                $c = $obj->getConditionsClear( $function );
                $l = $obj->getLimits( $function );
                
                if( $rowcount > 0 )
                {
                    $l->RowCount = $rowcount;
                    $l->Offset = $offset;
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
                    
                    $optioncontent[] = '$this->_property[\'orders\'][] = array(\'property\' => \''. $order->getAttribute( 'property' ) .'\', \'way\' => \''. $order->getAttribute( 'way' ) .'\' );';
                }
                // \damix\engines\logs\log::dump( $obj );
                $listedata = $obj->$function();
                $html = '';
                if( $firstempty )
                {
                    $html .= '<option></option>';
                }
                if( isset( $this->parameters['null'] ) && $this->parameters['null'] )
                {
                    $html .= '<option value="#null#">'. \damix\engines\locales\Locale::get( 'damix~lclcore.filter.combo.null' ) .'</option>';
                }
                $htmlgroupe = array();
                $htmlparent = array();
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
                            case 'string':
                                $display .= $dis->getAttribute( 'name' );
                                break;
                        }
                    }
					
					$this->appendFunction( 'propertyinit', array(), $optioncontent, 'protected');
					$optioncontent = array();
                    $optioncontent[] = '$this->_data[] = array(\'value\' => \''. $info->$value .'\', \'display\' => \''. preg_replace( '/\'/', '\\\'', $display ) .'\', \'idparent\' => \''. ($parent != '' ? $info->$parent : '' ) .'\' );';
                    
                    if( $parent == '' || $info->$parent == '' )
                    {
                        $htmlparent[ $info->$value ] = array( 'value' => $info->$value,
                                    'label' => preg_replace( '/\'/', '\\\'', $display ),
                                    'parent' => ($parent != '' ? $info->$parent : '' ),
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
				 
                foreach( $listedisplay as $dis )
                {
                    $optioncontent = array( 
								'$this->_property[\'option\'][] = array(\'type\' => \''. $dis->getAttribute( 'type' ) .'\', \'value\' => \''. $dis->getAttribute( 'name' ) .'\' );'
								);
					$this->appendFunction( 'propertyinit', array(), $optioncontent, 'protected');
                }
            }
        }
        
        $this->addConstructInit( 'propertyinit', array( '$this->propertyinit();' ) );
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