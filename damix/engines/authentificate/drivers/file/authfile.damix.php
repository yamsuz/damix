<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\authentificate;


class AuthFile
	extends \damix\engines\authentificate\AuthBase
{
	
	private function findUser($login) : ?string
	{
		$filename = $this->getFilename();
		if( file_exists( $filename ) )
		{
			$content = file($filename);
			foreach( $content as $line )
			{
				$split = preg_split( '/:/', $line);
				if( count($split) == 2)
				{
					if( strtolower($split[0]) === strtolower($login) )
					{
						return str_replace(array("\r", "\n"),"",$split[1]);
					}
				}
			}
		}
		
		return null;
	}
	
	public function getDriverName() : string
	{
		return 'file';
	}
	
	private function getFilename() : string
	{
		$filename = \damix\engines\settings\Setting::getValue('default', 'auth', 'filename');
		if( $filename )
		{
			return $filename;
		}
		return '';
	}
	
	public function verifyPassword( string $login, string $password ) : bool
	{
		$passwordhash = $this->findUser( $login );
		if( $passwordhash !== null )
		{
			if( $this->checkPassword( $passwordhash, $password ) )
			{
				return true;
			}
		}
		return false;
	}
	
	public function userNew( string $login, string $password ) : bool
	{
		$filename = $this->getFilename();
		
		if( $this->findUser( $login ) === null )
		{
			$content = $login . ':' . $this->cryptPassword($password) ."\n" ;
			file_put_contents($filename, $content, FILE_APPEND );
			
			return true;
		}
		
		return false;
	}
	
	public function changePassword( string $login, string $password ) : bool
	{
		$filename = $this->getFilename();
		$content = file($filename);
		foreach( $content as $i=>$line )
		{
			$split = preg_split( '/:/', $line);
			if( count($split) == 2)
			{
				if( strtolower($split[0]) === strtolower($login) )
				{
					$content[$i] = $login . ':' . $this->cryptPassword($password)."\r\n";
				}
			}
		}
		
		file_put_contents($filename, implode("", $content));
		
		return true;
	}
	
	
	public function userDelete( string $login ) : bool
	{
		$filename = $this->getFilename();
		if( file_exists( $filename ) )
		{
			$content = file($filename);
			$delete = -1;
			if( $content )
			{
				foreach( $content as $i=>$line )
				{
					$split = preg_split( '/:/', $line);
					if( count($split) == 2)
					{
						if( strtolower($split[0]) === strtolower($login) )
						{
							$delete = $i;
						}
					}
				}
				if( $delete > -1 )
				{
					unset( $content[$delete] );
					file_put_contents($filename, implode("", $content));
				}
				return true;
			}
		}
		
		return false;
	}
	
	
}