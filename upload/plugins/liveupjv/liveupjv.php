<?php
/*
Plugin Name: Live UPJV
Description: Display and manage live streaming
Author: Adrien Ponchelet
Author Website: https://www.u-picardie.fr
ClipBucket Version: 4.1 STABLE
Version: 1.0
*/

	// Define Plugin's uri constants
	define("SITE_MODE",'/admin_area');
	define('LIVEUPJV',basename(dirname(__FILE__)));			// *** Chemin du plugin


	/**
	 *	Get the stored information
	 *
	 *	@return array Array from database field
	 */
	function getLiveUPJV(){
		global $db;
		$selectLive = $db->_select('SELECT * FROM '.tbl("liveupjv"));

		$listLiveUPJV = array();

		for ($i = 0; $i < count($selectLive); $i++) {
			$listLiveUPJV[$i] = array($selectLive[$i]['id'], $selectLive[$i]['date_debut'], $selectLive[$i]['date_fin'], $selectLive[$i]['videoid']);
		}

		return $listLiveUPJV;
	}

	/**
	 * Get only the .m3u8 remote video
	 *
	 * @return array Array from database field
	 */
	function getStreamList(){
		global $db;
		$selectLive = $db->_select('SELECT * FROM '.tbl("video").' WHERE remote_play_url LIKE "%.m3u8";');

		$listStream = array();

		for ($i = 0; $i < count($selectLive); $i++) {
			$listStream[$i] = array($selectLive[$i]['videoid'], $selectLive[$i]['title'], $selectLive[$i]['remote_play_url']);
		}

		return $listStream;
	}

	/**
	 * Save information in database, update / insert
	 *
	 * @param [array] $post
	 * @return void
	 */
	function saveLiveUPJV($post){
		global $db;

		if (is_array($post)){
			if (isset($post['liveid'])){
				for ($i = 0; $i < (count($post['liveid'])-1); $i = $i+4){
					$db->execute("UPDATE `liveupjv` SET `date_debut` = '".$post['liveid'][$i+1]."', `date_fin` = '".$post['liveid'][$i+2]."', `videoid` = '".$post['liveid'][$i+3]."' WHERE id = '".$post['liveid'][$i]."';");
				}
			}

			if (
				( isset($post['vidid']) && !empty($post['vidid']) ) &&
				( isset($post['debut']) && !empty($post['debut']) ) &&
				( isset($post['fin']) && !empty($post['fin']) )
			){
				$db->execute("INSERT INTO `liveupjv` VALUE (NULL, '".$post['debut']."', '".$post['fin']."', '".$post['vidid']."');");
			}

		}
	}

	/**
	 * Select the live actualy streamable one hour before the selected hour.
	 *
	 * @return array Data about the stream
	 */
	function isLiveUPJV(){
		global $db;

		$selectLive = $db->_select("SELECT ".tbl("liveupjv").".videoid, title, DATE_FORMAT(date_debut, '%d/%m/%Y') AS jour_debut, DATE_FORMAT(date_debut, '%Hh%i') AS heure_debut, DATE_FORMAT(date_fin, '%d/%m/%Y') AS jour_fin, DATE_FORMAT(date_fin, '%Hh%i') AS heure_fin, date_debut, date_fin, remote_play_url FROM ".tbl("liveupjv")." INNER JOIN ".tbl("video")." ON ".tbl("liveupjv").".videoid = ".tbl("video").".videoid WHERE date_debut <= (NOW() + INTERVAL 1 HOUR) AND date_fin >= NOW() ORDER BY date_debut ASC;");

		$listStream = array();

		if (count($selectLive) >= 1){

			for ($i = 0; $i < count($selectLive); $i++) {
				$listStream[$i] = array(
					$selectLive[$i]['videoid'],
					$selectLive[$i]['title'],
					$selectLive[$i]['date_debut'],
					$selectLive[$i]['jour_debut'],
					$selectLive[$i]['heure_debut'],
					$selectLive[$i]['date_fin'],
					$selectLive[$i]['jour_fin'],
					$selectLive[$i]['heure_fin'],
					$selectLive[$i]['remote_play_url']
				);
			}

			$listStream["count"] = count($selectLive);

			return $listStream;
		}
	}

	/**
	 * Delete a planified stream
	 *
	 * @param [integer] $vidid
	 * @return void
	 */
	function deleteLiveUPJV($vidid){
		global $db;

		if (!empty($vidid)){
			$db->execute("DELETE FROM `liveupjv` WHERE `id` = '".$vidid."';");
		}
	}

	/**
	 * Make the template for displaying by ANCHOR
	 *
	 * @return void
	 */
	function displayLiveUPJV(){
		global $cbtpl;

		$liveList = isLiveUPJV();

		if ($liveList["count"] >= 1){
			assign('liveList', $liveList);

			$mavar = $cbtpl->fetch(PLUG_DIR.'/liveupjv/public/liveupjv.html');
			echo $mavar;
		}
	}

	// use {ANCHOR place="displayLiveUPJV"} to add the HTML string into the file.
	register_anchor_function('displayLiveUPJV','displayLiveUPJV');


	/**
	 *	Add entries for the plugin in the administration pages
	 */
	if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("liveupjv")]=='yes')
	add_admin_menu('Videos',lang('liveupjv_manage_stream'),'manage_stream.php',LIVEUPJV.'/admin');
?>