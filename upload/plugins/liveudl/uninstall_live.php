<?php
require_once PLUG_DIR.'/common_library/common_library.php';
require_once('../includes/common.php');

function delTree($dir){
    $files = array_diff(scandir($dir), array('.','..')); 
    foreach ($files as $file) { 
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
    } 
    return rmdir($dir);
}

/**
 *Remove live table from the database 
 */
function uninstallLive() {
    global $db;
    $db->Execute('DROP TABLE  IF EXISTS '.tbl('liveudl_http'));
    $db->Execute('DROP TABLE  IF EXISTS '.tbl('liveudl_rtmp'));
    $db->Execute('DROP TABLE  IF EXISTS '.tbl('liveudl'));
}

/**
 * Remove watch_live.php
 */
function removeFiles(){
    unlink(BASEDIR .'/watch_live.php');
    delTree(BASEDIR .'/files/thumbs/lives');
}

uninstallLive();
removeFiles();
uninstallPluginAdminPermissions('liveudl');
uninstallPluginAdminPermissions('liveudlAdmin');

/**
 * remove locales for this plugin
 */
global $cbplugin;
if ($cbplugin->is_installed('common_library.php')){
	require_once PLUG_DIR .'/common_library/common_library.php';
	$folder= PLUG_DIR .'/'. basename(dirname(__FILE__)) .'/lang';
	removeLangagePack($folder,'en');
	removeLangagePack($folder,'fr');
}

?>