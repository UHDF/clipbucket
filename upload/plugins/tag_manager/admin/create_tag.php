<?php
require_once TAGM_DIR.'/tag_manager_class.php';

$userquery->admin_login_check();
if ($cbplugin->is_installed('common_library.php'))
    $userquery->login_check(getStoredPluginName('tag_manager'));

$pages->page_redir();

/** Assigning page and subpage */
if(!defined('MAIN_PAGE'))
	define('MAIN_PAGE', lang('video_addon'));
if(!defined('SUB_PAGE'))
	define('SUB_PAGE', lang('tagm_subtitle'));

$tags = $tagmquery->getTags();
Assign('tags', $tags);

$videos = $tagmquery->getVideos();
Assign('videos', $videos);

/** Set HTML title */
subtitle(lang('tagm_titleNew'));

/** Set HTML template */
template_files('create_tag.html', TAGM_ADMIN_DIR);
?>