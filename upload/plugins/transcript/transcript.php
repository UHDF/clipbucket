<?php
/*
Plugin Name: Transcript
Description: Displays the subtitle downside of the video
Author: Adrien Ponchelet
Author Website: https://www.u-picardie.fr
ClipBucket Version: 4
Version: 0.1
*/


function notATimecode($string){

	$string = trim(str_replace("\n", "", str_replace("\r", "", $string)));
	$end = array(".", "?", "!");

	if (
		(!preg_match( "/-->/", $string))
		and ($string != '')
		and (!preg_match( "/NOTE:/", $string))
		and (!preg_match( "/NOTE :/", $string))
		and (!preg_match( "/WEBVTT/", $string))
	){
		if (in_array(substr($string, -1, 1), $end)){
			echo $string.'<br>';
		}
		else{
			echo $string.' ';
		}
	}
}

/*
	* This Function generate anchors for  subtitle vtt file if exist
	*/
function displayTranscript($data = ''){

	if ($data['videoid']){
		if (file_exists(FILES_DIR."/subtitle/subtitle_".$data['videoid'].".vtt")){
			$subfile = BASEDIR.'/files/subtitle/subtitle_'.$data['videoid'].'.vtt';
			$lines = file($subfile);

			echo '<h2>Transcription :</h2>';
			array_filter($lines, "notATimecode");
			echo '<hr>';
		}
	}

}
// use {ANCHOR place="displayTranscript" data=$vdata} to add the HTML string into the file.
register_anchor_function('displayTranscript','displayTranscript');

?>