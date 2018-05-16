<?php
require_once CHP_DIR .'/homepage_class.php';

$userquery->admin_login_check();
$pages->page_redir();

$homeid = intval(filter_input(INPUT_GET, 'hp'));

$isAdmin = $userquery->permission[getStoredPluginName('homepageAdmin')] === 'yes';
$canManage = $homepagequery->canManage($userquery->level, $homeid);

if(!($isAdmin || $canManage)){
	header('Location: '. CHP_MANAGEPAGES_URL);
	exit;
}

$homepage = $homepagequery->getByHome($homeid);
if(count($homepage) === 0){
	header('Location: '. CHP_MANAGEPAGES_URL);
	exit;
}
Assign('homepage', $homepage[0]);

if(filter_input(INPUT_POST, 'chp') !== null){
	$vids = filter_input(INPUT_POST, 'vids');
	$picked = filter_input(INPUT_POST, 'picked');
	unset($_POST);
	
	if($vids !== null && $picked !== null){
		if($homepagequery->addHomeVideos($homeid, $vids, $picked)){
			header('Location: '. CHP_MANAGEPAGES_URL);
			exit;
		} else {
			Assign('chpErrorSaveForm', lang('chp_errorSaveForm'));
		}
	}
}

if(!defined('MAIN_PAGE')) define('MAIN_PAGE', lang('video_addon'));
if(!defined('SUB_PAGE')) define('SUB_PAGE', lang('chp_title'));
subtitle(lang('chp_manageTitle'));

$chpvidSelected = '';

$chpvideos = $homepagequery->getHomeVideos($homeid);
for($i = 0; $i < count($chpvideos); $i++){
	if($chpvideos[$i]['datecreated'] === '0000-00-00 00:00:00'){
		$chpvideos[$i]['datecreated'] = '-';
	} else {
		$d = DateTime::createFromFormat('Y-m-d H:i:s', $chpvideos[$i]['datecreated']);
		$chpvideos[$i]['datecreated'] = $d->format('d/m/Y');
	}
}
Assign('chpvidSelected', $chpvideos);

$getTags = $homepagequery->getTags();
$tags = array();
foreach($getTags as $t){
	$r = explode(',', $t['tags']);
	foreach($r as $t){
		$t = trim($t);
		if($t !== ''){
			$tags[$t]++;
		}
	}
}
Assign('chpTags', $tags);

template_files('manage_page.html', CHP_ADMIN_DIR);