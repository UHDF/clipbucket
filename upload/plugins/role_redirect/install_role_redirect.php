<?php

require_once PLUG_DIR.'/common_library/common_library.php';
require_once('../includes/common.php');

/**
 * Install locales for this plugin and set admin permissions
 */
global $cbplugin;

if($cbplugin->is_installed('common_library.php')){
    $folder= PLUG_DIR.'/'.basename(dirname(__FILE__)).'/lang';
    importLangagePack($folder,'en');
    importLangagePack($folder,'fr');
	installPluginAdminPermissions('role_redirect', 'Gestion des redirections', 'Allow redirection management');
}

function addRoleRedirect(){
    global $db;
	
    $db->Execute(
        'CREATE TABLE IF NOT EXISTS '. tbl('roleredirect') .' (
			`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`role_user` varchar(255) NOT NULL,
			`pagefrom` varchar(255) NOT NULL,
			`redirectto` varchar(255) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
    );
}

addRoleRedirect();