<?php
/*

*/

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

require(PLUG_DIR."/".SUBTITLE_MAKER_BASE."/functions.php");		// Require function file
$video = mysql_clean($_GET['video']);							// Get th id of video
$data = get_video_details($video);								// Get the details of video
$lst_vid = get_video_files($data);								// Get list of URL for each files (sd, hd)

// video file
if (is_array($lst_vid)){
	$video_file = $lst_vid[0];
	$video_file = str_replace(BASEURL, BASEDIR, $video_file);
}
else{
	// video URL
	$video_file = $data["remote_play_url"];
}





/**
*	Existing element
*/
$element = array();
$savedSub = 0;
	
if (file_exists($marker)){

	$lines = file($marker, FILE_IGNORE_NEW_LINES);
	assign('nbMarker', count($lines));


	foreach ($lines as $line_num => $line) {

		$t = explode("\t", $line);

		$begin = substr($t[0],0,(strpos($t[0], ".")+4));
		$end = substr($t[1],0,(strpos($t[1], ".")+4));

		$element[] = array(
			($t[0]-$delayBefore),		// 0 : Begin
			($t[1]+$delayAfter),		// 1 : End
			$t[2],						// 2 : line count
			$t[3],						// 3 : Sentence
			$begin,						// 4 : Converted begin
			$end,						// 5 : Converted end
			secondToTime($begin),		// 6 : Human reading converted begin
			secondToTime($end),			// 7 : Human reading Converted end
			round(($end-$begin), 2)		// 8 : Duration
		);

		if (isset($t[3])){
			$savedSub++;
		}

	}

	assign('savedSub', $savedSub);
}

assign('marker', $element);













































//Using Multple Action
if(isset($_POST['make_featured_selected'])){
	for($id=0;$id<=count($_POST['check_video']);$id++){
		$cbvid->action('feature',$_POST['check_video'][$id]);
	}
	$eh->flush();
	e("Selected videos have been set as featured","m");
}
if(isset($_POST['make_unfeatured_selected'])){
	for($id=0;$id<=count($_POST['check_video']);$id++){
		$cbvid->action('unfeature',$_POST['check_video'][$id]);
	}
	$eh->flush();
	e("Selected videos have been removed from featured list","m");
}


//Using Multple Action
if(isset($_POST['activate_selected'])){
	for($id=0;$id<=count($_POST['check_video']);$id++){
		$cbvid->action('activate',$_POST['check_video'][$id]);
	}
	$eh->flush();
	e("Selected Videos Have Been Activated","m");
}
if(isset($_POST['deactivate_selected'])){
	for($id=0;$id<=count($_POST['check_video']);$id++){
		$cbvid->action('deactivate',$_POST['check_video'][$id]);
	}
	$eh->flush();
	e("Selected Videos Have Been Dectivated","m");
}

	

//Deleting Multiple Videos
if(isset($_POST['delete_selected']))
{
	for($id=0;$id<=count($_POST['check_video']);$id++)
	{
		$cbvideo->delete_video($_POST['check_video'][$id]);
	}
	$eh->flush();
	e(lang("vdo_multi_del_erro"),"m");
}


	//Calling Video Manager Functions
	call_functions($cbvid->video_manager_funcs);
		
	$page = mysql_clean($_GET['page']);
	$get_limit = create_query_limit($page,RESULTS);

	$all_categories = $cbvid->get_categories();
	$all_category_ids = array();

	foreach ($all_categories as $cats ) {
		$all_category_ids[] = $cats['category_id'];
	}
	
	if ( isset($_GET['category']) )
	{
		if ( $_GET['category'][0] == 'all')
		{
			$cat_field = "";
		}
		else 
		{
			$cat_field = $_GET['category'];
		}
	}
	

	if(isset($_GET['search']))
	{
		
		$array = array
		(
		 'videoid' => $_GET['videoid'],
		 'videokey' => $_GET['videokey'],
		 'title'	=> $_GET['title'],
		 'tags'	=> $_GET['tags'],
		 'user' => $_GET['userid'],
		 'category' => $cat_field,
		 'featured' => $_GET['featured'],
		 'active'	=> $_GET['active'],
		 'status'	=> $_GET['status'],
		 );		
	}
	

	$result_array = $array;
	//Getting Video List
	$result_array['limit'] = $get_limit;
	if(!$array['order'])
		$result_array['order'] = " videoid DESC ";

	
	$videos = get_videos($result_array);
	
	Assign('videos', $videos);	
	//pr($videos,true);


	//Collecting Data for Pagination
	$vcount = $array;
	$vcount['count_only'] = true;
	$total_rows  = get_videos($vcount);
	$total_pages = count_pages($total_rows,RESULTS);
	$pages->paginate($total_pages,$page);

	//Category Array
	if(is_array($_GET['category']))
		$cats_array = array($_GET['category']);		
	else
	{
		preg_match_all('/#([0-9]+)#/',$_GET['category'],$m);
		$cats_array = array($m[1]);
	}
	$cat_array =	array(lang('vdo_cat'),
					'type'=> 'checkbox',
					'name'=> 'category[]',
					'id'=> 'category',
					'value'=> array('category',$cats_array),
					'hint_1'=>  lang('vdo_cat_msg'),
					'display_function' => 'convert_to_categories');

	assign('cat_array',$cat_array);
//echo $db->db_query;
subtitle("Subtitle maker");

// Output
template_files(PLUG_DIR.'/mk_subtitle/admin/lstvideo.html',true);
