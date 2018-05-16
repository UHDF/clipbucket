<?php

function uninstallHomepageTables(){
	global $db;

	$db->Execute('DROP TABLE '. tbl('custom_home_page_suggest'));
	$db->Execute('DROP TABLE '. tbl('custom_home_page_video'));
	$db->Execute('DROP TABLE '. tbl('custom_home_page_permission'));
	$db->Execute('DROP TABLE '. tbl('custom_home_page'));
}

global $cbplugin;
if($cbplugin->is_installed('common_library.php')){
	require_once PLUG_DIR.'/common_library/common_library.php';
	
	$folder= PLUG_DIR .'/'. basename(dirname(__FILE__)) .'/lang';
	removeLangagePack($folder, 'en');
	removeLangagePack($folder, 'fr');
	
	uninstallPluginAdminPermissions('homepage');
	uninstallPluginAdminPermissions('homepageAdmin');
}

uninstallHomepageTables();