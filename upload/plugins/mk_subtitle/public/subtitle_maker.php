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



Ajout internationnalisation :

En Smarty :
	{lang('ldap_update_config')}
	{lang code="help"}

En Php :
	lang("speaker_already_exists")

*/

// Use the already existing functions
/*
require_once '../includes/config.inc.php';
$userquery->admin_login_check();
$userquery->login_check('admin_access');
$pages->page_redir();
*/





define("THIS_PAGE","edit_video");
define("PARENT_PAGE",'videos');

require '../../../includes/config.inc.php';
$userquery->logincheck();
$pages->page_redir();


$udetails = $userquery->get_user_details(userid());
assign('user',$udetails);
assign('p',$userquery->get_user_profile($udetails['userid']));

$vid = mysql_clean($_GET['vid']);
//get video details
$vdetails = $cbvid->get_video_details($vid);



/*

require_once '../includes/classes/video.class.php';
require_once '../includes/functions_video.php';

*/

// Assigning page and subpage
/*

if(!defined('MAIN_PAGE')){
    define('MAIN_PAGE', 'Videos');
}

if(!defined('SUB_PAGE')){
    define('SUB_PAGE', 'Subtitle maker');
}

*/

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


// assign in order to use in template file
assign('data',$data);


// Basic variable define
//$video = BASEDIR.'/files/videos/FE90C9AC15AE-B3274B-8D85F3-FB8C01E7.mp4';
$marker = BASEDIR.'/files/subtitle/marker/marker_'.$data['videoid'].'.txt';
$marker_meta = BASEDIR.'/files/subtitle/marker/marker_meta_'.$data['videoid'].'.txt';
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

/**
 * Due to change of format from tabulated file to json
 * test to convert the old file
 */
convertMarkerToJson($marker_meta);
/**
*	Check if a silence finder metadata file is associated to the marker video file
*	If it is the case, assign the variable that have been used, else assign default variable
*/
if (file_exists($marker_meta)){
	$data = file($marker_meta, FILE_IGNORE_NEW_LINES);
	$json = json_decode($data[0]);
	assign('threshold', $json->{'threshold'});
	assign('durationSilence', $json->{'durationSilence'});
	assign('delayBefore', $json->{'delayBefore'});
	assign('delayAfter', $json->{'delayAfter'});
	assign('originalLanguage', $json->{'originalLanguage'});

	$originalLanguage = $json->{'originalLanguage'};
	$delayBefore = $json->{'delayBefore'};
	$delayAfter = $json->{'delayAfter'};

	if (
		(isset($originalLanguage)) and
		(!empty($originalLanguage)) and
		(!strstr($subtitle, "_".$originalLanguage.".vtt"))
	){
		$subtitle = str_replace(".vtt", "_".$originalLanguage.".vtt", $subtitle);
	}

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

	$post = array();
	$orgFile = file($marker);
	$postedLine = explode("\n", $_POST['submarker']);
	$data = '';

	foreach ($postedLine as $line){
		$postedArray = explode("\t", $line);
		$post[$postedArray[2]] = $postedArray;
	}

	foreach ($orgFile as $line){
		$original = explode("\t", $line);

		if (isset($post[$original[2]])) {
			$data .= $post[$original[2]][0]."\t".$post[$original[2]][1]."\t".$post[$original[2]][2]."\t".$post[$original[2]][3]."\t".$post[$original[2]][4]."\n";
		}
		else{
			$data .= $original[0]."\t".$original[1]."\t".str_replace("\n", "", $original[2])."\t".$original[3]."\t".str_replace("\n", "", $original[4])."\n";
		}
	}

	updateFile($marker, $data);
}

/**
*	Generate the final file
*/
if ($_POST['subtitlize']){
	makeSubtitleFile($marker, $subtitle);
	assign('tabactive', 'final');
}

/**
*	If editing the final file
*/
if ($_POST['saveSubtitle']){
	updateFile($subtitle, $_POST['subdata']);

	if (isset($_POST['otherSubtitle'])){
		foreach ($_POST['otherSubtitle'] as $key => $value){
			// if default language is defined
			if ($originalLanguage){
				// modify default suffix by the new
				$otherSubtitleUpdate = str_replace($originalLanguage, $key, $subtitle);
			}
			else{
				// else no default language, just add suffix
				$otherSubtitleUpdate = str_replace('.vtt', '_'.$key.'.vtt', $subtitle);
			}

			if (trim($value) != ''){
				updateFile($otherSubtitleUpdate, $value);
			}
		}
	}

}

/**
*	If editing the final file
*/
if ($_POST['deleteSubtitle']){

	if (isset($_POST['lstDelSub'])){
		foreach ($_POST['lstDelSub'] as $key => $value){
			if (trim($value) != ''){
				deleteSubtitleFile($value);
			}
		}
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
			round(($end-$begin), 2),	// 8 : Duration
			$t[4]						// 9 : Subtitle alignement
		);

		if (!empty($t[3])){
			$savedSub++;
		}

	}

	assign('savedSub', $savedSub);
}

assign('marker', $element);



$page = mysql_clean($_GET['page']);
assign('page', $page);

$total_rows  = count($element);
$total_pages = count_pages($total_rows, 99);
$pages->paginate($total_pages, $page);


if (file_exists($subtitle)){
	$subdata = file_get_contents($subtitle);
	assign('subfile', $subdata);
}

/**
 * List all subtitle files of the video
 */
$listSubtitleFile = getSubtitleList($video);
// Unset the default file already save as subdata (assign subfile)
if (($key = array_search($subtitle, $listSubtitleFile)) !== false) {
	unset($listSubtitleFile[$key]);
}

if (!empty($listSubtitleFile)){
	assign('othersubtitle', $listSubtitleFile);
}

assign('defaultsubfile', $subtitle);
assign('langcode', getLangCode());




subtitle(lang("vdo_edit_vdo"));
template_files(PLUG_DIR.'/mk_subtitle/template/subtitle_maker_public.html');
display_it();

