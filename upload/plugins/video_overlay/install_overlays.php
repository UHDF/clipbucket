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
	installPluginAdminPermissions("video_overlay", "Video overlay administration", "Allow overlay management");
}

/**
 * Create Table for external job encoder if not exists
 */
function installOverlayTable() {
	global $db;
	$db->Execute(
		'CREATE TABLE IF NOT EXISTS '.tbl("video_overlay").' (
		`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
		`content` text NOT NULL,
		`videoid` int(11) NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
	);
}

/**
 * Add a Tab to the Edit Video Page
 */
function installOverlayEditTab() {
	global $db;
	$sql = 'INSERT INTO '.tbl("expand_video_manager")." (`evm_id`, `evm_plugin_url`, `evm_zone`, `evm_is_new_tab`, `evm_tab_title`)".
			" VALUES ('', '".BASEDIR."/plugins/video_overlay/admin/set_overlays.php', ".
			"'expand_video_manager_left_panel', 1, '".lang("Overlays")."');";
	$db->Execute($sql);
}

/** install the plugin */
installOverlayTable();
installOverlayEditTab();
?>