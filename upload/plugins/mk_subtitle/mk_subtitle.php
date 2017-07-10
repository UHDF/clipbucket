<?php
/*
Plugin Name: Subtitle maker
Description: For making subtitle
Author: Adrien Ponchelet
Author Website: https://www.u-picardie.fr
ClipBucket Version: 2.8.2
Version: 0.1
*/

if (!$cbplugin->is_installed('common_library.php'))
	e(sprintf(lang("plugin_not_installed : %s"),"Common Library"));
else
	require_once PLUG_DIR.'/common_library/common_library.php';

// Define Plugin's uri constants
define("SITE_MODE",'/admin_area');
define('SUBTITLE_MAKER_BASE',basename(dirname(__FILE__)));

define("SUBTITLE_MAKER_URL",BASEURL.SITE_MODE."/plugin.php?folder=".SUBTITLE_MAKER_BASE."/admin&file=subtitle_maker.php");

/**
 * Add a new entry "Subtitle maker" into the video manager menu named "Actions" associated to each video
 * 
 * @param int $vid 
 * 		the video id
 * @return string
 * 		the html string to be inserted into the menu
 */
function addSubtitleMaker($vid){
	$idtmp=$vid['videoid'];

	return '<li><a role="menuitem" href="'.SUBTITLE_MAKER_URL.'&video='.$idtmp.'">'.lang('mksub_title').'</a></li>';
}


/**
 * Update the draft file
 *
 * @param string $marker Path to the marker file
 */
function updateMarkerFile($marker){

	// Read files in array
	$lines = file($marker, FILE_IGNORE_NEW_LINES);	// Read file in array (without break line)

	for ($i = 1; $i <= $_POST['nbMarker']; $i++){
		if (!empty($_POST['phrase'.$i])){
			$t = explode("\t", $lines[$i-1]);		// Line to array
			$t[3] = $_POST['phrase'.$i];			// Replace with the new value
			$t = implode("\t", $t);					// Array to line
			$lines[($i-1)] = $t;					// New line assignation
		}
	}

	$fp = fopen($marker, "w+");						// Open the draft file
	for ($i = 0; $i < count($lines); $i++){			// Each line of table
		fwrite($fp, $lines[$i]."\n");				// Write
	}
	fclose($fp);									// Close file

}


/**
 * Write the subtitle file
 *
 * @param string $marker Path to the marker file
 * @param string $subtitle Path to the subtitle file
 * @param int $nbcar_by_line Number of caracter by line for one subtitle
 */
function makeSubtitleFile($marker, $subtitle, $nbcar_by_line = 70){

	$fp = fopen($subtitle, "w+");			// Open the draft file
	fwrite($fp, "WEBVTT\n\n");				// Header

	if (file_exists($marker)){

		$lines = file($marker, FILE_IGNORE_NEW_LINES);

		foreach ($lines as $line_num => $line) {
			$t = explode("\t", $line);

			$t[3] = trim($t[3]);			// Delete unwanted space

			// Test length of subtitle and number of line
			$nbCar = strlen($t[3]);
			if ( ($nbCar > $nbcar_by_line) ){
				$t[3] = wordwrap($t[3], $nbcar_by_line, "\n", true);

				if (substr_count($t[3], "\n") > 1){
					$t[3] = $t[3]."\n\nNOTE : You must split the subtitle above (too much line).";
				}
			}

			// If sentence exist, write in subtitle file
			if ($t[3] <> ''){
				fwrite($fp, secondToTime($t[0])." --> ".secondToTime($t[1])."\n".$t[3]."\n\n");
			}

		}
	}

	fclose($fp);
}


/**
 * Update the subtitle file
 *
 * @param string $subtitle Path to the subtitle file
 */
function updateSubtitleFile($subtitle){

	if (file_exists($subtitle)){

		$fp = fopen($subtitle, "w+");			// Open the draft file
		fwrite($fp, $_POST['subdata']);
		fclose($fp);
	}

}

/**
 * Test file exist
 *
 * @param integer $vid The video database id (videoid)
 */
function isMarker($vid){
	return (file_exists(FILES_DIR."/marker/marker_".$vid.".txt")) ? true : false;
}

/**
 * Test file exist
 *
 * @param integer $vid The video database id (videoid)
 */
function isSubtitle($vid){
	return (file_exists(FILES_DIR."/subtitle/subtitle_".$vid.".vtt")) ? true : false;
}

/**
 * Delete the subtitle file
 *
 * @param string $subtitle Path to the subtitle file
 */
function deleteSubtitleFile($subtitle){

	unlink($subtitle);

}











/*

Portion de code pour envoyer un email :

	// Sending Email
	global $cbemail,$userquery;

	$tpl = $cbemail->get_template('video_activation_email');
	#$user_fields = $userquery->get_user_field($video['userid'],"username,email");
	
	$more_var = array
	(
		'{username}'	=> $video['username'],
		'{video_link}' => videoLink($video)
	);
	
	if(!is_array($var))
		$var = array();
	
	$var = array_merge($more_var,$var);
	$subj = $cbemail->replace($tpl['email_template_subject'],$var);
	$msg = nl2br($cbemail->replace($tpl['email_template'],$var));

	//Now Finally Sending Email
	cbmail(array('to'=>$video['email'], 'from'=>WEBSITE_EMAIL, 'subject'=>$subj, 'content'=>$msg));

*/

	/*
	 * This Function generate anchors for  subtitle vtt file if exist
	 */
	function getSubtitleVtt($data = ''){
		if ($data['videoid']){
			$subfile = BASEDIR.'/files/subtitle/subtitle_'.$data['videoid'].'.vtt';
			$suburl = BASEURL.'/files/subtitle/subtitle_'.$data['videoid'].'.vtt';
			$str="";
			if (file_exists($subfile)){
				$str= '<track kind="subtitles" src="'.$suburl.'" srclang="fr" label="French" default/>';
			}
			echo $str;
		}
	}
	// use {ANCHOR place="getSubtitleVtt" data=$vdata} to add the HTML string into the file.
	register_anchor_function('getSubtitleVtt','getSubtitleVtt');
	






if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("mk_subtitle")]=='yes'){
	$cbvid->video_manager_link[]='addSubtitleMaker';
	add_admin_menu('Videos',lang('mksub_title'),'lstvideo.php',SUBTITLE_MAKER_BASE.'/admin');

}

?>