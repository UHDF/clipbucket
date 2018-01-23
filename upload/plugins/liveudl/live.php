<?php
/*
 Plugin Name: Live UDL
 Description: This plugin will add the management of the lives<br /><span class="glyphicon glyphicon-warning-sign"></span> Don't forget to edit the .htaccess file to add the Rewrite rules <span class="glyphicon glyphicon-warning-sign"></span>
 Author: Bastien POIRIER
 Author Website: http://semm.univ-lille1.fr/
 ClipBucket Version: 2.8
 Version: 1.0
 Website:
 */

require_once 'live_class.php';

global $cbplugin;

if (!$cbplugin->is_installed('common_library.php'))
	e(sprintf(lang('plugin_not_installed : %s'),'Common Library'));
else
	require_once PLUG_DIR.'/common_library/common_library.php';
		

/**
 * Define Plugin's uri constants. These constants represents folders or urls
 */
define('SITE_MODE','/admin_area');
define('LIVEUDL_BASE', basename(dirname(__FILE__)));
define('LIVEUDL_DIR', PLUG_DIR.'/'.LIVEUDL_BASE);
define('LIVEUDL_URL', PLUG_URL.'/'.LIVEUDL_BASE);
define('LIVEUDL_FRONT_DIR', LIVEUDL_DIR.'/layout');
define('LIVEUDL_ADMIN_DIR', LIVEUDL_DIR.'/admin');
define('LIVEUDL_ADMIN_URL', LIVEUDL_URL.'/admin');
define('LIVEUDL_FILE', BASEURL.SITE_MODE.'/plugin.php?folder='.LIVEUDL_BASE.'/admin&file=');
define('LIVEUDL_MANAGEPAGE_URL', LIVEUDL_FILE. 'manage_live.php');
define('LIVEUDL_ACTIONPAGE_URL', LIVEUDL_FILE .'live.php');
define('LIVEUDL_OPT_URL', LIVEUDL_FILE .'opt.php');
define('README', LIVEUDL_FILE .'readme.php');
define('LIVEUDL_THUMBSDIR', BASEDIR .'/files/thumbs/lives/');

assign('liveudl_dir', LIVEUDL_URL);
assign('liveudl_admin_dir', LIVEUDL_ADMIN_URL);
assign('liveudl_managepage', LIVEUDL_FILE .'manage_live.php');
assign('liveudl_actionpage', LIVEUDL_FILE .'live.php');
assign('liveudl_css', LIVEUDL_ADMIN_DIR. '/css.html');

assign('liveudl_opt', LIVEUDL_FILE .'opt.php');
assign('liveudl_opt_form', LIVEUDL_FILE .'opt.php&form');
assign('liveudl_opt_delete', LIVEUDL_FILE .'opt.php&delete=');

assign('liveudl_vjsScript', LIVEUDL_URL .'/js/lvp-live/videojs-lvp-live.min.js');
assign('liveudl_thumbsdir', BASEURL .'/files/thumbs/lives/');
assign('liveudl_thumbdefault', 'thumblive.png');

/**Add entries for the plugin in the administration pages */
if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName('liveudl')]=='yes'){
    add_admin_menu(lang('Videos'),lang('liveudl_manager'),'manage_live.php',LIVEUDL_BASE.'/admin');
    
    $folder = filter_input(INPUT_GET, 'folder');
    $file = filter_input(INPUT_GET, 'file');
    if($folder === 'liveudl/admin' && $file === 'live.php'){
        $Cbucket->add_admin_header(LIVEUDL_ADMIN_DIR . '/header.html');
    }
}
	
?>