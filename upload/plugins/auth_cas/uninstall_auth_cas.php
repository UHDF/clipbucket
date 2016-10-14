<?php
require_once('../includes/common.php');

/**
 * Delete database table of CAS configuration.
 *
 */
function uninstallAuthCas()	{
		global $db;
		$db->Execute(
		'DROP TABLE IF EXISTS '.tbl("auth_cas_config").';'
		);
	}


/**
 * Delete language code
 *	@var string $lang Iso country code
 */
function removeAuthCasLangagePack($lang){
	global $db,$lang_obj;
	$folder= PLUG_DIR.'/'.basename(dirname(__FILE__))."/lang";
	$file_name = $folder.'/auth_cas_lang_'.$lang.'.xml';
	// Reading Content
	$content = file_get_contents($file_name);
	if(!$content) {
		e(lang("err_reading_file_content")." : ".$file_name);
	}
	else {
		// Converting data from xml to array
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

uninstallAuthCas();
removeAuthCasLangagePack('fr');
removeAuthCasLangagePack('en');
?>
