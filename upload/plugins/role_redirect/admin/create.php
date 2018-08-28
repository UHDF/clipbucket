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

Assign('rredir_ugrps', $rredirquery->getRoles());

if($_SESSION['rredir']['error']){
	$e = $_SESSION['rredir']['error'];
	Assign('rredir_error', $e['msg']);
	Assign('rredir_ugid', $e['value']['ugid']);
	Assign('rredir_from', $e['value']['from']);
	Assign('rredir_to', $e['value']['to']);
	
	unset($_SESSION['rredir']['error']);
}

/** Set HTML title */
subtitle(lang('rredir_titleNew'));
Assign('rrTitle', lang('rredir_titleNew'));
Assign('rrAction', 'create');

/** Set HTML template */
template_files('create.html', RREDIR_DIR.'/admin');