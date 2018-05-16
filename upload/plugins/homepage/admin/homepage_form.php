<?php

$isAdmin = $userquery->permission[getStoredPluginName('homepageAdmin')] === 'yes';
if(!$isAdmin){
	header('Location: '. CHP_MANAGEPAGES_URL);
	exit;
}

if(!defined('MAIN_PAGE')) define('MAIN_PAGE', lang('video_addon'));

$chpid = filter_input(INPUT_GET, 'hp', FILTER_SANITIZE_NUMBER_INT);
$chpname = filter_input(INPUT_POST, 'chpname');
$chpslug = filter_input(INPUT_POST, 'chpslug');
$chplevels = filter_input(INPUT_POST, 'chpulvl', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

if($chpname && $chpslug){
	Assign('chpname', $chpname);
	Assign('chpslug', $chpslug);
	Assign('chpulevels', implode(',', $chplevels));

	if($homepagequery->exists($chpname, $chpslug, $chpid)){
		Assign('chpError', lang('chp_errorExists'));
	} else {
		$hp = $chpid !== null;
		
		if(!$hp){
			$hp = $homepagequery->create($chpname, $chpslug);
		} else {
			$homepagequery->update($chpid, $chpname, $chpslug);
			$homepagequery->removeAllPermissions($chpid);
			$hp = array('id' => $chpid);
		}
		
		if($hp !== false){
			foreach($chplevels as $ulvl){
				$homepagequery->addPermission($hp['id'], $ulvl);
			}

			$_POST = '';
			if($chpid) $_SESSION['chp']['m'][] = lang('chp_editSuccess', $chpname);
			else $_SESSION['chp']['m'][] = lang('chp_createSuccess', $chpname);
			header('Location: '. CHP_MANAGEPAGES_URL);
			exit;
		}

		Assign('chpError', lang('chp_errorSaveDb'));
	}
}

Assign('chpulvls', $userquery->get_levels());