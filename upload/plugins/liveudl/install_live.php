<?php
require_once PLUG_DIR.'/common_library/common_library.php';
require_once('../includes/common.php');

/**
 * Install locales for this plugin and set admin permissions
 */
global $cbplugin;

if ($cbplugin->is_installed('common_library.php')){
    require_once PLUG_DIR.'/common_library/common_library.php';
    $folder= PLUG_DIR.'/'.basename(dirname(__FILE__)).'/lang';
    importLangagePack($folder,'en');
    importLangagePack($folder,'fr');
    installPluginAdminPermissions('liveudl', 'LiveUDL administration', 'Allow liveUDL management');
    installPluginAdminPermissions('liveudlAdmin', 'LiveUDL administration management', 'LiveUDL RTMP/HTTP management');
}

/**
 * Create Table for live if not exists 
 */
function installLive() {
    global $db;
    $db->Execute(
        'CREATE TABLE IF NOT EXISTS '.tbl('liveudl').' (
            `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `thumb` varchar(255) DEFAULT NULL,
            `title` varchar(255) NOT NULL,
            `description` text,
            `date` datetime NOT NULL,
            `visible` tinyint(1) NOT NULL,
            `active` tinyint(1) NOT NULL,
            `homepage` tinyint(1) NOT NULL,
            `rtmpid` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
    );
    
    $db->Execute(
        'CREATE TABLE IF NOT EXISTS '.tbl('liveudl_rtmp').' (
            `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `fms` varchar(255) NOT NULL,
            `fmsid` varchar(50) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
    );
    
    $db->Execute(
        'CREATE TABLE IF NOT EXISTS '.tbl('liveudl_http').' (
            `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `rtmpid` int(11) NOT NULL,
            `quality` int(5) NOT NULL,
            `dash` varchar(255) NOT NULL,
            `hls` varchar(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
    );
}

/**
 * Copy watch_live.php to activate live on Frontend & add the folder live in files/thumbs
 */
function copyFile(){
    $pluginDir = BASEDIR .'/plugins/liveudl';
    
    symlink($pluginDir .'/admin/watch_live.php', BASEDIR.'/watch_live.php');
    //copy($pluginDir .'/watch_live.php', BASEDIR.'/watch_live.php');
    mkdir(BASEDIR .'/files/thumbs/lives');
    symlink($pluginDir .'/thumblive.png', BASEDIR .'/files/thumbs/lives/thumblive.png');
    //copy($pluginDir .'/thumblive.png', BASEDIR .'/files/thumbs/lives/thumblive.png');
}

/** install the plugin */
installLive();
copyFile();
?>