<?php
require_once LIVEUDL_DIR.'/live_class.php';
/** Check if user has admin acces */
$userquery->admin_login_check();
/** Check if user has admin acces to this plugin */
if ($cbplugin->is_installed('common_library.php')){
    $userquery->login_check(getStoredPluginName('liveudl'));
    Assign('liveudlOpt', $userquery->permission[getStoredPluginName('liveudlAdmin')] == 'yes');
}

$htaccessAlert = '';
if(!$fp = fopen(LIVEUDL_BASE.'/../../.htaccess', 'r')){
    $htaccessAlert = lang('liveudlHtaccess', '<a href="'. README .'">README</a>');
} else {
    $content = file_get_contents(LIVEUDL_BASE.'/../../.htaccess');
    if(!preg_match('#\s*watch_live.php\?l\=#Usi', $content)){
        $htaccessAlert = lang('liveudlHtaccess', '<a href="'. README .'">README</a>');
    }
}
Assign('htaccessAlert', $htaccessAlert);

$pages->page_redir();

/** Assigning page and subpage */
if(!defined('MAIN_PAGE'))
	define('MAIN_PAGE', lang('Videos'));
if(!defined('SUB_PAGE'))
	define('SUB_PAGE', lang('liveudl_manager'));

/** Prepare page */
$page = mysql_clean($_GET['page']);
$lives = $liveudlquery->getLives($page);
for($i = 0; $i < count($lives); $i++){
    $lives[$i]['description'] = preg_replace('#\\\r\\\n#Usi', chr(13), $lives[$i]['description']);
}
Assign('lives', $lives);

$rtmp = $liveudlquery->getAllRtmp();
Assign('rtmp', $rtmp);

/** Pagination **/
$pages->paginate(count_pages($liveudlquery->getTotal(), $liveudlquery->getMax()), $page);

$flashmsg = '';
if(isset($_SESSION['liveudl_flashmsg'])){
    $flashmsg = $_SESSION['liveudl_flashmsg'];
    unset($_SESSION['liveudl_flashmsg']);
}
Assign('liveudl_flashmsg', $flashmsg);
$flashmsgerr = '';
if(isset($_SESSION['liveudl_flashmsgerr'])){
    $flashmsgerr = $_SESSION['liveudl_flashmsgerr'];
    unset($_SESSION['liveudl_flashmsgerr']);
}
Assign('liveudl_flashmsgerr', $flashmsgerr);

/** Set HTML title */
subtitle(lang('liveudl_manager'));

/** Set HTML template */
template_files('manage_live.html', LIVEUDL_ADMIN_DIR);
?>