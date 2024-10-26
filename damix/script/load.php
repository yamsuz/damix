<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/

require('..\..\apps\applitest\application.init.php');

$dir = \damix\application::getPathTemp();

rrmdir( $dir );

function rrmdir(string $dir) : void
{ 
	if (is_dir($dir)) 
	{ 
		$objects = scandir($dir);
		foreach ($objects as $object) 
		{ 
			if ($object != "." && $object != "..") 
			{ 
				if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
					rrmdir($dir. DIRECTORY_SEPARATOR .$object);
				else
					@unlink($dir. DIRECTORY_SEPARATOR .$object); 
			} 
		}
		rmdir($dir); 
	}
	else
	{
		@unlink($dir); 
	}
}