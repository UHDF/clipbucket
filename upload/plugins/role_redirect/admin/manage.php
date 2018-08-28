<?php
require_once RREDIR_DIR.'/role_redirect_class.php';

$userquery->admin_login_check();
if ($cbplugin->is_installed('common_library.php'))
    $userquery->login_check(getStoredPluginName('role_redirect'));

$pages->page_redir();

/** Assigning page and subpage */
if(!defined('MAIN_PAGE'))
	define('MAIN_PAGE', lang('video_addon'));
if(!defined('SUB_PAGE'))
	define('SUB_PAGE', lang('rredir_subtitle'));

if($_SESSION['rredir']['formSuccess']){
	Assign('rredir_formSuccess', $_SESSION['rredir']['formSuccess']);
	
	unset($_SESSION['rredir']['formSuccess']);
}

Assign('rrRecords', $rredirquery->getAll());

/** Set HTML title */
subtitle(lang('rredir_subtitle'));

/** Set HTML template */
template_files('manage.html', RREDIR_DIR .'/admin');