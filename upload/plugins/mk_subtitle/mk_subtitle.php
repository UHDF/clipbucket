<?php
/*
Plugin Name: Subtitle maker
Description: For making subtitle
Author: Adrien Ponchelet
Author Website: https://www.u-picardie.fr
ClipBucket Version: 2.8.2
Version: 0.1
*/

if (!$cbplugin->is_installed('common_library.php'))
	e(sprintf(lang("plugin_not_installed : %s"),"Common Library"));
else
	require_once PLUG_DIR.'/common_library/common_library.php';

// Define Plugin's uri constants
define("SITE_MODE",'/admin_area');
define('SUBTITLE_MAKER_BASE',basename(dirname(__FILE__)));

define("SUBTITLE_MAKER_URL",BASEURL.SITE_MODE."/plugin.php?folder=".SUBTITLE_MAKER_BASE."/admin&file=subtitle_maker.php");

/**
 * Add a new entry "Link document" into the video manager menu named "Actions" associated to each video
 * 
 * @param int $vid 
 * 		the video id
 * @return string
 * 		the html string to be inserted into the menu
 */
function addSubtitleMaker($vid){
	$idtmp=$vid['videoid'];
	return '<li><a role="menuitem" href="'.SUBTITLE_MAKER_URL.'&video='.$idtmp.'">Subtitle Maker</a></li>';
}

if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("mk_subtitle")]=='yes'){
	$cbvid->video_manager_link[]='addSubtitleMaker';
}

?>