<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\tools;

class xFile
{
	private static \damix\engines\settings\SettingBase $config;
	
	private static function loadconfig()
	{
		self::$config = \damix\engines\settings\Setting::get('default');
	}
	
    public static function write(string $file, string $data) : bool
    {
		$_dirname=dirname($file);
       
		self::createDir($_dirname);
		if(file_exists($file)){
			unlink($file);
		}
        
		$res = file_put_contents( $file, $data );
		if( $res === false )
		{
			return $res;
		}
		$settings = \damix\engines\settings\Setting::get( 'default' );
		$filesystem = $settings->getAllSection( 'filesystem' );

		if( isset( $filesystem['filechmod'] ) )
		{
			( self::checkOctal( $filesystem['filechmod'] ) ) ? chmod( $file, octdec( $filesystem['filechmod'] ) ) : throw new \damix\core\exception\CoreException("chmod does not have a valid octal value");
		}
		else
	    {
		    throw new \damix\core\exception\CoreException("filechmod does not exist in filesystem section or filechmod value is not string valid");
	    }

		if( isset( $filesystem['userchown'] ) && is_string( $filesystem['userchown'] ) )
		{
			chown( $file, $filesystem['userchown'] );
		}
		else
		{
			throw new \damix\core\exception\CoreException("userchown does not exist in filesystem section or userchown value is not string valid");
		}

		if( isset( $filesystem['groupchown'] ) && is_string( $filesystem['groupchown'] ) )
		{
			chgrp( $file, $filesystem['groupchown'] );
		}
		else
		{
			throw new \damix\core\exception\CoreException("groupchown does not exist in filesystem section or groupchown value is not string valid");
		}
		return true;
	}
	
	private static function checkOctal( $chmod )
	{
		return preg_match('/^(?:0o|0)[0-7]{1,}$/i', $chmod);
	}
	
    public static function read(string $file): string|false
    {
		if(file_exists($file)){		
			return file_get_contents( $file );
		}
        
		return false;
	}
    
	public static function createDir(string $dir) : bool
	{
		if(!file_exists($dir)){
			self::createDir(dirname($dir));
            @mkdir($dir);
			
			$settings = \damix\engines\settings\Setting::get( 'default' );
			$filesystem = $settings->getAllSection( 'filesystem' );

			if( isset( $filesystem['dirchmod'] ) )
			{
				( self::checkOctal( $filesystem['dirchmod'] ) ) ? chmod( $dir, octdec( $filesystem['dirchmod'] ) ) : throw new \CoreException("chmod does not have a valid octal value");
			}
			else
			{
				throw new \CoreException("dirchmod does not exist in filesystem section or dirchmod value is not string valid");
			}

			if( isset( $filesystem['userchown'] ) && is_string( $filesystem['userchown'] ) )
			{
				chown( $dir, $filesystem['userchown'] );
			}
			else
			{
				throw new \CoreException("userchown does not exist in filesystem section or userchown value is not string valid");
			}

			if( isset( $filesystem['groupchown'] ) && is_string( $filesystem['groupchown'] ) )
			{
				chgrp( $dir, $filesystem['groupchown'] );
			}
			else
			{
				throw new \CoreException("groupchown does not exist in filesystem section or groupchown value is not string valid");
			}
			
			return true;
		}
		return false;
	}
    
	public static function remove(string $file) : bool{
		if(file_exists($file)){
			unlink($file);
			return true;
		}
		return false;
	}
	
	public static function deleteDir( string $dirname, bool $recursive = true ) : void
    {
        if( is_dir( $dirname ) )
        {
            if( $dir = dir( $dirname ) )
            {
                while( ( $entry = $dir->read() ) !== false )
                {
                    if( $entry != '.' && $entry != '..' )
                    {
                        $fullentry = realpath( realpath( $dirname ) . DIRECTORY_SEPARATOR . $entry );
                        
                        if( is_dir( $fullentry ) )
                        {
                            if( $recursive )
                            {
                                xFile::deleteDir( $fullentry, $recursive );
                                if( xFile::isEmpty( $fullentry ) )
                                {
                                    rmdir( $fullentry );
                                }
                            }
                            else
                            {
                                if( xFile::isEmpty( $fullentry ) )
                                {
                                    rmdir( $fullentry );
                                }
                            }
                        }
                        elseif( file_exists( $fullentry ) && is_file( $fullentry ) )
                        {
                            unlink( $fullentry );
                        }
                    }
                }
                $dir->close();
            }
			if( xFile::isEmpty( $dirname ) )
			{
				rmdir($dirname);
			}
        }
    }
	
	public static function copy(string $source, string $destination ) : void
	{
		if( is_dir( $source ) )
		{
			self::createDir( $destination );
            $objects = scandir($source);
            if( sizeof($objects) > 0 )
            {
                foreach( $objects as $file )
                {
                    if( $file == "." || $file == ".." )
                        continue;
                    
                    if( is_dir( $source . DIRECTORY_SEPARATOR . $file ) )
                    {
                        self::copy( $source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file );
                    }
                    else
                    {
                        copy( $source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file );
                    }
                }
            }
		}
		elseif( is_file($source) )
        {
            copy($source, $destination);
        }
	}
	
	public static function isEmpty( string $dirname ) : bool
    {
        $empty = true;
        if( is_dir( $dirname ) )
        {
            if( $dir = dir( $dirname ) )
            {
                while( ( $entry = $dir->read() ) !== false )
                {
                    if( $entry != '.' && $entry != '..' )
                    {
                        $empty = false;
                        break;
                    }
                }
                $dir->close();
            }
        }
		else
		{
			return false;
		}
        return $empty;
    }
}