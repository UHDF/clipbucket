<?php
/*

Régle de sous titrage :
-----------------------

- La police utilisée est Helvetica 28.
- les caractères sont en jaune.
- Un maximum de 36 caractères par ligne (incluant les espaces), mais cela dépend de la largeur des lettres : un W compte pour 1 caractère, mais A, B ou C compte pour 0,8 par exemple, un f ou un i compte pour 0,3.
- Deux rangées de sous-titres maximum (si possible)
- Caractères autorisés (pour une diffusion en France) : ! " % & ' ( ) * + , - ; / : > < = ? en plus des lettres et chiffres classiques.
- Sous-titrage du titre du film : 4 secondes minimum.
- Durée minimale d'un sous-titre : 1 seconde, durée maximale 10 secondes.
- 5 images minimum entre deux sous-titres.
- On évite qu'un sous-titre chevauche un changement de plan.
- Un sous-titre disparait au minimum 4 images avant un changement de plan, et il apparait minimum 4 images après le changement de plan.
- Si besoin, un sous-titre peut cependant chevaucher un changement de plan, à la condition qu'il apparaisse ou disparaisse au moins 1 seconde avant ou après ce changement de plan.

*/

// Use the already existing functions
require_once '../includes/admin_config.php';
$userquery->admin_login_check();
$userquery->login_check('admin_access');
$pages->page_redir();

require_once '../includes/classes/video.class.php';
require_once '../includes/functions_video.php';
	
// Assigning page and subpage
if(!defined('MAIN_PAGE')){
    define('MAIN_PAGE', 'Videos');
}

if(!defined('SUB_PAGE')){
    define('SUB_PAGE', 'Subtitle maker');
}

// Require function file
require(PLUG_DIR."/".SUBTITLE_MAKER_BASE."/functions.php");

// Get th id of video
$video = mysql_clean($_GET['video']);

// Get the details of video
$data = get_video_details($video);

// Get list of URL for each files (sd, hd)
$lst_vid = get_video_files($data);
// video file
if (is_array($lst_vid)){
	$video_file = $lst_vid[0];
	$video_file = str_replace(BASEURL, BASEDIR, $video_file);
}
else{
	// video URL
	$video_file = $data["remote_play_url"];
}


// Get the details of video
//$data = get_video_details($video);

// assign in order to use in template file
assign('data',$data);


// Basic variable define
//$video = BASEDIR.'/files/videos/FE90C9AC15AE-B3274B-8D85F3-FB8C01E7.mp4';
$marker = BASEDIR.'/files/marker/marker_'.$data['videoid'].'.txt';
$marker_meta = BASEDIR.'/files/marker/marker_meta_'.$data['videoid'].'.txt';
$subtitle = BASEDIR.'/files/subtitle/subtitle_'.$data['videoid'].'.vtt';
$subtitle_draft = BASEDIR.'/files/subtitle/subtitle_draft_'.$data['videoid'].'.vtt';

// ffmpeg path
$ffmpeg_path = $GLOBALS['Cbucket']->configs['ffmpegpath'];	
assign('ffmpeg_path', $ffmpeg_path);
	

// Assign for template
assign('video_file', $video_file);
assign('marker_file', $marker);
assign('savedSub', 0);
assign('nbMarker', 0);

// Number of caracter by line for one subtitle
$nbcar_by_line = 70;

/**
*	Check if a silence finder metadata file is associated to the marker video file
*	If it is the case, assign the variable that have been used, else assign default variable
*/
if (file_exists($marker_meta)){
	$lines = file($marker_meta, FILE_IGNORE_NEW_LINES);
	$t = explode("\t", $lines[0]);
	assign('threshold', $t[0]);
	assign('durationSilence', $t[1]);
	assign('delayBefore', $t[2]);
	assign('delayAfter', $t[3]);

	$delayBefore = $t[2];
	$delayAfter = $t[3];
}
else{
	assign('threshold', '-26');
	assign('durationSilence', '0.2');
	assign('delayBefore', '0.200');
	assign('delayAfter', '0.200');

	$delayBefore = 0.200;
	$delayAfter = 0.200;
}


/**
*	Update the marker file
*/
if ($_POST['saveMarker']){

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
*	Generate the final file
*/
if ($_POST['subtitlize']){

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
*	If editing the final file
*/
if ($_POST['saveSubtitle']){

	if (file_exists($subtitle)){

		$fp = fopen($subtitle, "w+");			// Open the draft file
		fwrite($fp, $_POST['subdata']);
		fclose($fp);
	}

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

	
if (file_exists($subtitle)){
	$subdata = file_get_contents($subtitle);
	assign('subfile', $subdata);
}


// Output
template_files(PLUG_DIR.'/mk_subtitle/admin/subtitle_maker.html',true);
