<?php
require_once PLUG_DIR.'/common_library/common_library.php';
require_once('../includes/common.php');



/**
 *  Delete directory
 */
function uninstallSubtitleMakerDirectory(){
	// Create folder
	$subtitle = BASEDIR.'/files/subtitle';
	$marker = BASEDIR.'/files/subtitle/marker';
	

 	if (is_dir($subtitle)){
 		if (!rmdir($subtitle)) {
 			die('Failed to delete folders...');
 		}
	}

 	if (is_dir($marker)){
 		if (!rmdir($marker)) {
 			die('Failed to delete folders...');
 		}
	}


}


uninstallSubtitleMakerDirectory();
uninstallPluginAdminPermissions("mk_subtitle");

/**
 * remove locales for this plugin
 */
global $cbplugin;
if ($cbplugin->is_installed('common_library.php')){
	require_once PLUG_DIR.'/common_library/common_library.php';
	$folder= PLUG_DIR.'/'.basename(dirname(__FILE__))."/lang";
	removeLangagePack($folder,'en');
	removeLangagePack($folder,'fr');
}

?>