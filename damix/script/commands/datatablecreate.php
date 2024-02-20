<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandDatatablecreate
	extends DamixCommands
{
	private string $module;
	private string $resource;
	private string $function;
	private string $selectororm;
	private string $key;
	
	public function execute(array $params)
	{
		$application = $params['a'] ?? null;
		
		if( $application === null )
		{
			self::display( 'Le nom de l\'application est obligatoire.' );
			return;
		}
		
		$this->module = $params['m'] ?? null;
		
		if( $this->module === null )
		{
			self::display( 'Le nom du module est obligatoire.' );
			return;
		}
		
		$this->resource = $params['r'] ?? null;
		
		if( $this->resource === null )
		{
			self::display( 'Le nom de la ressource est obligatoire.' );
			return;
		}
		
		$this->function = $params['f'] ?? null;
		
		if( $this->function === null )
		{
			self::display( 'Le nom de la function est obligatoire.' );
			return;
		}
		
	
		$this->selectororm = $params['o'] ?? null;
		
		if( $this->selectororm === null )
		{
			self::display( 'Le sélecteur de l\'orm est obligatoire.' );
			return;
		}
	
		$this->key = $params['k'] ?? null;
		
		if( $this->key === null )
		{
			self::display( 'La clé de la table est obligatoire.' );
			return;
		}
			
		$this->createdatatable();
		// $content = array();
		
	
		// $filename = $this->directory . $application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'zones' . DIRECTORY_SEPARATOR . $zone . '.class.php';
		
		// \damix\engines\tools\xFile::write($filename, implode( "\r\n", $content ));
		
	}
	
	private function createdatatable()
	{
		$datatableselector = new \damix\engines\datatables\DatatableSelector( $this->module . '~' . $this->resource . ':' . $this->function );
		
		$ormmethod = \damix\engines\orm\method\OrmMethod::get( $this->selectororm );
				
		$xml = \damix\engines\tools\xmlDocument::createDocument( 'datatable' );
		$datatable = $xml->firstChild;
		$xml->setAttribute($datatable, 'version', '1.0');
		$xml->setAttribute($datatable, 'confcompletion', 'replace');
		
		$headers = $xml->addElement( 'headers', $datatable, array() );
		$xml->addElement( 'header', $headers, array( 'name' => 'title', 'value' => $this->module . '~lcltitle.datatable.' .  $this->module . '.' . $this->resource . '.' . $this->function) );
		$xml->addElement( 'header', $headers, array( 'name' => 'key', 'value' => $this->key ) );
		$xml->addElement( 'header', $headers, array( 'name' => 'module', 'value' => $this->module ) );
		$xml->addElement( 'header', $headers, array( 'name' => 'resource', 'value' => $this->resource ) );
		$xml->addElement( 'header', $headers, array( 'name' => 'function', 'value' => $this->function ) );
		$xml->addElement( 'header', $headers, array( 'name' => 'selection', 'value' => 'multiple' ) );
		$xml->addElement( 'header', $headers, array( 'name' => 'classname', 'value' => '' ) );
		$xml->addElement( 'header', $headers, array( 'name' => 'type', 'value' => 'list' ) );
		
		$orm = $xml->addElement( 'orm', $datatable, array( 'selector' => $this->selectororm ) );
		$xml->addElement( 'ormconditions', $orm, array( ) );
		$xml->addElement( 'ormorders', $orm, array( ) );
		$xml->addElement( 'right', $datatable, array( ) );
		$screen = $xml->addElement( 'screen', $datatable, array( 'name' => 'screen', 'default' => 'default' ) );
		$list = $xml->addElement( 'list', $screen, array( 'name' => 'default', 'visible' => '1' ) );
		$settings = $xml->addElement( 'settings', $list, array( ) );
		$xml->addElement( 'setting', $settings, array( 'name' => 'autoload', 'value' => '1' ) );
		$filters = $xml->addElement( 'filters', $list, array( ) );
		$datatable = $xml->addElement( 'datatable', $list, array( ) );
		$columns = $xml->addElement( 'columns', $datatable, array( ) );
		
		$i = 1;
		foreach( $ormmethod->getProperties() as $property )
		{
			$xml->addElement( 'column', $columns, array( 'ref' => $property['ref'], 'order' => strval($i), 'from' => 'orm', 'visible' => '1' ) );
			$i++;
		}
	
		$filename = $datatableselector->getFileDefault();
		
		$xml->saveDocument( $filename );
		
	}
}