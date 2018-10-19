<?php
require_once('../includes/common.php');

	/**
	* Install db table of CAS configuration
	*/
	function installLiveUPJV() {
		global $db;
		$db->Execute(
			'CREATE TABLE '.tbl("liveupjv").' (
				`id` INT(8) NOT NULL AUTO_INCREMENT ,
				`date_debut` DATETIME NOT NULL ,
				`date_fin` DATETIME NOT NULL ,
				`videoid` INT(8) NOT NULL ,
				PRIMARY KEY (`id`)
			)
			ENGINE = InnoDB CHARSET=utf8;'
		);
	}

	/**
	* Install locales for this plugin
	*/
	global $cbplugin;
	if ($cbplugin->is_installed('common_library.php')){
		require_once PLUG_DIR.'/common_library/common_library.php';
		$folder= PLUG_DIR.'/'.basename(dirname(__FILE__))."/lang";
		importLangagePack($folder,'en');
		importLangagePack($folder,'fr');
		installPluginAdminPermissions("liveupjv", "Live UPJV administration", "Allow Live UPJV management");
	}

	installLiveUPJV();
?>