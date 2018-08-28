<?php
require_once PLUG_DIR.'/common_library/common_library.php';
require_once('../includes/common.php');


/**
 *Remove external encoding job table from the database
 */
function uninstallOverlayTable() {
	global $db;
	$db->Execute(
			'DROP TABLE  IF EXISTS '.tbl("video_overlay").''
			);
}

/**
 * Remove Tab from the Edit Video Page
 */
function uninstallOverlayEditTab() {
	global $db;
	$query='DELETE FROM '.tbl("expand_video_manager").' WHERE `evm_plugin_url` =
			\''.BASEDIR.'/plugins/video_overlay/admin/set_overlays.php\';';
	$db->Execute($query);
}

uninstallOverlayTable();
uninstallOverlayEditTab();
uninstallPluginAdminPermissions("video_overlay");

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