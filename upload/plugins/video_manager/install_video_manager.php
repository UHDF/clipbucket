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
}

function alterVideoDate(){
    global $db;
    $db->Execute(
        'ALTER TABLE '.tbl('video').' CHANGE `datecreated` `datecreated` DATETIME NULL DEFAULT NULL;'
    );
}

function addVidmFiles(){
    if(!is_writable(BASEDIR .'/admin_area/video_manager.php')){
        die('CANNOT MODIFY '. BASEDIR .'/admin_area/video_manager.php');
    } else {
        rename(BASEDIR .'/admin_area/video_manager.php', BASEDIR .'/admin_area/video_manager_vidmbkp.php');
        symlink(BASEDIR .'/plugins/video_manager/admin/manage.php', BASEDIR .'/admin_area/video_manager.php');
    }
    
    if(!is_writable(BASEDIR .'/admin_area/edit_video.php')){
        die('CANNOT MODIFY '. BASEDIR .'/admin_area/edit_video.php');
    } else {
        rename(BASEDIR .'/admin_area/edit_video.php', BASEDIR .'/admin_area/edit_video_vidmbkp.php');
        symlink(BASEDIR .'/plugins/video_manager/admin/edit.php', BASEDIR .'/admin_area/edit_video.php');
    }
}

addVidmFiles();
alterVideoDate();
// ALTER TABLE `cb_video` CHANGE `datecreated` `datecreated` DATETIME NULL DEFAULT NULL;

// ALTER TABLE `cb_video` CHANGE `datecreated` `datecreated` DATE NULL DEFAULT NULL;