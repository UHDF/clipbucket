<?php
require_once '../../../includes/admin_config.php';
require_once TAGSELECTOR_DIR.'/tag_selector_class.php';

/** Check if user has admin acces */
$userquery->admin_login_check();
/** Check if user has admin acces to this plugin */
if ($cbplugin->is_installed('common_library.php'))	$userquery->login_check(getStoredPluginName("tag_selector"));
$pages->page_redir();



global $tagSeletor;
$video=$_POST["data"]["video"];
Assign("alltags", json_encode($tagSelector->getAllTags()));
Assign("initialtags", json_encode($tagSelector->getTags($_GET['video'])));
$output=json_encode(["alltags"=>$tagSelector->getAllTags(), "initialtags"=>$tagSelector->getTags($video)]);

echo $output;