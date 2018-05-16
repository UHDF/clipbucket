<?php
$userquery->admin_login_check();
$pages->page_redir();

require_once 'homepage_form.php';

if($chpname === null && $chpslug === null && $chplevels === null){
	$homeid = intval(filter_input(INPUT_GET, 'hp'));
	$res = $homepagequery->getByHome($homeid);
	if(count($res) === 0){
		Assign('chpNoHomeForId', lang('chp_noHomeForId', $homeid));
	} else {
		Assign('chpid', $res[0]['id']);
		Assign('chpname', $res[0]['name']);
		Assign('chpslug', $res[0]['slug']);
		$rlvls = '';
		for($i = 0; $i < count($res); $i++){
			$rlvls .= ($i !== 0 ? ',': '') . $res[$i]['user_level_id'];
		}
		Assign('chpulevels', $rlvls);
	}
}

define('SUB_PAGE', lang('chp_title'));
subtitle(lang('chp_editTitle'));

template_files('edit_homepage.html', CHP_ADMIN_DIR);