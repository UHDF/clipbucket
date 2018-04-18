<?php

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die('NOT XMLHTTPREQUEST');
}

ini_set('error_reporting', E_ALL);
ini_set("display_errors", 1);

require_once '../../includes/admin_config.php';
require_once 'video_grouping_class.php';

$getOpt = filter_input(INPUT_POST, 'getOpt');
$entity = filter_input(INPUT_POST, 'entity');
if($getOpt !== null && in_array($entity, array('type', 'groupe'))){
	$obj = array();
	if($entity === 'groupe'){
		$type = filter_input(INPUT_POST, 'type');
		$obj = $videoGrouping->getModalGrouping($type, $getOpt);
	} elseif($entity === 'type'){
		$ids = explode(',', $getOpt);
		$types = $videoGrouping->getAllGroupingTypes();
		foreach($types as $t){
			$grps = $videoGrouping->getGroupingsOfType($t['id']);
			$continue = false;
			foreach($grps as $g){
				if($continue) continue;
				if(in_array($g['id'], $ids)){
					$continue = true;
					if(count($grps) > 1) $obj[] = $t;
				}
			}
			
			if(!$continue && count($grps)) $obj[] = $t;
		}
	}

	$options = array();
    foreach($obj as $o){
        $opt = array('id' => $o['id'], 'name' => $o['name']);
		$options[] = $opt;
    }
    echo json_encode($options);
}