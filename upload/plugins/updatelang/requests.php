<?php

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die('NOT XMLHTTPREQUEST');
}

require_once '../../includes/common.php';

$action = filter_input(INPUT_POST, 'action');
if($action === 'check'){
	$plg_folder = array();
	foreach($cbplugin->getInstalledPlugins() as $p){
		if(is_dir(PLUG_DIR .'/'. $p['plugin_folder'] .'/lang')){
			$plg_folder[] = $p['plugin_folder'];
		}
	}
	echo json_encode($plg_folder);
} elseif($action === 'update'){
	$plg = filter_input(INPUT_POST, 'plg');
	if(is_dir(PLUG_DIR .'/'. $plg .'/lang') && $cbplugin->is_installed('common_library.php')){
		require_once PLUG_DIR.'/common_library/common_library.php';
		importLangagePack(PLUG_DIR .'/'. $plg .'/lang', 'en');
		importLangagePack(PLUG_DIR .'/'. $plg .'/lang', 'fr');

		echo 'The translations of <strong>'. $plg .'</strong> have been updated with success';
	} else {
		echo 'Error, cannot find the lang folder of <strong>'. $plg .'</strong>';
	}
}

