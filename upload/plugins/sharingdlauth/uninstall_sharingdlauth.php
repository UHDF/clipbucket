<?php
require_once PLUG_DIR.'/common_library/common_library.php';
require_once('../includes/common.php');

uninstallPluginAdminPermissions('video_manager');

// ALTER TABLE `cb_video` DROP `authdl`, DROP `authsharing`;
function removeSharingDl(){
    global $db;
    $db->Execute('ALTER TABLE '. tbl('video') .' DROP `authdl`, DROP `authsharing`');
}

removeSharingDl();

/**
 * remove locales for this plugin
 */
global $cbplugin;

if ($cbplugin->is_installed('common_library.php')){
    $folder= PLUG_DIR.'/'.basename(dirname(__FILE__)).'/lang';
    removeLangagePack($folder,'en');
    removeLangagePack($folder,'fr');
}

?>