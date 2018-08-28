<?php
/*
Plugin Name: Define chapters
Description: Add a tab into the edit_video page that enable video chapter edition
Author: Franck Rouze
Author Website: http://semm.univ-lille1.fr/
ClipBucket Version: 2.8.1
Version: 1.0
*/

/**
 * Define Plugin's uri constants. These constants represents folders or urls
 */
define("SITE_MODE",'/admin_area');
define('CHAPTER_BASE',basename(dirname(__FILE__)));
define('CHAPTER_DIR',PLUG_DIR.'/'.CHAPTER_BASE);
define('CHAPTER_URL',PLUG_URL.'/'.CHAPTER_BASE);
define('CHAPTER_ADMIN_DIR',CHAPTER_DIR.'/admin');
define('CHAPTER_ADMIN_URL',CHAPTER_URL.'/admin');

require_once CHAPTER_DIR.'/chapter_class.php';

if(!function_exists('getVTTFile')){
	/**
	 * Define the Anchor for adding vtt file into the videojs player if it exists
	 */
	function getVTTFile(){
		global $db;
		$query = " SELECT * FROM ".tbl('video')." WHERE `videokey`='".$_GET["v"]."'";
		//echo '<pre>'.$query.'</pre>';die();
		$respons = select( $query );
		$str="";
		if (count($respons)>0) {
			$filename=$respons[0]["file_name"];
			$fileDirectory=$respons[0]['file_directory'];
			$dstFullpath=dirname(__FILE__)."/../../files/videos/".$fileDirectory."/track_".$filename.'.vtt';
			$fileurl=VIDEOS_URL.'/'.$fileDirectory."/track_".$filename.'.vtt';
			if (file_exists($dstFullpath)) {
				$str='<track kind="chapters" src="'.$fileurl.'" srclang="fr" label="French" default/>';
			}
		}
		echo $str;
	}
	// use {ANCHOR place="getVTTFile" data=$video} to add the HTML string into the file.
	register_anchor_function('getVTTFile','getVTTFile');
}

if(!isset($cbplugin)) global $cbplugin;
$videoManagerIsActive = false;
$p_installed = $cbplugin->getInstalledPlugins();
foreach($p_installed as $p){
	if($p['plugin_file'] === 'video_manager.php' && $p['plugin_folder'] === 'video_manager'){
		$videoManagerIsActive = $p['plugin_active'] === 'yes';
	}
}

$get_vid = filter_input(INPUT_GET, 'video');
if($get_vid){
	$cvid = $db->select(tbl('video'), '*', 'embed_code = "none" AND (videoid = '. intval($get_vid) .' OR videokey = '. mysql_clean($get_vid).')');
	if($videoManagerIsActive && count($cvid)){
		$_POST['data']['video'] = $get_vid;
		register_anchor('<li role="presentation"><a href="#chapters" aria-controls="required" role="tab" data-toggle="tab">'. lang('Chapters') .'</a></li>', 'vidm_navtab');
		$html = '';
		ob_start();
		require_once CHAPTER_ADMIN_DIR .'/set_chapters.php';
		$html = ob_get_clean();
		register_anchor('<div id="chapters" role="tabpanel" class="tab-pane">'. $html .'</div>', 'vidm_tabcontent');
	}
}

?>