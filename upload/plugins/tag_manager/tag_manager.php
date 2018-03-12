<?php
/*
 Plugin Name: Tag manager
 Description: This plugin will allow to manage the videos' tags.
 Author: Bastien Poirier
 Author Website: http://www.univ-lille.fr/
 ClipBucket Version: 2.8
 Version: 1.0
 Website:
 */

require_once 'tag_manager_class.php';

global $cbplugin;
if (!$cbplugin->is_installed('common_library.php'))
	e(sprintf(lang("plugin_not_installed : %s"),"Common Library"));
else
	require_once PLUG_DIR.'/common_library/common_library.php';

		

/**
 * Define Plugin's uri constants. These constants represents folders or urls
 */
define('SITE_MODE', '/admin_area');
define('TAGM_BASE', basename(dirname(__FILE__)));
define('TAGM_DIR', PLUG_DIR .'/'. TAGM_BASE);
define('TAGM_URL', PLUG_URL .'/'. TAGM_BASE);
define('TAGM_ADMIN_DIR', TAGM_DIR .'/admin');
define('TAGM_ADMIN_URL', TAGM_URL .'/admin');
define('TAGM_MANAGEPAGE_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. TAGM_BASE .'/admin&file=manage_tags.php');
define('TAGM_CREATEPAGE_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. TAGM_BASE .'/admin&file=create_tag.php');
define('TAGM_ACTIONPAGE_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. TAGM_BASE .'/admin&file=action.php');

assign('tagm_admin', TAGM_ADMIN_URL);
assign('tagm_pagination', TAGM_ADMIN_DIR. '/pagination.html');
assign('tagm_pagindex', TAGM_ADMIN_DIR. '/pagination-index.html');
assign('tagm_js', TAGM_ADMIN_DIR. '/js.html');
assign('tagm_css', TAGM_ADMIN_DIR. '/css.html');

assign('tagm_managepage', TAGM_MANAGEPAGE_URL);
assign('tagm_createpage', TAGM_CREATEPAGE_URL);
assign('tagm_actionpage', TAGM_ACTIONPAGE_URL);

/**Add entries for the plugin in the administration pages */
//var_dump($cbplugin->is_installed('common_library.php'), $userquery->permission[getStoredPluginName('tag_manager')]);
if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName('tag_manager')] === 'yes')
	add_admin_menu(lang('video_addon'), 'Gestion des tags', 'manage_tags.php', TAGM_BASE .'/admin');	
?>