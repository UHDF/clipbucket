<?php

require_once('../includes/common.php');

global $cbplugin;

if($cbplugin->is_installed('common_library.php')){
	require_once PLUG_DIR.'/common_library/common_library.php';
	$folder= PLUG_DIR.'/'.basename(dirname(__FILE__)).'/lang';
	
    importLangagePack($folder, 'en');
    importLangagePack($folder, 'fr');
    installPluginAdminPermissions('homepage', 'Gestion des pages d\'accueil', 'Allow homepages management');
	installPluginAdminPermissions('homepageAdmin', 'Création des pages d\'accueil', 'Allow homepages creation');
}

function installHomepage(){
	global $db;
	$db->Execute(
		'CREATE TABLE IF NOT EXISTS '.tbl('custom_home_page').' (
  		`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  		`name` varchar(255) NOT NULL, 
		`slug` varchar(255) NOT NULL,
		CONSTRAINT uc_chp UNIQUE(name, slug)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
	);
}

function installHomepagePermission(){
	global $db;
	$db->Execute(
		'CREATE TABLE IF NOT EXISTS '.tbl('custom_home_page_permission').' (
  		`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  		`home_id` int(11) NOT NULL, 
		`userlevel_id` int(11) NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
	);
}

function installHomepageVideo(){
	global $db;
	$db->Execute(
		'CREATE TABLE IF NOT EXISTS '.tbl('custom_home_page_video').' (
  		`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  		`home_id` int(11) NOT NULL, 
		`video_id` int(11) NOT NULL,
		`position` int(3) NOT NULL,
		`picked` tinyint(1) NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
	);
}

function installHomepageSuggest(){
	global $db;
	$db->Execute(
		'CREATE TABLE IF NOT EXISTS '.tbl('custom_home_page_suggest').' (
  		`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  		`home_id` int(11) NOT NULL, 
		`video_id` int(11) NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
	);
}

installHomepage();
installHomepagePermission();
installHomepageVideo();
installHomepageSuggest();