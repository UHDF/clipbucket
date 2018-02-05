<?php
/*
 Plugin Name: Video manager
 Description: Replace the default video manager to be more convenient
 Author: Bastien Poirier
 Author Website: http://semm.univ-lille1.fr/
 ClipBucket Version: 2.8
 Version: 1.0
 Website:
 */

require_once 'video_manager_class.php';

global $cbplugin;
if (!$cbplugin->is_installed('common_library.php'))
	e(sprintf(lang("plugin_not_installed : %s"),"Common Library"));
else
	require_once PLUG_DIR.'/common_library/common_library.php';

/**
 * Define Plugin's uri constants. These constants represents folders or urls
 */
define('SITE_MODE', '/admin_area');
define('VIDM_BASE', basename(dirname(__FILE__)));
define('VIDM_DIR', PLUG_DIR .'/'. VIDM_BASE);
define('VIDM_URL', PLUG_URL .'/'. VIDM_BASE);
define('VIDM_ADMIN_DIR', VIDM_DIR .'/admin');
define('VIDM_ADMIN_URL', VIDM_URL .'/admin');
define('VIDM_MANAGEPAGE_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. VIDM_BASE .'/admin&file=manage.php');
define('VIDM_CREATEPAGE_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. VIDM_BASE .'/admin&file=create_tag.php');
define('VIDM_EDITPAGE_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. VIDM_BASE .'/admin&file=edit.php');

assign('vidm_encoder', $cbplugin->is_installed('video_extensions'));

assign('vidm_admin', VIDM_ADMIN_URL);
assign('vidm_admin_dir', VIDM_ADMIN_DIR);
assign('vidm_pagination', VIDM_ADMIN_DIR. '/pagination.html');
assign('vidm_pagindex', VIDM_ADMIN_DIR. '/pagination-index.html');
assign('vidm_css', VIDM_ADMIN_DIR. '/css.html');

assign('vidm_managepage', VIDM_MANAGEPAGE_URL);
assign('vidm_createpage', VIDM_CREATEPAGE_URL);
assign('vidm_actionpage', VIDM_ACTIONPAGE_URL);

if(!function_exists('vidm_datetime')){
    function vidm_datetime($vid){
        global $vidmquery;
        $video = $vidmquery->getVideo($vid);
        
        if($video){
            return $video['vidmdate'] === null ? $video['datecreated'] : $video['vidmdate'];
        }
    }
    
    $Smarty->register_function('vidm_datetime', 'vidm_datetime');
}

if(!function_exists('vidm_formatNbr')){
    function vidm_formatNbr($num){
        if($num >= 1000) {
            return floor($num / 1000) . 'k';
        }
        return $num;
    }
    
    $Smarty->register_function('vidm_formatNbr', 'vidm_formatNbr');
}

/**Add entries for the plugin in the administration pages */
if($vidmquery->isActivated()){
    global $Cbucket;
    $menu = $Cbucket->AdminMenu;
    //$menu['Videos'] = array(lang('vidm_admMenu') => 'plugin.php?folder='. VIDM_BASE .'/admin&file=manage.php') + $menu['Videos'];
    $menu['Videos'] = array(lang('vidm_admMenu') => $menu['Videos']['Videos Manager']) + $menu['Videos'];
    unset($menu['Videos']['Videos Manager']);
    $Cbucket->AdminMenu = $menu;  
    
    $vidmnotpl = filter_input(INPUT_GET, 'notpl') !== null;
    if($vidmnotpl){
        
    } else {
        if($Cbucket->configs['player_file'] != ''){
            if($Cbucket->configs['player_dir'])
                $folder = '/'. $Cbucket->configs['player_dir'];
            
            $file = PLAYER_DIR . $folder .'/'. $Cbucket->configs['player_file'];
            
            if(file_exists($file))
                include_once($file);
        }
        
        if($_SERVER['PHP_SELF'] === '/admin_area/edit_video.php'){
            $Cbucket->add_admin_header(VIDM_ADMIN_DIR . '/header.html');
        }
        /*switch($_SERVER['PHP_SELF']){
            case '/admin_area/video_manager.php': //http://webtv.local/admin_area/video_manager.php
                //header('Location: '. VIDM_MANAGEPAGE_URL . ($_SERVER['QUERY_STRING'] !== '' ? '&'. $_SERVER['QUERY_STRING'] : ''));
                include_once VIDM_ADMIN_DIR. '/manage.php';
                break;

            case '/admin_area/edit_video.php':
                //header('Location: '. VIDM_EDITPAGE_URL . ($_SERVER['QUERY_STRING'] !== '' ? '&'. $_SERVER['QUERY_STRING'] : ''));
                $Cbucket->add_admin_header(VIDM_ADMIN_DIR . '/header.html');                
                include_once VIDM_ADMIN_DIR. '/edit.php';
                
                break;
        }*/
    }
}


?>