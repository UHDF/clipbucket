<?php

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
	require_once RREDIR_DIR.'/role_redirect_class.php';
	
	$userquery->admin_login_check();
	if ($cbplugin->is_installed('common_library.php'))
    $userquery->login_check(getStoredPluginName('role_redirect'));
	
	$action = filter_input(INPUT_POST, 'action');
	$ugid = filter_input(INPUT_POST, 'gid');
	$from = filter_input(INPUT_POST, 'pagefrom');
	$to = filter_input(INPUT_POST, 'redirectto');
	if($ugid && $from && $to){
		if($action === 'create'){
			if($rredirquery->create($ugid, $from, $to)){
				$_SESSION['rredir']['formSuccess'][] = lang('rredir_successCreation');
				header('Location: '. RREDIR_MANAGEPAGE_URL);
			} else { 
				$_SESSION['rredir']['error'] = array('msg' => lang('rredir_errorRedirectExists'), 'value' => array('ugid' => $ugid, 'from' => $from, 'to' => $to));
				header('Location: '. RREDIR_CREATEPAGE_URL);
			}
		}
		elseif($action === 'edit'){
			$id = filter_input(INPUT_POST, 'id');
			if($rredirquery->edit($id, $ugid, $from, $to)){
				$_SESSION['rredir']['formSuccess'][] = lang('rredir_successEdit');
				header('Location: '. RREDIR_MANAGEPAGE_URL);
			} else { 
				$_SESSION['rredir']['error_'. $id] = array('msg' => lang('rredir_errorRedirectExists'), 'value' => array('ugid' => $ugid, 'from' => $from, 'to' => $to));
				header('Location: '. RREDIR_EDITPAGE_URL);
			}
		}
	} 
} else {
	require_once '../../../includes/admin_config.php';
	require_once RREDIR_DIR.'/role_redirect_class.php';
	
	$rr = intval(filter_input(INPUT_POST, 'rr'));
	
	if($rr){
		$rredirquery->delete($rr);
	}
}
