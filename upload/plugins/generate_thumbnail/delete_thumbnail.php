<?php
    require_once '../../includes/admin_config.php';
    $userquery->admin_login_check();
    $userquery->login_check('admin_access');
    $pages->page_redir();

    $cbvid = new CBvideo;
	
	$return = '';

    if(isset($_POST['id']) && isset($_POST['d'])){
		$d = filter_input(INPUT_POST, 'd');
		$n = filter_input(INPUT_POST, 'n');
        $video = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		
		if($d === null){
			echo json_encode(array('error' => 'Error, thumbnail not found'));
			return;
		}
		
		$vdetails = get_video_details($video);
		if($vdetails === false){
			echo json_encode(array('error' => 'Error, video not found'));
			return;
		}
		
		delete_video_thumb($vdetails['file_directory'], $vdetails['file_name'].'-', '-'. get_thumb_num($d));
		$thumb = array('num' => 0, 'file' => default_thumb(), 'name' => 'processing.jpg', 'id' => 'processing');
		if($n === 'none'){
			$thumb = array();
		} elseif($n === 'checked'){
			$thumbs = get_thumb($video, 1, true, false, false, true, false);
			if(count($thumbs)) $thumb = array('num' => get_thumb_num(end($thumbs)), 'file' => end($thumbs));
		}
		
		if(count($thumb)){
			$cbvid->set_default_thumb($video, mysql_clean($thumb['num']));
		}
		
		$return = 'last';
		if($n === ''){
			$return = '<li><img src="'. $thumb['file'] .'" width="100" height="100" />';
			$return .= '<div class="tools"><input type="radio" value="'. $thumb['name'] .'" id="'. $thumb['id'] .'" name="default_thumb" checked />';
			$return .= '</div></li>';
		}
		echo json_encode(array('error' => '', 'res' => $return));
		
		return;
	}