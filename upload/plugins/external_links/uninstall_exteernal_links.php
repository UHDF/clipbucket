<?php
require_once('../includes/common.php');

/**____________________________________
 * uninstall_links
 * ____________________________________
 *Remove links table from the database 
 */
function uninstall_links()	{
		global $db;
		$db->Execute(
		'DROP TABLE  IF EXISTS '.tbl("links").''
		);
	}
	
/**____________________________________
 * uninstall_video_links
 * ____________________________________
 *Remove video_links table from the database 
 */
function uninstall_video_links()
{
	global $db;
	$db->Execute(
	'DROP TABLE  IF EXISTS '.tbl("video_links").''
	);
}

/**____________________________________
 * remove_links_langage_pack
 * ____________________________________
 *Remove the plugin language data from the "phrases" database table. Read 
 *inserted keys in the corresponding xml language pack 
 *e
 *input $lang : iso code of the pack to import (ie: 'en')
 */
function remove_links_langage_pack($lang){
	global $db,$lang_obj;
	$folder= PLUG_DIR.'/'.basename(dirname(__FILE__))."/lang";
	$file_name = $folder.'/links_lang_'.$lang.'.xml';
	//Reading Content
	$content = file_get_contents($file_name);
	if(!$content) {
		e(lang("err_reading_file_content")." : ".$file_name);
	}
	else {
		//Converting data from xml to array
		$data = xml2array($content,1,'tag',false);
		$data = $data['clipbucket_language'];
		$phrases = $data['phrases'];
		$iso=$data['iso_code'];
		if(count($phrases)<1) {
			e(lang("no_phrases_found"));
		}
		else if(!$lang_obj->lang_exists($data['iso_code'])) {
			e(lang("language_does_not_exist")." : ".$lang);
		}
		else
		{
			$sql = '';
			foreach($phrases as $code => $phrase) {
				$query = "DELETE FROM ".tbl("phrases")." WHERE lang_iso='".$iso."' AND varname='".$code."'";
				$db->execute($query);
			}
			e(lang("lang_deleted")." : ".$lang,"m");
		}
	}
}

uninstall_video_links();
uninstall_links();
remove_links_langage_pack('fr');
remove_links_langage_pack('en');
?>