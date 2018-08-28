<?php
/*
 Plugin Name: Role redirect
 Description: Redirect a groupe of users from the admin homepage to the selected page
 Author: Bastien Poirier
 Author Website: http://semm.univ-lille1.fr/
 ClipBucket Version: 2.8
 Version: 1.0
 Website:
 */

require_once 'role_redirect_class.php';

global $cbplugin;
if (!$cbplugin->is_installed('common_library.php'))
	e(sprintf(lang("plugin_not_installed : %s"),"Common Library"));
else
	require_once PLUG_DIR.'/common_library/common_library.php';

define('SITE_MODE', '/admin_area');
define('RREDIR_BASE', basename(dirname(__FILE__)));
define('RREDIR_DIR', PLUG_DIR .'/'. RREDIR_BASE);
define('RREDIR_URL', PLUG_URL .'/'. RREDIR_BASE);

define('RREDIR_MANAGEPAGE_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. RREDIR_BASE .'/admin&file=manage.php');
define('RREDIR_CREATEPAGE_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. RREDIR_BASE .'/admin&file=create.php');
define('RREDIR_EDITPAGE_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. RREDIR_BASE .'/admin&file=edit.php');
define('RREDIR_ACTIONPAGE_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. RREDIR_BASE .'/admin&file=action.php');

assign('rredir_managepage', RREDIR_MANAGEPAGE_URL);
assign('rredir_createpage', RREDIR_CREATEPAGE_URL);
assign('rredir_editpage', RREDIR_EDITPAGE_URL);
assign('rredir_actionpage', RREDIR_ACTIONPAGE_URL);
assign('rredir_ajax', RREDIR_URL. '/admin/action.php');
assign('rredir_css', RREDIR_DIR. '/admin/css.html');
assign('rredir_js', RREDIR_DIR. '/admin/js.html');

if($rredirquery->isActivated()){
	if($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName('tag_manager')] === 'yes')
		add_admin_menu(lang('video_addon'), lang('rredir_sidebarMenu'), 'manage.php', RREDIR_BASE .'/admin');	

	if($_SERVER['PHP_SELF'] === '/admin_area/index.php'){
		$ugrp = $userquery->level;
		$redirectTo = $rredirquery->getRedirByGID($ugrp, $_SERVER['PHP_SELF']);
		if($redirectTo){
			header('Location: '. $redirectTo);
			exit;
		}
	}
}



?>