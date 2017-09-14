<?php
// Use the already existing functions
require_once '../includes/admin_config.php';
$userquery->admin_login_check();
$userquery->login_check('admin_access');
$pages->page_redir();
global $cbvid;

require_once '../includes/classes/video.class.php';
require_once '../includes/functions_video.php';
	
// Assigning page and subpage
if(!defined('MAIN_PAGE')){
    define('MAIN_PAGE', 'Videos');
}

if(!defined('SUB_PAGE')){
    define('SUB_PAGE', 'Subtitle maker');
}

$page = mysql_clean($_GET['page']);
$get_limit = create_query_limit($page,RESULTS);


// Getting Video List
$result_array['limit'] = $get_limit;
if(!$array['order']){
	$result_array['order'] = " videoid DESC ";
}


$videos = get_videos($result_array);
Assign('videos', $videos);	

// Collecting Data for Pagination
$vcount['count_only'] = true;
$total_rows  = get_videos($vcount);
$total_pages = count_pages($total_rows,RESULTS);
$pages->paginate($total_pages, $page);


// echo $db->db_query;
subtitle("Subtitle maker");

// Output
template_files(PLUG_DIR.'/mk_subtitle/template/lstvideo.html',true);