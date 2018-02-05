<?php
require_once  '../includes/admin_config.php';
require_once BASEDIR .'/api/push.php';

if($vidmquery === null){
    class VidmDefaultClass{
        function isActivated(){ return false; }
    }
    $vidmquery = new VidmDefaultClass();
}

if(!$vidmquery->isActivated()){
    include_once BASEDIR .'/admin_area/video_manager_vidmbkp.php';
    exit;
}

global $cbvid;

$userquery->admin_login_check();
// Permission to access videos moderation
$userquery->login_check('video_moderation');

$pages->page_redir();


// Assigning page and subpage
if(!defined('MAIN_PAGE'))
	define('MAIN_PAGE', 'Videos');
if(!defined('SUB_PAGE'))
	define('SUB_PAGE', lang('vidm_admMenu'));


if(isset($_POST['reconvert_selected']) || isset($_GET['reconvert_video'])) {
    reConvertVideos();
}

//Feature / UnFeature Video
if(isset($_GET['make_feature'])){
    $video = mysql_clean($_GET['make_feature']);
    $cbvid->action('feature', $video);
    $row = $myquery->Get_Website_Details();
    
    if($row['notification_option']=='1'){
        send_video_notification($video);
    }   
}

if(isset($_GET['make_unfeature'])){
    $video = mysql_clean($_GET['make_unfeature']);
    $cbvid->action('unfeature', $video);
}

//Using Multiple Action
if(isset($_POST['make_featured_selected'])){
    for($id=0; $id<=count($_POST['check_video']); $id++){
        $cbvid->action('feature', $_POST['check_video'][$id]);
    }
    $eh->flush();
    e('Selected videos have been set as featured', 'm');
}

if(isset($_POST['make_unfeatured_selected'])){
    for($id=0; $id<=count($_POST['check_video']); $id++){
        $cbvid->action('unfeature', $_POST['check_video'][$id]);
    }
    $eh->flush();
    e('Selected videos have been removed from featured list', 'm');
}

//Activate / Deactivate

if(isset($_GET['activate'])){
    $video = mysql_clean($_GET['activate']);
    $cbvid->action('activate', $video);
}

if(isset($_GET['deactivate'])){
    $video = mysql_clean($_GET['deactivate']);
    $cbvid->action('deactivate', $video);
}

//Using Multple Action
if(isset($_POST['activate_selected'])){
    for($id=0; $id<=count($_POST['check_video']); $id++){
        $cbvid->action('activate',$_POST['check_video'][$id]);
    }
    $eh->flush();
    e('Selected Videos Have Been Activated', 'm');
}

if(isset($_POST['deactivate_selected'])){
    for($id=0; $id<=count($_POST['check_video']); $id++){
        $cbvid->action('deactivate', $_POST['check_video'][$id]);
    }
    $eh->flush();
    e('Selected Videos Have Been Dectivated', 'm');
}
	
//Delete Video
if(isset($_GET['delete_video'])){
    $video = mysql_clean($_GET['delete_video']);
    $cbvideo->delete_video($video);
}

//Deleting Multiple Videos
if(isset($_POST['delete_selected'])){
    for($id = 0; $id <= count($_POST['check_video']); $id++){
            $cbvideo->delete_video($_POST['check_video'][$id]);
    }
    $eh->flush();
    e(lang('vdo_multi_del_erro'), 'm');
}

/****************************************************************************************************/
if(!function_exists('vidm_ineditorspicks')){
    function vidm_ineditorspicks($video){
        global $vidmquery;
        return $vidmquery->inEditorsPicks($video);
    }
    
    $Smarty->register_function('vidm_ineditorspicks', 'vidm_ineditorspicks');
}

if(!function_exists('vidm_actionsMenuEp')){
    function vidm_actionsMenuEp($params){
        $txt = lang('vidm_displayOnHome');
        if(vidm_ineditorspicks($params['video'])){
            $txt = lang('vidm_hideFromHome');
        }
        
        $replace = '/'. strip_tags($params['txt']) .'/';
        echo preg_replace($replace, $txt, $params['txt']);
    }
    
    $Smarty->register_function('vidm_actionsMenuEp', 'vidm_actionsMenuEp');
}

/****************************************************************************************************/
//Calling Video Manager Functions
call_functions($cbvid->video_manager_funcs);

$page = mysql_clean($_GET['page']);
$get_limit = create_query_limit($page, RESULTS);

$all_categories = $cbvid->get_categories();
$all_category_ids = array();

foreach($all_categories as $cats){
    $all_category_ids[] = $cats['category_id'];
}

if(isset($_GET['category'])){
    if($_GET['category'][0] == 'all'){
        $cat_field = '';
    } else  {
        $cat_field = $_GET['category'];
    }
}


if(isset($_GET['search'])){
    $array = array(
        'videoid' => $_GET['videoid'],
        'videokey' => $_GET['videokey'],
        'title'	=> $_GET['title'],
        'tags'	=> $_GET['tags'],
        'user' => $_GET['userid'],
        'category' => $cat_field,
        'featured' => $_GET['featured'],
        'active' => $_GET['active'],
        'status' => $_GET['status'],
    );		
}

$result_array = $array;
// Getting Video List
$result_array['limit'] = $get_limit;
if(!$array['order'])
    $result_array['order'] = ' videoid DESC ';


$videos = get_videos($result_array);
Assign('videos', $videos);	

//Collecting Data for Pagination
$vcount = $array;
$vcount['count_only'] = true;
$total_rows  = get_videos($vcount);
$total_pages = count_pages($total_rows, RESULTS);
$pages->paginate($total_pages, $page);

// Correction pagination
$pagination = $Smarty->get_template_vars('pagination');
$pagination = preg_replace('#<a>&hellip;<\/a>#', '<li><a>&hellip;</a></li>', $pagination);
assign('pagination', $pagination);

//Category Array
if(is_array($_GET['category']))
    $cats_array = array($_GET['category']);		
else {
    preg_match_all('/#([0-9]+)#/', $_GET['category'], $m);
    $cats_array = array($m[1]);
}

$cat_array = array(lang('vdo_cat'),
    'type'=> 'checkbox',
    'name'=> 'category[]',
    'id'=> 'category',
    'value'=> array('category',$cats_array),
    'hint_1'=>  lang('vdo_cat_msg'),
    'display_function' => 'convert_to_categories'
);
assign('cat_array', $cat_array);


subtitle(lang('vidm_subtitle'));
template_files('manage.html', VIDM_ADMIN_DIR);

display_it();

?>