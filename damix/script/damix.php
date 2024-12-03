#!/cli/php
<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
include('damixcommands.php');


function my_autoload_register($class) {
    $class = preg_replace( '/\\\/', DIRECTORY_SEPARATOR, $class);
    
    $filename = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . strtolower($class) . '.damix.php';
    if( is_readable( $filename ) )
    {
        require_once( $filename );
    }
}

spl_autoload_register('my_autoload_register');

$short_options = "a:e:m:c:d:u:p:o:r:f:k:h";
$long_options = [];


$options = getopt($short_options, $long_options);


if(isset($options["a"]) ) {
    $application = $options["a"];
}


if(isset($options["h"]) || count($options)  == 0) {
    help();
	return;
}



function help()
{
	print "Usage: damix [args....]" . "\r\n";	
	print "  " . "-a [application] -e appcreate : création d'une application" . "\r\n";
	print "  " . "-a [application] -e appdelete : suppression d'une application" . "\r\n";
	print "  " . "-a [application] -e modulecreate -m [nomModule] : création d'un module" . "\r\n";
	print "  " . "-a [application] -e controllercreate -m [nomModule] -c [nomController] : création d'un controller" . "\r\n";
	print "  " . "-a [application] -e cacheclear : vider le cache (sauf la structure)" . "\r\n";
	print "  " . "-a [application] -e cacheclearall : vider tout le cache" . "\r\n";
	print "  " . "-a [application] -e activateauth -d db : activation de l'authentification et autorisation" . "\r\n";
	print "  " . "-a [application] -e bddtablecreate : crée ttoutes les tables" . "\r\n";
	print "  " . "-a [application] -e bddstoredcreate : crée toutes les procédures stockées" . "\r\n";
	print "  " . "-a [application] -e bddtablealter : mettre à jour les tables" . "\r\n";
	print "  " . "-a [application] -e bddtableindex : crée les index et les foreign key" . "\r\n";
	print "  " . "-a [application] -e bddupgrade : upgrade de la bdd" . "\r\n";
	print "  " . "-a [application] -e useradd -u [login] -p [password]: ajouter un utilisateur" . "\r\n";
	print "  " . "-a [application] -e ormdiscover -m [nomModule] : Autodiscover de la base de donnée" . "\r\n";
	print "  " . "-a [application] -e aclupdate : Mise a jour des acls" . "\r\n";
	print "  " . "-a [application] -e pagecreate -m [nomModule] -c [nom] : Creation d'une page" . "\r\n";
	print "  " . "-a [application] -e templatecreate -m [nomModule] -c [nomZone] : Creation d'un template" . "\r\n";
	print "  " . "-a [application] -e zonecreate -m [nomModule] -c [nomZone] : Creation d'une zone" . "\r\n";
	print "  " . "-a [application] -e classcreate -m [nomModule] -c [nomClasse] : Creation d'une classe" . "\r\n";
	print "  " . "-h" . "\t" . "Cette aide" . "\r\n";
}


DamixCommands::run( $options );
// var_dump( $options );