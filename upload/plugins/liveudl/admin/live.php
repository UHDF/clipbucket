<?php
require_once LIVEUDL_DIR.'/live_class.php';
/** Check if user has admin acces */
$userquery->admin_login_check();
/** Check if user has admin acces to this plugin */
if ($cbplugin->is_installed('common_library.php'))	$userquery->login_check(getStoredPluginName('liveudl'));

$pages->page_redir();

function toManagePage(){
    header('Location: '. LIVEUDL_MANAGEPAGE_URL);
    exit;
}

function redirToRef($ref){
    header('Location: '. BASEURL .'/'. $ref);
    exit;
}

/** Assigning page and subpage */
if(!defined('MAIN_PAGE'))
	define('MAIN_PAGE', lang('Videos'));
if(!defined('SUB_PAGE'))
	define('SUB_PAGE', lang('liveudl_manager'));


$live = $liveudlquery->getDefault();
$errors = null;

if(!empty($_POST)){
    $lid = intval(filter_input(INPUT_POST, 'lid'));
    $fmsid = intval(filter_input(INPUT_POST, 'fmsid'));
	
    if(filter_input(INPUT_POST, 'action') === 'delete'){
        $title = $liveudlquery->deleteLive($lid);
        $_SESSION['liveudl_flashmsg'] = lang('liveudl_deleteSuccess', $title);
        toManagePage();
    }
    
    if(!$fmsid){
        $errors['rtmp'] = true;
    }
    
    $title = trim(filter_input(INPUT_POST, 'title'));
    if(!$title) $errors['title'] = true;
    $description = trim(filter_input(INPUT_POST, 'description'));
    if($description === null) $description = '';
    
    $date = filter_input(INPUT_POST, 'date');
    if($date){
        $date = DateTime::createFromFormat('d/m/Y H:i', $date, new DateTimeZone('Europe/Paris'));
    } else {
        $errors['date'] = true;
    }
    
    $active = filter_input(INPUT_POST, 'active') === '1';
    $front = filter_input(INPUT_POST, 'homepage') === '1';
    $visible = filter_input(INPUT_POST, 'visibility') === '1';
    
    if(!$visible){
        $active = false;
        $front = false;
    }
    
    $file = null;
    $oldfile = filter_input(INPUT_POST, 'oldthumb');
    $deleteThumb = filter_input(INPUT_POST, 'deleteThumb') === '1';
    if(is_array($_FILES['thumb']) && file_exists($_FILES['thumb']['tmp_name']) && is_uploaded_file($_FILES['thumb']['tmp_name'])){
        $file = $_FILES['thumb'];
        if($file['error'] !== 0){
            switch($file['error']){
                case UPLOAD_ERR_NO_FILE:
                   $errors['thumb'] = lang('liveudl_uploadNoFile');
                   break;
                case UPLOAD_ERR_INI_SIZE:
                   $errors['thumb'] = lang('liveudl_uploadIniSize');
                   break;
                case UPLOAD_ERR_FORM_SIZE:
                    $errors['thumb'] = lang('liveudl_uploadFormSize');
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors['thumb'] = lang('liveudl_uploadPartial');
                    break;
                default:
                    $errors['thumb'] = 'Error';
            }
        } else {
            $acceptedExt = array('jpg', 'jpeg', 'gif', 'png');
            $ext = strtolower(substr(strrchr($file['name'], '.'), 1));
            
            if(!in_array($ext, $acceptedExt)){
                $errors['thumb'] = lang('liveudl_uploadExt');
            }
        }
    }
    
    if($errors){
        $errLive = array('id' => $lid, 'title' => $title, 'description' => $description, 'date' => $date, 'visible' => $visible, 'active' => $active, 'homepage' => $front, 'rtmpid' => $fmsid);
    } else {
        $flash = lang('liveudl_create_success');
        $save = true;
        if(!$lid){
            $save = $liveudlquery->setLive($title, $description, $date, $visible, $active, $front, $fmsid, $file);
        } else {
            if(!is_array($file) && !$deleteThumb){
                $save = $liveudlquery->setLive($title, $description, $date, $visible, $active, $front, $fmsid, $file, $lid);
            } else {
                $save = $liveudlquery->setLive($title, $description, $date, $visible, $active, $front, $fmsid, $file, $lid, $oldfile);
            }
            
            $flash = lang('liveudl_edit_success');
        }
        
        if(!$save){
            $_SESSION['liveudl_flashmsgerr'] = lang('liveudl_uploadErr');
        } else {
            $_SESSION['liveudl_flashmsg'] = $flash;
        }
        toManagePage(); 
    }
}

$action = 'add';
if(!empty($_GET)){
    $id = filter_input(INPUT_GET, 'live');
    if($id){
        $liveudlquery->setActive($id);
        
        $ref = filter_input(INPUT_GET, 'ref');
        if($ref === 'lives') {
            redirToRef($ref);
        } elseif($ref === 'live') {
            header('Location: '. $liveudlquery->live_link($id));
            exit;
        }
        toManagePage();
    }
    
    $id = filter_input(INPUT_GET, 'home');
    if($id){
        $liveudlquery->setFront($id);
        toManagePage();
    }
    
    $id = filter_input(INPUT_GET, 'visible');
    if($id){
        $liveudlquery->setVisible($id);
        toManagePage();
    }
    
    $action = filter_input(INPUT_GET, 'edit') ? 'edit' : (filter_input(INPUT_GET, 'delete') ? 'delete' : $action);
    $id = intval(filter_input(INPUT_GET, $action));
}

$title = 'liveudl_title_new';
$tpl = 'action.html';

// EDIT, DELETE
if($id){
    $live = $liveudlquery->getLive($id);
    $live['description'] = preg_replace('#\\\r\\\n#Usi', chr(13), $live['description']);
    Assign('lid', $id);
    
    if($live === $liveudlquery->getDefault()) $tpl = 'error.html';

    if($action === 'edit'){
        $title = 'liveudl_title_edit';
        if($errors){
            $errLive['thumb'] = $live['thumb'];
            $live = $errLive;
        }
    } else 
        $title = 'liveudl_title_delete';
} elseif($errors){
	if($errLive['id']) $title = 'liveudl_title_edit';
	$live = $errLive;
}

$rtmp = $liveudlquery->getAllRtmp(true);

Assign('rtmp', $rtmp);
Assign('live', $live);
Assign('errors', $errors);
Assign('titlepage', $title);
Assign('ludlAction', $action);

mkdir(BASEURL .'/files/thumbs/lives');
/** Set HTML title */
subtitle(lang('liveudl_manager'));

/** Set HTML template */
template_files($tpl, LIVEUDL_ADMIN_DIR);
?>