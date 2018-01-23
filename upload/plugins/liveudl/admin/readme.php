<?php

require_once LIVEUDL_DIR.'/live_class.php';
/** Check if user has admin acces */
$userquery->admin_login_check();
/** Check if user has admin acces to this plugin */
if ($cbplugin->is_installed('common_library.php')){
    $userquery->login_check(getStoredPluginName('liveudl'));
    Assign('liveudlOpt', $userquery->permission[getStoredPluginName('liveudlAdmin')] == 'yes');
}

$pages->page_redir();

/** Assigning page and subpage */
if(!defined('MAIN_PAGE'))
	define('MAIN_PAGE', lang('Videos'));
if(!defined('SUB_PAGE'))
	define('SUB_PAGE', lang('liveudl_manager'));

$seo = $liveudlquery->live_link();
Assign('liveudlSeo', $seo);

/** Set HTML title */
subtitle('LiveUDL README');

/** Set HTML template */
template_files('readme.html', LIVEUDL_ADMIN_DIR);

