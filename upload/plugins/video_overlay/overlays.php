<?php
/*
Plugin Name: Video overlays
Description: Add a tab into the edit_video page that enable video enrichment like tooltip.
Author: Adrien Ponchelet
Author Website: https://www.u-picardie.fr/
ClipBucket Version: 2.8.1
Version: 1.0
*/

/**
 * Define Plugin's uri constants. These constants represents folders or urls
 */
define("SITE_MODE",'/admin_area');
define('OVERLAY_BASE',basename(dirname(__FILE__)));
define('OVERLAY_DIR',PLUG_DIR.'/'.OVERLAY_BASE);
define('OVERLAY_URL',PLUG_URL.'/'.OVERLAY_BASE);
define('OVERLAY_ADMIN_DIR',OVERLAY_DIR.'/admin');
define('OVERLAY_ADMIN_URL',OVERLAY_URL.'/admin');

//require_once OVERLAY_DIR.'/overlay_class.php';

if (!function_exists('getOverlayList')){
	/**
	 * Define the Anchor for adding vtt file into the videojs player if it exists
	 */
	function getOverlayList($data = ''){
		global $db;
		$query = "SELECT * FROM ".tbl('video_overlay')." WHERE `videoid` = '".$data['videoid']."'";
		$respons = select($query);
		$str = "";
		$cpt = 0;
		if (count($respons) > 0) {
			foreach ($respons as $key => $val){
				$content = $respons[$key]["content"];

				$json = json_decode($respons[$key]["content"]);

				$str .= '{align:\''.$json->{'align'}.'\', showBackground:'.$json->{"showBackground"}.', class:\''.$json->{"class"}.'\', content:\''.addslashes($json->{"content"}).'\', start:'.$json->{"start"}.', end:'.$json->{"end"}.'}';

//				$str .= '{'.$content.'}';
				//$str .= $respons;
				if ($cpt < (count($respons)-1)){
					$str .= ',';
				}
				$cpt++;
			}
		}

		echo htmlspecialchars_decode($str, ENT_QUOTES);
		//echo $str;
	}

	// use {ANCHOR place="getOverlayList" data=$video} to add the HTML string into the file.
	register_anchor_function('getOverlayList','getOverlayList');
}




if (!function_exists('getOverlayCount')){
	function getOverlayCount($data = ''){
		global $db;
		$nb = 0;
		$vid = $data['videoid'];

		if ($vid){
			$respons = $db->_select("SELECT COUNT(`id`) AS `TOTAL` FROM ".tbl('video_overlay')." WHERE `videoid` = '".$vid."';");
			if (count($respons) > 0) {
				$nb = $respons[0]["TOTAL"];
			}
		}

		return intval($nb);
	}

	global $Smarty;
	$Smarty->register_function('getOverlayCount','getOverlayCount');
}

?>