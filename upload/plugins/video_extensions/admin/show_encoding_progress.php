<?php
require_once '../../../includes/admin_config.php';
require_once VIDEO_EXTENSIONS_DIR.'/video_extensions_class.php';

/** Check if user has admin acces */
$userquery->admin_login_check();
/** Check if user has admin acces to this plugin */
if ($cbplugin->is_installed('common_library.php'))	$userquery->login_check(getStoredPluginName("video_extensions"));
$pages->page_redir();

global $videoExtension;
$video=$_POST["data"]["video"];
Assign('video', $video);

$output=$videoExtension->generateHTMLEncoding($video);
echo $output;