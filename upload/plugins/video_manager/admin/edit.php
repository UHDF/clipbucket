<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once '../includes/admin_config.php';

if($vidmquery === null){
    class VidmDefaultClass{
        function isActivated(){ return false; }
    }
    $vidmquery = new VidmDefaultClass();
}

$userquery->admin_login_check();
// Permission to access videos moderation
$userquery->login_check('video_moderation');

$pages->page_redir();

// Assigning page and subpage
if(!defined('MAIN_PAGE'))
	define('MAIN_PAGE', 'Videos');

if($vidmquery->isActivated()){
    if(!defined('SUB_PAGE')) define('SUB_PAGE', lang('vidm_admMenu'));
} else {
    if($_GET['active'] == 'no')
        define('SUB_PAGE', 'List Inactive Videos');
    else
        define('SUB_PAGE', 'Videos Manager');
}

if(@$_GET['msg']){
    $msg[] = clean($_GET['msg']);
}

$video = mysql_clean($_GET['video']);

//Updating Video Details
if(isset($_POST['update'])){
    $Upload->validate_video_upload_form();
    if(empty($eh->error_list)){
        $myquery->update_video();
        $myquery->set_default_thumb($video, $_POST['default_thumb']);
    }
}

if($vidmquery->isActivated()){
	$vidmdate = filter_input(INPUT_POST, 'vidm-date');
	if($vidmdate){
		if(!$vidmquery->updateDateCreated($video, $vidmdate)){
			Assign('vidmErrorDateCreated', lang('vidm_errorDateCreated'));
		}
	}
	
    $ep_action = filter_input(INPUT_GET, 'ep');
    if($ep_action){
        if($ep_action === 'hidehome' && function_exists('remove_vid_editors_pick')){
            remove_vid_editors_pick($video);
        }
        if($ep_action === 'displayhome' && function_exists('add_to_editor_pick')){
            add_to_editor_pick($video);
        }

        if($eh->error_list){
            for($i = 0; $i < count($eh->error_list); $i++){
                $m = $eh->error_list[$i];
                if($m === 'Video is already in editor\'s pick'){
                    unset($eh->error_list[$i]);
                    $_SESSION['vidm_err'][] = lang('vidm_alreadyInEp');
                }
            }
        }
        if($eh->message_list){
           for($i = 0; $i < count($eh->message_list); $i++){
                $m = $eh->message_list[$i];
                if($m === 'Video has been removed from editor\'s pick'){
                    unset($eh->message_list[$i]);
                    $_SESSION['vidm_msg'][] = lang('vidm_removedFromEp');
                } elseif($m === 'Video has been added to editor\'s pick'){
                    unset($eh->message_list[$i]);
                    $_SESSION['vidm_msg'][] = lang('vidm_addedToEp');
                }
            } 
        }

        header('Location: '. $_SERVER['PHP_SELF'] .'?video='. $video);
        exit;
    }
	
	$thumb = filter_input(INPUT_GET, 'delete');
	if($thumb && $myquery->VideoExists($video)){
		$data = empty($data) ? get_video_details($video) : $data;
		$file_name_num = explode('-', $thumb);
		$num = get_thumb_num($thumb);

		$file_name = $file_name_num[0];

		delete_video_thumb($data['file_directory'], $file_name,$num);
		$_SESSION['vidm_msg'][] = lang('video_thumb_delete_msg');
		
		header('Location: '. $_SERVER['PHP_SELF'] .'?video='. $video);
        exit;
	}

    $mode = filter_input(INPUT_GET, 'mode');
    if($mode){
        $modedata = $cbvid->action($_GET['mode'], $video);
        $_SESSION['vidm_modedata'] = $modedata;

        if($eh->message_list){
           for($i = 0; $i < count($eh->message_list); $i++){
                $m = $eh->message_list[$i];
                if($m === lang('class_fr_msg1')){
                    unset($eh->message_list[$i]);
                    $_SESSION['vidm_msg'][] = lang('vidm_unfeaturedmsg');
                } elseif($m === lang('class_vdo_fr_msg')){
                    unset($eh->message_list[$i]);
                    $_SESSION['vidm_msg'][] = lang('vidm_featuredmsg');
                }
            } 
        }

        header('Location: '. $_SERVER['PHP_SELF'] .'?video='. $video);
        exit;
    }

    if(isset($_SESSION['vidm_err'])){
        $eh->error_list = array_merge($eh->error_list, $_SESSION['vidm_err']);
        unset($_SESSION['vidm_err']);
    }
    if(isset($_SESSION['vidm_msg'])){
        $eh->message_list = array_merge($eh->message_list, $_SESSION['vidm_msg']);
        unset($_SESSION['vidm_msg']);
    }
    if(isset($_SESSION['vidm_modedata'])){
        assign('modedata', $_SESSION['vidm_modedata']);
        unset($_SESSION['vidm_modedata']);
    }
} else {
    //Performing Video Actions
    if($_GET['mode']!=''){
        $modedata = $cbvid->action($_GET['mode'],$video);
        assign("modedata",$modedata);
    }
}

