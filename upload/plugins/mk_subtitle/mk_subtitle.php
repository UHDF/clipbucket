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
define("mksubtitle_install","installed");


/**
 * Test if database exist, create and populate if not
 */
testTableLangCodeExist();





/**
 * Add a new entry "Subtitle maker" into the video manager menu named "Actions" associated to each video
 *
 * @param int $vid
 * 		the video id
 * @return string
 * 		the html string to be inserted into the menu
 */
function addSubtitleMaker($vid){
	$idtmp = $vid['videoid'];

	return '<li><a role="menuitem" href="'.SUBTITLE_MAKER_URL.'&video='.$idtmp.'">'.lang('mksub_title').'</a></li>';
}

/**
 *	Test for public subtitlize
 */
function addPublicSubtitleMaker($vid){
	$idtmp = $vid['videoid'];

	return '<li><a role="menuitem" href="'.BASEURL.'/plugins/'.SUBTITLE_MAKER_BASE.'/public/subtitle_maker.php?video='.$idtmp.'">'.lang('mksub_title').'</a></li>';
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
 * Update the file
 *
 * @param string $file Path to the file
 *
 * @param string $data What to write
 *
 */
function updateFile($file, $data){

	// if (file_exists($file)){
		$fp = fopen($file, "w+");			// Open the draft file
		fwrite($fp, $data);
		fclose($fp);
	// }

}


/**
 * convert old marker_meta format to JSON
 *
 * @param [type] $marker_meta
 * @return void
 */
function convertMarkerToJson($marker_meta){

	$data = file($marker_meta, FILE_IGNORE_NEW_LINES);
	$oldFormat = explode("\t", $data[0]);

	if (count($oldFormat) > 2){
		$jsonmeta = array(
			"threshold" => $oldFormat[0],
			"durationSilence" => $oldFormat[1],
			"delayBefore" => $oldFormat[2],
			"delayAfter" => $oldFormat[3],
			"originalLanguage" => ""
		);
		$jsonmeta = json_encode($jsonmeta);

		updateFile($marker_meta, $jsonmeta);
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
	return (file_exists(getDefaultSubtitle($vid))) ? true : false;
}

/**
 * Delete the subtitle file
 *
 * @param string $subtitle Path to the subtitle file
 */
function deleteSubtitleFile($subtitle){

	unlink($subtitle);

}

function getSubtitleList($vid){
	$tmparray = array();
	$listsubtitle = scandir(BASEDIR."/files/subtitle");

	foreach ($listsubtitle as $key => $value) {
		// echo $key.' '.$value.'<br>';

		if (strstr($value, "_".$vid."_")){
			$tmparray[] = BASEDIR."/files/subtitle/".$value;
		}
	}
	return $tmparray;
}

function getSubtitleContent($filepath){
	if (file_exists($filepath)){
		$subdata = file_get_contents($filepath);
	}

	return $subdata;
}

function getSubtitleLanguage($filepath, $vid){
	$filepath = str_replace("subtitle_".$vid."_", "", basename($filepath, ".vtt"));

	return $filepath;
}

function getLangCode(){
	global $db;
	$arr = array();

	$langcode = $db->_select('SELECT * FROM '.tbl("langcode").' ORDER BY frenchname;');

	for ($i = 0; $i < count($langcode); $i++) {
		$arr[$langcode[$i]['langcode']] = $langcode[$i]['frenchname'];
	}

	return $arr;
}

function getLangLabelByCode($code){
	global $db;
	$langcode = $db->_select('SELECT * FROM '.tbl("langcode").' WHERE langcode = "'.$code.'";');

	if (count($langcode) > 0) {
		return $langcode[0]['frenchname'];
	}
	else{
		return 'undefined';
	}
}


function getDefaultSubtitle($vid){

	$marker_meta = BASEDIR.'/files/subtitle/marker/marker_meta_'.$vid.'.txt';
	$subtitle = BASEDIR.'/files/subtitle/subtitle_'.$vid.'.vtt';

	$data = file($marker_meta, FILE_IGNORE_NEW_LINES);
	$json = json_decode($data[0]);

	$originalLanguage = $json->{'originalLanguage'};

	if ( (isset($originalLanguage)) and (!strstr($subtitle, "_".$originalLanguage.".vtt")) ){
		$subtitle = str_replace(".vtt", "_".$originalLanguage.".vtt", $subtitle);
	}
	return $subtitle;
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
			$str = "";

			if (file_exists($subfile)){
				$str = '<track kind="subtitles" src="'.$suburl.'?'.time().'" srclang="fr" label="French" default/>';
			}

			$listSubtitleFile = getSubtitleList($data['videoid']);
			$default = getDefaultSubtitle($data['videoid']);

			if (($key = array_search($subfile, $listSubtitleFile)) !== false) {
				unset($listSubtitleFile[$key]);
			}

			if (!empty($listSubtitleFile)){

				for ($i = 0; $i < count($listSubtitleFile); $i++){
					$code = getSubtitleLanguage($listSubtitleFile[$i], $data['videoid']);
					$label = getLangLabelByCode($code);

					$str .= '<track kind="subtitles" src="'.BASEURL.'/files/subtitle/'.basename($listSubtitleFile[$i]).'?'.time().'" srclang="'.$code.'" label="'.$label.'"'.( ($default == $listSubtitleFile[$i]) ? ' default/>' : '>');;
				}
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



/**
 * Create and populate the database if not exist
 * this function is used because adding the multilingual
 * use a database and to not have to reinstall the
 * plugin.
 *
 * @return void
 */
function testTableLangCodeExist(){

	global $db;
	$langcode = $db->_select("SHOW TABLES LIKE 'langcode';");

	if (count($langcode) <= 0) {
		$langcode = $db->execute("CREATE TABLE `langcode` (`id` int(11) NOT NULL, `langcode` varchar(5) NOT NULL, `langcode2` varchar(5) NOT NULL, `langcode3` varchar(5) NOT NULL, `frenchname` varchar(255) NOT NULL, `originname` varchar(255) NOT NULL, `englishname` varchar(255) NOT NULL, `comment` varchar(255) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Liste des codes ISO 639-1';");

		$langcode = $db->execute("INSERT INTO `langcode` (`id`, `langcode`, `langcode2`, `langcode3`, `frenchname`, `originname`, `englishname`, `comment`) VALUES (1, 'aa', 'aar', 'aar', 'Afar', '', 'Afar', ''), (2, 'ab', 'abk', 'abk', 'Abkhaze', '', 'Abkhazian', ''), (3, 'ae', 'ave', 'ave', 'Avestique', '', 'Avestan', ''), (4, 'af', 'afr', 'afr', 'Afrikaans', '', 'Afrikaans', ''), (5, 'ak', 'aka', 'aka +', 'Akan', '', 'Akan', ''), (6, 'am', 'amh', 'amh', 'Amharique', '', 'Amharic', ''), (7, 'an', 'arg', 'arg', 'Aragonais', '', 'Aragonese', ''), (8, 'ar', 'ara', 'ara +', 'Arabe', '', 'Arabic', ''), (9, 'as', 'asm', 'asm', 'Assamais', '', 'Assamese', ''), (10, 'av', 'ava', 'ava', 'Avar', '', 'Avaric', ''), (11, 'ay', 'aym', 'aym +', 'Aymara', '', 'Aymara', ''), (12, 'az', 'aze', 'aze +', 'Azéri', '', 'Azerbaijani', ''), (13, 'ba', 'bak', 'bak', 'Bachkir', '', 'Bashkir', ''), (14, 'be', 'bel', 'bel', 'Biélorusse', '', 'Belarusian', ''), (15, 'bg', 'bul', 'bul', 'Bulgare', '', 'Bulgarian', ''), (16, 'bh', 'bih', '--', 'Bihari', '', 'Bihari', ''), (17, 'bi', 'bis', 'bis', 'Bichelamar', '', 'Bislama', ''), (18, 'bm', 'bam', 'bam', 'Bambara', '', 'Bambara', ''), (19, 'bn', 'ben', 'ben', 'Bengali', '', 'Bengali', ''), (20, 'bo', 'tib/b', 'bod', 'Tibétain', '', 'Tibetan', ''), (21, 'br', 'bre', 'bre', 'Breton', '', 'Breton', ''), (22, 'bs', 'bos', 'bos', 'Bosnien', '', 'Bosnian', ''), (23, 'ca', 'cat', 'cat', 'Catalan', '', 'Catalan', ''), (24, 'ce', 'che', 'che', 'Tchétchène', '', 'Chechen', ''), (25, 'ch', 'cha', 'cha', 'Chamorro', '', 'Chamorro', ''), (26, 'co', 'cos', 'cos', 'Corse', '', 'Corsican', ''), (27, 'cr', 'cre', 'cre +', 'Cri', '', 'Cree', ''), (28, 'cs', 'cze/c', 'ces', 'Tchèque', '', 'Czech', ''), (29, 'cu', 'chu', 'chu', 'Vieux-slave', '', 'Old Church Slavonic', ''), (30, 'cv', 'chv', 'chv', 'Tchouvache', '', 'Chuvash', ''), (31, 'cy', 'wel/c', 'cym', 'Gallois', '', 'Welsh', ''), (32, 'da', 'dan', 'dan', 'Danois', '', 'Danish', ''), (33, 'de', 'ger/d', 'deu', 'Allemand', '', 'German', ''), (34, 'dv', 'div', 'div', 'Maldivien', '', 'Divehi', ''), (35, 'dz', 'dzo', 'dzo', 'Dzongkha', '', 'Dzongkha', ''), (36, 'ee', 'ewe', 'ewe', 'Ewe', '', 'Ewe', ''), (37, 'el', 'gre/e', 'ell', 'Grec moderne', '', 'Greek', ''), (38, 'en', 'eng', 'eng', 'Anglais', '', 'English', ''), (39, 'eo', 'epo', 'epo', 'Espéranto', '', 'Esperanto', ''), (40, 'es', 'spa', 'spa', 'Espagnol', '', 'Spanish', ''), (41, 'et', 'est', 'est', 'Estonien', '', 'Estonian', ''), (42, 'eu', 'baq/e', 'eus', 'Basque', '', 'Basque', ''), (43, 'fa', 'per/f', 'fas +', 'Persan', '', 'Persian', ''), (44, 'ff', 'ful', 'ful +', 'Peul', '', 'Fulah', ''), (45, 'fi', 'fin', 'fin', 'Finnois', '', 'Finnish', ''), (46, 'fj', 'fij', 'fij', 'Fidjien', '', 'Fijian', ''), (47, 'fo', 'fao', 'fao', 'Féroïen', '', 'Faroese', ''), (48, 'fr', 'fre/f', 'fra', 'Français', '', 'French', ''), (49, 'fy', 'fry', 'fry +', 'Frison occidental', '', 'Western Frisian', ''), (50, 'ga', 'gle', 'gle', 'Irlandais', '', 'Irish', ''), (51, 'gd', 'gla', 'gla', 'Écossais', '', 'Scottish Gaelic', ''), (52, 'gl', 'glg', 'glg', 'Galicien', '', 'Galician', ''), (53, 'gn', 'grn', 'grn +', 'Guarani', '', 'Guarani', ''), (54, 'gu', 'guj', 'guj', 'Gujarati', '', 'Gujarati', ''), (55, 'gv', 'glv', 'glv', 'Mannois', '', 'Manx', ''), (56, 'ha', 'hau', 'hau', 'Haoussa', '', 'Hausa', ''), (57, 'he', 'heb', 'heb', 'Hébreu', '', 'Hebrew', ''), (58, 'hi', 'hin', 'hin', 'Hindi', '', 'Hindi', ''), (59, 'ho', 'hmo', 'hmo', 'Hiri motu', '', 'Hiri Motu', ''), (60, 'hr', 'scr/h', 'hrv', 'Croate', '', 'Croatian', ''), (61, 'ht', 'hat', 'hat', 'Créole haïtien', '', 'Haitian', ''), (62, 'hu', 'hun', 'hun', 'Hongrois', '', 'Hungarian', ''), (63, 'hy', 'arm/h', 'hye', 'Arménien', '', 'Armenian', ''), (64, 'hz', 'her', 'her', 'Héréro', '', 'Herero', ''), (65, 'ia', 'ina', 'ina', 'Interlingua', '', 'Interlingua', ''), (66, 'id', 'ind', 'ind', 'Indonésien', '', 'Indonesian', ''), (67, 'ie', 'ile', 'ile', 'Occidental', '', 'Interlingue', ''), (68, 'ig', 'ibo', 'ibo', 'Igbo', '', 'Igbo', ''), (69, 'ii', 'iii', 'iii', 'Yi', '', 'Sichuan Yi', ''), (70, 'ik', 'ipk', 'ipk +', 'Inupiak', '', 'Inupiaq', ''), (71, 'io', 'ido', 'ido', 'Ido', '', 'Ido', ''), (72, 'is', 'ice/i', 'isl', 'Islandais', '', 'Icelandic', ''), (73, 'it', 'ita', 'ita', 'Italien', '', 'Italian', ''), (74, 'iu', 'iku', 'iku +', 'Inuktitut', '', 'Inuktitut', ''), (75, 'ja', 'jpn', 'jpn', 'Japonais', '', 'Japanese', ''), (76, 'jv', 'jav', 'jav', 'Javanais', '', 'Javanese', ''), (77, 'ka', 'geo/k', 'kat', 'Géorgien', '', 'Georgian', ''), (78, 'kg', 'kon', 'kon +', 'Kikongo', '', 'Kongo', ''), (79, 'ki', 'kik', 'kik', 'Kikuyu', '', 'Kikuyu', ''), (80, 'kj', 'kua', 'kua', 'Kuanyama', '', 'Kwanyama', ''), (81, 'kk', 'kaz', 'kaz', 'Kazakh', '', 'Kazakh', ''), (82, 'kl', 'kal', 'kal', 'Groenlandais', '', 'Kalaallisut', ''), (83, 'km', 'khm', 'khm', 'Khmer', '', 'Khmer', ''), (84, 'kn', 'kan', 'kan', 'Kannada', '', 'Kannada', ''), (85, 'ko', 'kor', 'kor', 'Coréen', '', 'Korean', ''), (86, 'kr', 'kau', 'kau +', 'Kanouri', '', 'Kanuri', ''), (87, 'ks', 'kas', 'kas', 'Cachemiri', '', 'Kashmiri', ''), (88, 'ku', 'kur', 'kur +', 'Kurde', '', 'Kurdish', ''), (89, 'kv', 'kom', 'kom +', 'Komi', '', 'Komi', ''), (90, 'kw', 'cor', 'cor', 'Cornique', '', 'Cornish', ''), (91, 'ky', 'kir', 'kir', 'Kirghiz', '', 'Kirghiz', ''), (92, 'la', 'lat', 'lat', 'Latin', '', 'Latin', ''), (93, 'lb', 'ltz', 'ltz', 'Luxembourgeois', '', 'Luxembourgish', ''), (94, 'lg', 'lug', 'lug', 'Ganda', '', 'Ganda', ''), (95, 'li', 'lim', 'lim', 'Limbourgeois', '', 'Limburgish', ''), (96, 'ln', 'lin', 'lin', 'Lingala', '', 'Lingala', ''), (97, 'lo', 'lao', 'lao', 'Lao', '', 'Lao', ''), (98, 'lt', 'lit', 'lit', 'Lituanien', '', 'Lithuanian', ''), (99, 'lu', 'lub', 'lub', 'Luba-katanga', '', 'Luba-Katanga', ''), (100, 'lv', 'lav', 'lav', 'Letton', '', 'Latvian', ''), (101, 'mg', 'mlg', 'mlg +', 'Malgache', '', 'Malagasy', ''), (102, 'mh', 'mah', 'mah', 'Marshallais', '', 'Marshallese', ''), (103, 'mi', 'mao/m', 'mri', 'Maori de Nouvelle-Zélande', '', 'Māori', ''), (104, 'mk', 'mac/m', 'mkd', 'Macédonien', '', 'Macedonian', ''), (105, 'ml', 'mal', 'mal', 'Malayalam', '', 'Malayalam', ''), (106, 'mn', 'mon', 'mon +', 'Mongol', '', 'Mongolian', ''), (107, 'mo', 'mol', 'mol', 'Moldave', '', 'Moldavian', ''), (108, 'mr', 'mar', 'mar', 'Marathi', '', 'Marathi', ''), (109, 'ms', 'may/m', 'msa +', 'Malais', '', 'Malay', ''), (110, 'mt', 'mlt', 'mlt', 'Maltais', '', 'Maltese', ''), (111, 'my', 'bur/m', 'mya', 'Birman', '', 'Burmese', ''), (112, 'na', 'nau', 'nau', 'Nauruan', '', 'Nauru', ''), (113, 'nb', 'nob', 'nob', 'Norvégien Bokmål', '', 'Norwegian Bokmål', ''), (114, 'nd', 'nde', 'nde', 'Sindebele', '', 'North Ndebele', ''), (115, 'ne', 'nep', 'nep', 'Népalais', '', 'Nepali', ''), (116, 'ng', 'ndo', 'ndo', 'Ndonga', '', 'Ndonga', ''), (117, 'nl', 'dut/n', 'nld', 'Néerlandais', '', 'Dutch', ''), (118, 'nn', 'nno', 'nno', 'Norvégien Nynorsk', '', 'Norwegian Nynorsk', ''), (119, 'no', 'nor', 'nor +', 'Norvégien', '', 'Norwegian', ''), (120, 'nr', 'nbl', 'nbl', 'Nrebele', '', 'South Ndebele', ''), (121, 'nv', 'nav', 'nav', 'Navajo', '', 'Navajo', ''), (122, 'ny', 'nya', 'nya', 'Chichewa', '', 'Chichewa', ''), (123, 'oc', 'oci', 'oci +', 'Occitan', '', 'Occitan', ''), (124, 'oj', 'oji', 'oji +', 'Ojibwé', '', 'Ojibwa', ''), (125, 'om', 'orm', 'orm +', 'Oromo', '', 'Oromo', ''), (126, 'or', 'ori', 'ori', 'Oriya', '', 'Oriya', ''), (127, 'os', 'oss', 'oss', 'Ossète', '', 'Ossetian', ''), (128, 'pa', 'pan', 'pan', 'Pendjabi', '', 'Panjabi', ''), (129, 'pi', 'pli', 'pli', 'Pali', '', 'Pāli ', ''), (130, 'pl', 'pol', 'pol', 'Polonais', '', 'Polish', ''), (131, 'ps', 'pus', 'pus +', 'Pachto', '', 'Pashto', ''), (132, 'pt', 'por', 'por', 'Portugais', '', 'Portuguese', ''), (133, 'qu', 'que', 'que +', 'Quechua', '', 'Quechua', ''), (134, 'rc ', 'rcf ', 'rcf ', 'Créole Réunionnais ', '', 'Reunionese ', ''), (135, 'rm', 'roh', 'roh', 'Romanche', '', 'Romansh', ''), (136, 'rn', 'run', 'run', 'Kirundi', '', 'Kirundi', ''), (137, 'ro', 'rum/r', 'ron', 'Roumain', '', 'Romanian', ''), (138, 'ru', 'rus', 'rus', 'Russe', '', 'Russian', ''), (139, 'rw', 'kin', 'kin', 'Kinyarwanda', '', 'Kinyarwanda', ''), (140, 'sa', 'san', 'san', 'Sanskrit', '', 'Sanskrit', ''), (141, 'sc', 'srd', 'srd +', 'Sarde', '', 'Sardinian', ''), (142, 'sd', 'snd', 'snd', 'Sindhi', '', 'Sindhi', ''), (143, 'se', 'sme', 'sme', 'Same du Nord', '', 'Northern Sami', ''), (144, 'sg', 'sag', 'sag', 'Sango', '', 'Sango', ''), (145, 'sh', '', 'hbs ', 'Serbo-croate', '', 'Serbo-Croatian ', ''), (146, 'si', 'sin', 'sin', 'Cingalais', '', 'Sinhalese', ''), (147, 'sk', 'slo/s', 'slk', 'Slovaque', '', 'Slovak', ''), (148, 'sl', 'slv', 'slv', 'Slovène', '', 'Slovenian', ''), (149, 'sm', 'smo', 'smo', 'Samoan', '', 'Samoan', ''), (150, 'sn', 'sna', 'sna', 'Shona', '', 'Shona', ''), (151, 'so', 'som', 'som', 'Somali', '', 'Somali', ''), (152, 'sq', 'alb/s', 'sqi +', 'Albanais', '', 'Albanian', ''), (153, 'sr', 'scc/s', 'srp', 'Serbe', '', 'Serbian', ''), (154, 'ss', 'ssw', 'ssw', 'Swati', '', 'Swati', ''), (155, 'st', 'sot', 'sot', 'Sotho du Sud', '', 'Sotho', ''), (156, 'su', 'sun', 'sun', 'Soundanais', '', 'Sundanese', ''), (157, 'sv', 'swe', 'swe', 'Suédois', '', 'Swedish', ''), (158, 'sw', 'swa', 'swa +', 'Swahili', '', 'Swahili', ''), (159, 'ta', 'tam', 'tam', 'Tamoul', '', 'Tamil', ''), (160, 'te', 'tel', 'tel', 'Télougou', '', 'Telugu', ''), (161, 'tg', 'tgk', 'tgk', 'Tadjik', '', 'Tajik', ''), (162, 'th', 'tha', 'tha', 'Thaï', '', 'Thai', ''), (163, 'ti', 'tir', 'tir', 'Tigrigna', '', 'Tigrinya', ''), (164, 'tk', 'tuk', 'tuk', 'Turkmène', '', 'Turkmen', ''), (165, 'tl', 'tgl', 'tgl', 'Tagalog', '', 'Tagalog', ''), (166, 'tn', 'tsn', 'tsn', 'Tswana', '', 'Tswana', ''), (167, 'to', 'ton', 'ton', 'Tongien', '', 'Tonga', ''), (168, 'tr', 'tur', 'tur', 'Turc', '', 'Turkish', ''), (169, 'ts', 'tso', 'tso', 'Tsonga', '', 'Tsonga', ''), (170, 'tt', 'tat', 'tat', 'Tatar', '', 'Tatar', ''), (171, 'tw', 'twi', 'twi', 'Twi', '', 'Twi', ''), (172, 'ty', 'tah', 'tah', 'Tahitien', '', 'Tahitian', ''), (173, 'ug', 'uig', 'uig', 'Ouïghour', '', 'Uighur', ''), (174, 'uk', 'ukr', 'ukr', 'Ukrainien', '', 'Ukrainian', ''), (175, 'ur', 'urd', 'urd', 'Ourdou', '', 'Urdu', ''), (176, 'uz', 'uzb', 'uzb +', 'Ouzbek', '', 'Uzbek', ''), (177, 've', 'ven', 'ven', 'Venda', '', 'Venda', ''), (178, 'vi', 'vie', 'vie', 'Vietnamien', '', 'Viêt Namese', ''), (179, 'vo', 'vol', 'vol', 'Volapük', '', 'Volapük', ''), (180, 'wa', 'wln', 'wln', 'Wallon', '', 'Walloon', ''), (181, 'wo', 'wol', 'wol', 'Wolof', '', 'Wolof', ''), (182, 'xh', 'xho', 'xho', 'Xhosa', '', 'Xhosa', ''), (183, 'yi', 'yid', 'yid +', 'Yiddish', '', 'Yiddish', ''), (184, 'yo', 'yor', 'yor', 'Yoruba', '', 'Yoruba', ''), (185, 'za', 'zha', 'zha +', 'Zhuang', '', 'Zhuang', ''), (186, 'zh', 'chi/z', 'zho +', 'Chinois', '', 'Chinese', ''), (187, 'zu', 'zul', 'zul', 'Zoulou', '', 'Zulu', '');");

	}

}

?>