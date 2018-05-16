<?php
/*
Plugin Name: Update lang
Description: Add a function in the Actions dropdown button on the Plugin Manager page to update translations contained if the lang folder
Author: Bastien Poirier
Author Website: http://semm.univ-lille1.fr/
ClipBucket Version: 2.8
Version: 1.0
*/

if(intval($db->count(tbl('plugins'), 'plugin_id', 'plugin_folder = "updatelang" AND plugin_file = "updatelang.php" AND plugin_active = "yes"')) === 1){
	if($_SERVER['SCRIPT_NAME'] === '/admin_area/plugin_manager.php'){
		/*require_once PLUG_DIR.'/common_library/common_library.php';
		if($cbplugin->is_installed('common_library.php')){
			importLangagePack(PLUG_DIR .'/'. basename(dirname(__FILE__)) .'/lang','fr');
		}*/
		$plg_folder = '';
		foreach($cbplugin->getInstalledPlugins() as $p){
			if(is_dir(PLUG_DIR .'/'. $p['plugin_folder'] .'/lang')){
				$plg_folder .= '<input type="hidden" name="updatelangPlg[]" value="'. $p['plugin_folder'] .'" />';
			}
		}
		if($plg_folder !== ''){
			//echo '<div id="updatelangplgfolders" style="display: none !important">'. $plg_folder .'</div>';
		}
		$Cbucket->add_admin_header(PLUG_DIR . '/updatelang/admin/header.html');
	}
}	
