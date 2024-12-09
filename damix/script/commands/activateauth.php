<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandActivateauth
	extends DamixCommands
{
	private string $application;
	
	public function execute(array $params)
	{
		$this->application = $params['a'] ?? null;
		$driver = $params['d'] ?? null;
		
		if( $driver === null )
		{
			self::display( 'Le nom du driver est obligatoire.' );
			return;
		}
		$racine = $this->directory . $this->application;
		
		$filename = $racine . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'default.cfg.xml';
		$dom = new \damix\engines\tools\xmlDocument();
		$dom->load($filename );
		
		$setting = $dom->xPath( '/setting' )->item(0);
		if( $setting )
		{
			$plugins = $this->noteadd( $dom, $setting, 'section', array( 'name' => 'plugins' ));
			$this->noteadd( $dom, $plugins, 'config', array( 'value' => 'auth'));
			$this->noteadd( $dom, $plugins, 'config', array( 'value' => 'acl'));
			
			$section = $this->noteadd( $dom, $setting, 'section', array( 'name' => 'auth' ));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'driver', 'value' => $driver));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'sessionname', 'value' => 'DAMIX_USER'));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'auth_required', 'value' => 'true'));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'formauth', 'value' => 'auth~auth:index'));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'auth_error', 'value' => 'auth~auth:index'));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'userdummy', 'value' => 'auth~userdummy'));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'error_action', 'value' => 'video~ctrliste:index'));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'password_salt', 'value' => '$2a$07$usesomesillystringforsalt$'));
		
			$plugins = $dom->xPath( 'section[@name="plugins"]', $setting )->item(0);
			if( $plugins )
			{
				$config = $dom->xPath( 'config[@value="auth"]', $plugins )->item(0);
				if( ! $config )
				{
					$config = $dom->createElement( 'config' );
					$config->setAttribute( 'value', 'auth' );
					$plugins->insertBefore( $config, $plugins->firstChild );
				}
			}

			$section = $this->noteadd( $dom, $setting, 'section', array( 'name' => 'acl' ));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'driver', 'value' => 'db'));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'profile', 'value' => ''));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'ormsubjects', 'value' => 'auth~tormaclsubjects'));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'ormrights', 'value' => 'auth~tormaclrights'));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'ormusersgroups', 'value' => 'auth~tormaclusersgroups'));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'ormgroups', 'value' => 'auth~tormaclgroups'));
			$this->noteadd( $dom, $section, 'config', array( 'name' => 'sormaclsright', 'value' => 'auth~sormaclsright'));
			
			$dom->save( $filename );
		}
		
		$sel = new \damix\engines\orm\defines\OrmDefinesSelector();
		$domdefine = new \damix\engines\tools\xmlDocument();
		if( file_exists( $sel->getFileDefault() ) )
		{
			$domdefine->load( $sel->getFileDefault() );
		}
		else
		{
			$domdefine = \damix\engines\tools\xmlDocument::createDocument( 'defines' );
			
		}
		$defines = $domdefine->xPath( '/defines' )->item(0);
		if( $defines )
		{
			$this->noteadd( $domdefine, $defines, 'define', array( 'name' => 'ORM_USERS', 'value' => 'auth~tormusers', 'class' => '' ));

			$this->noteadd( $domdefine, $defines, 'define', array( 'name' => 'ORM_SUBJECTS', 'value' => 'auth~tormaclsubjects', 'class' => '' ));
			$this->noteadd( $domdefine, $defines, 'define', array( 'name' => 'ORM_RIGHTS', 'value' => 'auth~tormaclrights', 'class' => '' ));
			$this->noteadd( $domdefine, $defines, 'define', array( 'name' => 'ORM_GROUPS', 'value' => 'auth~tormaclgroups', 'class' => '' ));
			$this->noteadd( $domdefine, $defines, 'define', array( 'name' => 'ORM_USERS_GROUPS', 'value' => 'auth~tormaclusersgroups', 'class' => '' ));
			
			$domdefine->save( $sel->getFileDefault() );
		}
	}
	
	private function noteadd( \damix\engines\tools\xmlDocument $dom, \DOMNode $parent, string $name, array $attribute) : \DOMNode
	{
		$query = '';
		foreach( $attribute as $attrname => $attrvalue )
		{
			$query .= '[@'.$attrname.'="'. $attrvalue . '"]';
		}

		$node = $dom->xPath( $name . $query, $parent )->item(0);
		if( ! $node )
		{
			$node = $dom->addElement( $name, $parent, $attribute);
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
	
}