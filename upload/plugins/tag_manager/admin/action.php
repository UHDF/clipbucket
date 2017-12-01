<?php
require_once TAGM_DIR.'/tag_manager_class.php';

$userquery->admin_login_check();
if ($cbplugin->is_installed('common_library.php'))
    $userquery->login_check(getStoredPluginName('tag_manager'));

$exist = filter_input(INPUT_GET, 'exist');
if($exist){
    if($tagmquery->checkExist(trim($exist))) echo 'exist';
    exit;
}

// Get tags //  videos
$action = filter_input(INPUT_GET, 'action');
if($action){
    if($action === 'tags'){
        $alltags = $tagmquery->getAllTags();
        $tags = array();
        foreach($alltags as $t => $v){
            $tags[] = html_entity_decode($t);
        }
        echo json_encode($tags);
        exit;
    } elseif($action === 'videos'){
        $videos = $tagmquery->getVideos();
        $res = array();
        foreach($videos as $v){
            $res[] = array('id' => $v['videoid'], 'title' => trim($v['title']));
        }
        echo json_encode($res);
        exit;
    }
}

$action = filter_input(INPUT_POST, 'action');
// Case : Edit / Delete
$old = filter_input(INPUT_POST, 'old');
// Case : Create / Edit
$new = filter_input(INPUT_POST, 'tag');
// Case : Create / Edit / Delete
$video = filter_input(INPUT_POST, 'video');

if(session_id() === '') session_start();

if($action === 'create' && $new && $video){
    $new = $tagmquery->createTag($new, $video);
    $_SESSION['tagm_action'] = lang('tagm_create_success', '<b>'. $new .'</b>');
} elseif($action === 'edit' && $old && $new && $video){
    $new = $tagmquery->editTag($old, $new, $video);
    $_SESSION['tagm_action'] = lang('tagm_edit_success', '<b>'. $old .'</b>').lang('tagm_edit_success2', '<b>'. $new .'</b>');
} elseif($action === 'delete' && $old && $video){
    $tagmquery->deleteTag($old, $video);
    $_SESSION['tagm_action'] = lang('tagm_delete_success', '<b>'. $old .'</b>');
}

header('Location: '. TAGM_MANAGEPAGE_URL);
?>