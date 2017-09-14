<?php
/*
Plugin Name: Tag Selector
Description: Use this plugin to select tags for a video into a list of all already existing tags. 
Author: Franck Rouze
Author Website: http://semm.univ-lille1.fr/
ClipBucket Version: 2.8.1
Version: 1.0
*/


/**
 * Define Plugin's uri constants. These constants represents folders or urls
 */
define("SITE_MODE",'/admin_area');
define('TAGSELECTOR_BASE',basename(dirname(__FILE__)));
define('TAGSELECTOR_DIR',PLUG_DIR.'/'.TAGSELECTOR_BASE);
define('TAGSELECTOR_URL',PLUG_URL.'/'.TAGSELECTOR_BASE);
define('TAGSELECTOR_ADMIN_DIR',TAGSELECTOR_DIR.'/admin');
define('TAGSELECTOR_ADMIN_URL',TAGSELECTOR_URL.'/admin');

require_once TAGSELECTOR_DIR.'/tag_selector_class.php';



if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("tag_selector")]=='yes'){
	global $tagSeletor;
	Assign("alltags", json_encode($tagSelector->getAllTags()));
	Assign("initialtags", json_encode($tagSelector->getTags($_GET['video'])));
	Assign("PlaceHolder", lang("type_tag_or_select_value"));
	$Cbucket->add_admin_header(TAGSELECTOR_ADMIN_DIR . '/header.html');	// *** Ajoute le JS pour récupérer le temps de lecture
}

?>