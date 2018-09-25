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
 *	Test for public subtitlize
 */
function addPublicSubtitleMaker($vid){
	$idtmp=$vid['videoid'];

	return '<li><a role="menuitem" href="'.BASEURL.'/plugins/'.SUBTITLE_MAKER_BASE.'/public/subtitle_maker.php?video='.$idtmp.'">'.lang('mksub_title').'</a></li>';

}


/**
 * Update the draft file
 *
 * @param string $marker Path to the marker file
 */
function updateMarkerFile($marker){

	$fp = fopen($marker, "w+");						// Open the draft file
	fwrite($fp, $_POST["submarker"]);				// Write
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
				// $t[3] = wordwrap($t[3], $nbcar_by_line, "\n", true);
				$t[3] = cutString($t[3]);

				if (substr_count($t[3], "\n") > 1){
					$t[3] = $t[3]."\n\nNOTE : You must split the subtitle above (too much line).";
				}
			}

			// If sentence exist, write in subtitle file
			if ($t[3] <> ''){
				$subline = secondToTime($t[0])." --> ".secondToTime($t[1]);
				if (!empty($t[4])){
					$subline .= " align:".$t[4]."";
				}
				$subline .= "\n".$t[3]."\n\n";

				fwrite($fp, $subline);

			}

		}
	}

	fclose($fp);
}



/**
 * Return size of string in pixel
 *
 * @param [string] $string
 * @return void
 */
function getPixelWidth($string){

	$carWidth_array = array(
		"a" => 14, "b" => 13, "c" => 13, "d" => 13, "e" => 14, "f" => 8, "g" => 13, "h" => 12, "i" => 5, "j" => 7, "k" => 12, "l" => 5, "m" => 18,
		"n" => 12, "o" => 14, "p" => 13, "q" => 13, "r" => 8, "s" => 12, "t" => 8, "u" => 12, "v" => 13, "w" => 18, "x" => 13, "y" => 13, "z" => 12,
		"," => 6, "?" => 13, ";" => 5, "." => 6, ":" => 5, "/" => 10, "!" => 5, "%" => 21, "*" => 10, "$" => 14, "€" => 14, "#" => 14, "&" => 15,
		"(" => 8, ")" => 8, "[" => 7, "]" => 7, "{" => 9, "}" => 8, "'" => 5, "@" => 23, "=" => 13, "+" => 13, "-" => 8, "£" => 14, "€" => 14,
		"A" => 17, "B" => 15, "C" => 16, "D" => 16, "E" => 15, "F" => 14, "G" => 17, "H" => 16, "I" => 5, "J" => 12, "K" => 16, "L" => 13, "M" => 18,
		"N" => 16, "O" => 19, "P" => 15, "Q" => 19, "R" => 15, "S" => 15, "T" => 15, "U" => 16, "V" => 16, "W" => 23, "X" => 16, "Y" => 18, "Z" => 15,
		"à" => 14, "ç" => 13, "è" => 14, "é" => 14, "ù" => 12, "ê" => 14, "î" => 10, "û" => 12, "ô" => 14, '"' => 8, " " => 5
	);

	$total = 0;

	// For each caracters in word
	for ($i = 0; $i < iconv_strlen($string,'UTF-8'); $i++){
		$total = $total + $carWidth_array[iconv_substr($string, $i, 1,'UTF-8')];
	}

	return $total;
}

/**
 * Return string with carriage return
 *
 * @param [string] $string
 * @return void
 */
function cutString($string){
	// Size of entire sentence
	$phrase_len = getPixelWidth($string);
	// Array of each word size
	$mot_len = array();
	$retstring = '';
	$val = 0;

	$word_array = str_word_count($string, 1, 'éèçàùôîûêâ,?;.:!.');

	// For each word in sentence
	foreach ($word_array as $key => $value) {
		$mot_len[] = getPixelWidth($value);
	}

	foreach ($mot_len as $key => $value) {
		// Word length
		$total = $total + $mot_len[$key];

		if (($phrase_len-$total) >= $total) {
			if ( ($key > 0) ){
				// Where to cut
				$val = ($key-1);

			}
		}
	}

	// For each word in sentence
	foreach ($word_array as $key => $value) {
		if ($key == $val){
			$retstring .= $word_array[$key]."\n";
		}
		else{
			$retstring .= $word_array[$key]." ";
		}
	}

	return trim($retstring);
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
	return (file_exists(FILES_DIR."/subtitle/marker/marker_".$vid.".txt")) ? true : false;
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
				$str= '<track kind="subtitles" src="'.$suburl.'?'.time().'" srclang="fr" label="French" default/>';
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

$cbvid->video_manager_links[]='addPublicSubtitleMaker';
?>