<?php
require_once RREDIR_DIR.'/role_redirect_class.php';

$userquery->admin_login_check();
if ($cbplugin->is_installed('common_library.php'))
    $userquery->login_check(getStoredPluginName('role_redirect'));

$pages->page_redir();

$id = filter_input(INPUT_GET, 'rr');
$rr = $rredirquery->get($id);
if($rr === null){
	$_SESSION['rredir']['error']['msg'] = lang('rredir_errorNoRedirect');
	header('Location: '. RREDIR_MANAGEPAGE_URL);
}
Assign('rredir_id', $id);

/** Assigning page and subpage */
if(!defined('MAIN_PAGE'))
	define('MAIN_PAGE', lang('video_addon'));
if(!defined('SUB_PAGE'))
	define('SUB_PAGE', lang('rredir_subtitle'));

Assign('rredir_ugrps', $rredirquery->getRoles($id));

if($_SESSION['rredir']['error_'. $id]){
	$e = $_SESSION['rredir']['error_'. $id];
	Assign('rredir_error', $e['msg']);
	Assign('rredir_ugid', $e['value']['ugid']);
	Assign('rredir_from', $e['value']['from']);
	Assign('rredir_to', $e['value']['to']);
	
	unset($_SESSION['rredir']['error_'. $id]);
} else {
	Assign('rredir_ugid', $rr['role_user']);
	Assign('rredir_from', $rr['pagefrom']);
	Assign('rredir_to', $rr['redirectto']);
}

/** Set HTML title */
subtitle(lang('rredir_titleEdit'));
Assign('rrTitle', lang('rredir_titleEdit'));
Assign('rrAction', 'edit');

/** Set HTML template */
template_files('create.html', RREDIR_DIR.'/admin');