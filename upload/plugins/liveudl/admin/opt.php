<?php
require_once LIVEUDL_DIR.'/live_class.php';
/** Check if user has admin acces */
$userquery->admin_login_check();
/** Check if user has admin acces to this plugin */
if ($cbplugin->is_installed('common_library.php')){
    $userquery->login_check(getStoredPluginName('liveudlAdmin'));
}

$flashMsg = '';
$flashErr = '';

if(!empty($_POST)){
    $id = intval(filter_input(INPUT_POST, 'id'));
    
    if(filter_input(INPUT_POST, 'action') === 'delete' && id !== 0){
        $title = $liveudlquery->deleteRtmp($id);
        if($title)
            $flashMsg = lang('liveudl_deleteRtmpSuccess', $title);
        else 
            $flashErr = lang('liveudl_deleteRtmpErr');
    } else {        
        $fms = $liveudlquery->mysql_clean(filter_input(INPUT_POST, 'fms', FILTER_SANITIZE_URL));
        $fmsid = $liveudlquery->mysql_clean(filter_input(INPUT_POST, 'fmsid'));
        $qualities = array(
            '1080' => array('dash' => $liveudlquery->mysql_clean(filter_input(INPUT_POST, 'dash1080', FILTER_SANITIZE_URL)),
                'hls' => $liveudlquery->mysql_clean(filter_input(INPUT_POST, 'hls1080', FILTER_SANITIZE_URL))),
            '720' => array('dash' => $liveudlquery->mysql_clean(filter_input(INPUT_POST, 'dash720', FILTER_SANITIZE_URL)),
                'hls' => $liveudlquery->mysql_clean(filter_input(INPUT_POST, 'hls720', FILTER_SANITIZE_URL))),
            '480' => array('dash' => $liveudlquery->mysql_clean(filter_input(INPUT_POST, 'dash480', FILTER_SANITIZE_URL)),
                'hls' => $liveudlquery->mysql_clean(filter_input(INPUT_POST, 'hls480', FILTER_SANITIZE_URL))),
            '360' => array('dash' => $liveudlquery->mysql_clean(filter_input(INPUT_POST, 'dash360', FILTER_SANITIZE_URL)),
                'hls' => $liveudlquery->mysql_clean(filter_input(INPUT_POST, 'hls360', FILTER_SANITIZE_URL)))
        );
        
        if($id === 0){
            $flashErr = $liveudlquery->setRtmp($fms, $fmsid, $qualities);
            if($flashErr === '') $flashMsg = lang('liveudl_rtmpSuccessAdd');
        } else {
            $flashErr = $liveudlquery->setRtmp($fms, $fmsid, $qualities, $id);
            if($flashErr === '') $flashMsg = lang('liveudl_rtmpSuccessEdit');
        }
    }
    
    $_SESSION['liveudlrtmp_flashmsg'] = $flashMsg;
    $_SESSION['liveudlrtmp_flasherr'] = $flashErr;
    header('Location: '. LIVEUDL_OPT_URL);
    exit;
}

/** Assigning page and subpage */
if(!defined('MAIN_PAGE'))
	define('MAIN_PAGE', lang('Videos'));
if(!defined('SUB_PAGE'))
	define('SUB_PAGE', lang('liveudl_manager'));

$tpl = 'opt.html';

$id = filter_input(INPUT_GET, 'form');
$rtmp = false;
if($id !== null){
    $tpl = 'opt_form.html';
    $rtmp = $liveudlquery->getRtmp(intval($id));
} elseif($id = intval(filter_input(INPUT_GET, 'delete'))){
    $tpl = 'opt_delete.html';
    $rtmp = $liveudlquery->getRtmp(intval(filter_input(INPUT_GET, 'delete')));
}

if(intval($rtmp['id'])){
    $qualities = $liveudlquery->getQualities($rtmp['id']);
    Assign('qualities', $qualities);
}

if($tpl === 'opt.html'){
    $rtmp = $liveudlquery->getAllRtmp();
    for($i = 0; $i < count($rtmp); $i++){
        $rtmp[$i]['qualities'] = $liveudlquery->getQualities($rtmp[$i]['id']);
    }

    if(isset($_SESSION['liveudlrtmp_flasherr'])) $flashErr = $_SESSION['liveudlrtmp_flasherr'];
    if(isset($_SESSION['liveudlrtmp_flashmsg'])) $flashMsg = $_SESSION['liveudlrtmp_flashmsg'];

    unset($_SESSION['liveudlrtmp_flasherr']);
    unset($_SESSION['liveudlrtmp_flashmsg']);

    Assign('flashErr', $flashErr);
    Assign('flashMsg', $flashMsg);
}

Assign('rtmp', $rtmp);
Assign('rtmpid', $id);

/** Set HTML title */
subtitle(lang('liveudl_manager'));

/** Set HTML template */
template_files($tpl, LIVEUDL_ADMIN_DIR);
?>