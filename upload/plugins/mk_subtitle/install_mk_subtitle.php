<?php
require_once PLUG_DIR.'/common_library/common_library.php';
require_once('../includes/common.php');

/**
 * Install locales for this plugin and set admin permissions
 */
global $cbplugin;

if ($cbplugin->is_installed('common_library.php')){
	require_once PLUG_DIR.'/common_library/common_library.php';
	$folder= PLUG_DIR.'/'.basename(dirname(__FILE__))."/lang";
	importLangagePack($folder,'en');
	importLangagePack($folder,'fr');
	installPluginAdminPermissions("mk_subtitle", "Subtitle maker administration", "Allow documents management");
}


function installSubtitleMaker(){
	// Create folder
	$subtitle = BASEDIR.'/files/subtitle';
	$marker = BASEDIR.'/files/subtitle/marker';
	

 	if (!file_exists($subtitle)){
 		if (!mkdir($subtitle, 0775, true)) {
 			die('Failed to create folders...');
 		}
	}

 	if (!file_exists($marker)){
 		if (!mkdir($marker, 0775, true)) {
 			die('Failed to create folders...');
 		}
	}


}


/** install the plugin */
installSubtitleMaker();
?>