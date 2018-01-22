<?php

	/**
	* File: watch_live
	* Description: File used to display watch live page
	* @author: Bastien Poirier
	* @since: 2018
	* @website: univ-lille.fr
	*/

	define("THIS_PAGE",'watch_live');
	define("PARENT_PAGE",'videos');
        
	require_once 'includes/config.inc.php';
	require_once 'plugins/liveudl/live.php';
	global $cbvid;
        
	//$userquery->perm_check('view_live',true);
	$pages->page_redir();

	$lid = intval(filter_input(INPUT_GET, 'l'));
        
        if(!$lid){ // Affichage de tous les lives visibles
            $lives = $liveudlquery->getLivesFront();
            Assign('lives', $lives);
            
            if(file_exists(LAYOUT .'/lives.html')){
                template_files('lives.html');
            } else {
                template_files('lives.html', LIVEUDL_FRONT_DIR);
            }
            
            display_it();
            return;
        }
        
        $live = $liveudlquery->getLiveRtmp($lid);
        if($live['visible']){
            Assign('live', $live);
        } else {
            $Cbucket->show_page = false;
            e(lang('liveudl_liveNotExist'));
        }
        
        
        if(file_exists(LAYOUT .'/lives.html')){
            template_files('watch_live.html');
        } else {
            template_files('watch_live.html', LIVEUDL_FRONT_DIR);
        }
        
	display_it();
?> 