<?php
require_once PLUG_DIR.'/common_library/common_library.php';
require_once('../includes/common.php');

uninstallPluginAdminPermissions('video_manager');

function restoreVideoDate(){
    global $db;
    $db->Execute(
        'ALTER TABLE '.tbl('video').' CHANGE `datecreated` `datecreated` DATE NULL DEFAULT NULL;'
    );
}

function removeVidmFiles(){
    $files = array(BASEDIR .'/admin_area/video_manager.php', BASEDIR .'/admin_area/edit_video.php');
    $origin = array(BASEDIR .'/admin_area/video_manager_vidmbkp.php', BASEDIR .'/admin_area/edit_video_vidmbkp.php');
    $plugin = array(__DIR__ .'/admin/manager.php', __DIR__.'/admin/edit.php');
    
    for($i = 0; $i < count($files); $i++){
        if(file_exists($origin[$i])){
            if(file_exists($files[$i])){
                unlink($files[$i]);
            }
            rename($origin[$i], $files[$i]);
        }elseif(file_exists($plugin[$i])){
            if(file_exists($files[$i])){
                unlink($files[$i]);
            }
            copy($plugin[$i], $files[$i]);
        }
    }
}

removeVidmFiles();
restoreVideoDate();

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