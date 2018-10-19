<?php
/** Check if user has admin acces */
$userquery->admin_login_check();
/** Check if user has admin acces to this plugin */
if ($cbplugin->is_installed('common_library.php'))	$userquery->login_check(getStoredPluginName("liveupjv"));

// Assigning page and subpage
if(!defined('MAIN_PAGE')){
    define('MAIN_PAGE', 'Videos');
}

if(!defined('SUB_PAGE')){
    define('SUB_PAGE', lang('manage_stream'));
}

// Delete
if ( (isset($_GET['del'])) and (!empty($_GET['del'])) ){
	deleteLiveUPJV($_GET['del']);
}

// Insert/Update
if ($_POST){
	saveLiveUPJV($_POST);
}

// get list already plannified
$list = getLiveUPJV();
assign('streamList', $list);

// get already saved .m3u8
$list = getStreamList();
assign('listStream', $list);

// Output
template_files(PLUG_DIR.'/liveupjv/admin/manage_stream.html',true);
