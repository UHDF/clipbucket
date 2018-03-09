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

$flashmsg = '';
if(isset($_SESSION['tagm_action'])){
    $flashmsg = $_SESSION['tagm_action'];
    unset($_SESSION['tagm_action']);
}
Assign('tag_flashmsg', $flashmsg);

$tags = null;
$tpl = 'manage_tags.html';
$search = filter_input(INPUT_POST, 'tagm_search');
$tagm_index = '';
if($search){
    $tagm_index = 'search';
    $search = trim($search);
    $tags = $tagmquery->searchTag($search);
    $tpl = 'search_tags.html';
    Assign('tagm_search', $search);
} else {
    $page = mysql_clean($_GET['page']);
    if(isset($_GET['index'])){
        $tagm_index = $_GET['index'] !== '' ? $_GET['index'] : '#';
        $tags = $tagmquery->getTagsByIndex($tagm_index, $page);
        $pages->paginate(count_pages($tagmquery->getTotal(), $tagmquery->getMax()), $page);
    } else {
        $tags = $tagmquery->getTags($page);
        $pages->paginate(count_pages($tagmquery->getTotal(), $tagmquery->getMax()), $page);
    }
}

$pagination = $Smarty->get_template_vars('pagination');
$pagination = preg_replace('#<a>&hellip;<\/a>#', '<li><a>&hellip;</a></li>', $pagination);
Assign('pagination', $pagination);

Assign('tagm_page', $tagm_index);
Assign('tags', $tags);

/** Set HTML title */
subtitle(lang("tagm_subtitle"));

/** Set HTML template */
template_files($tpl, TAGM_ADMIN_DIR);
?>