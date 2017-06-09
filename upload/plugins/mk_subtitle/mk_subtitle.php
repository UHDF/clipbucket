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

	return '<li><a role="menuitem" href="'.SUBTITLE_MAKER_URL.'&video='.$idtmp.'">Subtitle Maker</a></li>';
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
 * Delete the subtitle file
 *
 * @param string $subtitle Path to the subtitle file
 */
function deleteSubtitleFile($subtitle){

	unlink($subtitle);

}

/*
echo '<pre>';
print_r($video);
echo '</pre>';
*/

if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("mk_subtitle")]=='yes'){
	$cbvid->video_manager_link[]='addSubtitleMaker';

/*
	echo '<pre>';
		var_dump($cbvid);
	echo '</pre>';	
	*/
}

?>