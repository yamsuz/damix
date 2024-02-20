<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

class CommandAclupdate
	extends DamixCommands
{
	private string $application;
	
	public function execute(array $params)
	{
		$this->application = $params['a'] ?? null;
		$login = $params['u'] ?? null;
		$password = $params['p'] ?? null;
		
		
		$this->acls();
	}
	
	private function acls()
	{
		$racine = $this->directory . $this->application;
		
		$filename = $racine . DIRECTORY_SEPARATOR . 'configuration' . DIRECTORY_SEPARATOR . 'acl' . DIRECTORY_SEPARATOR . 'default.xml';
		
		$dom = new \damix\engines\tools\xmlDocument();
		
		if( $dom->load( $filename ) )
		{
			$liste = $dom->xPath( '/acls/acl' );
	
			
			
			$acl = \damix\engines\acls\Acl::get();
			
			foreach( $liste as $info )
			{
				$subject = $info->getAttribute( 'subject' );
				
				$acl->addsubject( $subject, '');					
				
				
				foreach( $info->childNodes as $group )
				{
					if( $group->getAttribute( 'name' ) != '' )
					{
						$acl->addgroup($group->getAttribute( 'name' ), $group->getAttribute( 'name' ));
							
						$acl->addright( $subject, $group->getAttribute( 'name' ) );
					}
				}
			}
		}
	}
}