//Check Video Exists or Not
if($myquery->VideoExists($video)){
    //Deleting Comment
    $cid = mysql_clean($_GET['delete_comment']);
    if(!empty($cid)){
        $myquery->delete_comment($cid);
    }

    //pr($video,true);
    $data = get_video_details($video);
    $data['is_featured'] = $vidmquery->is_featured($data);
    Assign('udata', $userquery->get_user_details($data['userid']));
    Assign('data', $data);
     //pr($data,true);
} else {
    $msg[] = lang('class_vdo_del_err');
}

$type = 'v';
$comment_cond = array();
$comment_cond['order'] = ' comment_id DESC';
$comment_cond['videoid'] = $video;
$comments = getComments($comment_cond);
assign('comments', $comments);

    
//Deleting comment 
if(isset($_POST['del_cmt'])){
    $cid = mysql_clean($_POST['cmt_id']);
    $myquery->delete_comment($cid);
}

if(!$array['order'])
    $result_array['order'] = ' doj DESC LIMIT 1  ';

$users = get_users($result_array);

Assign('users', $users);


if(!$array['order'])
    $result_array['order'] = ' views DESC LIMIT 8 ';
$videos = get_videos($result_array);

Assign('videos', $videos);


$numbers = array(100, 1000, 15141, 3421);
if(!function_exists('format_number')){
    function format_number($number){
        if($number >= 1000) {
            return floor($number / 1000 . 'k');   // NB: you will want to round this
        } else {
            return $number;
        }
    }
}

if(function_exists('get_ep_videos')){
    $ep_videos = get_ep_videos();
    if(isset($_POST['update_order'])){
        if(is_array($ep_videos)){
            foreach($ep_videos as $epvid){
                $order = $_POST['ep_order_'. $epvid['pick_id']];
                move_epick($epvid['videoid'], $order);
            }
        }
        $ep_videos = get_ep_videos();
    }
}


$get_limit = create_query_limit($page, 5);
$videos = $cbvid->action->get_flagged_objects($get_limit);
Assign('flagedVideos', $videos);

$comments = getComments($comment_cond);
assign('comments', $comments);

// DELETING THUMBNAIL
$thumb = filter_input(INPUT_GET, 'delete');
if($thumb && $myquery->VideoExists($video)){
    $data = empty($data) ? get_video_details($video) : $data;
    $file_name_num = explode('-', $thumb);
    $num = get_thumb_num($thumb);

    $file_name = $file_name_num[0];

    delete_video_thumb($data['file_directory'], $file_name,$num);    
}

if($vidmquery->isActivated()){
    Assign('vidm_defaultCategory', $cbvid->get_default_category());

    subtitle(lang('vidm_subtitleEdit'));
    template_files('edit.html', VIDM_ADMIN_DIR);
} else {
    subtitle("Edit Video");
    template_files('edit_video.html');
}

unset($_POST);
display_it();

?>