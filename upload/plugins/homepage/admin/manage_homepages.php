<?php
require_once CHP_DIR .'/homepage_class.php';

$userquery->admin_login_check();

$pages->page_redir();

/** Assigning page and subpage */
if(!defined('MAIN_PAGE'))
	define('MAIN_PAGE', lang('video_addon'));
if(!defined('SUB_PAGE'))
	define('SUB_PAGE', lang('chp_title'));

subtitle(lang('chp_title'));

$homepages = array();
$isAdmin = $userquery->permission[getStoredPluginName('homepageAdmin')] === 'yes';
$tpl = 'manage_homepages.html';
if($isAdmin){
	$homepages = $homepagequery->getAllHomepages();
} else {
	$homepages = $homepagequery->getByLevel($userquery->level);
	/*if(count($homepages) === 1){
		Assign('homepage', $homepages[0]);
		$tpl = 'edit_homepage.html';
	}*/
}

$currentHp = array('id' => 0, 'name' => '', 'slug' => '', 'ulvl' => array());
$res = array();
foreach($homepages as $hp){
	if($currentHp['id'] !== $hp['id']){
		if($currentHp['id'] !== 0) $res[] = $currentHp;
		$currentHp = array('id' => $hp['id'], 'name' => $hp['name'], 'slug' => $hp['slug'], 'ulvl' => array());
	}
	
	$currentHp['ulvl'][] = $hp['user_level_name'];
}
if($currentHp['id'] !== 0) $res[] = $currentHp;

if(isset($_SESSION['chp']['m'])){
	$alert = '';
	foreach($_SESSION['chp']['m'] as $msg){
		$alert .= $msg .'<br />';
	}
	unset($_SESSION['chp']['m']);
	Assign('chpAlert', $alert);
}

Assign('homepages', $res);
template_files($tpl, CHP_ADMIN_DIR);