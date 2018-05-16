<?php

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die('NOT XMLHTTPREQUEST');
}

ini_set('error_reporting', E_ALL);
ini_set("display_errors", 1);

require_once '../../includes/admin_config.php';
require_once 'speaker_class.php';

$getOpt = filter_input(INPUT_POST, 'getOpt');
if($getOpt !== null){
    $speakers = $speakerquery->getModalSelect($getOpt);
	$options = array();
    foreach($speakers as $s){
        $opt = array('id' => $s['id'], 'name' => $s['firstname'] .' '. $s['lastname']);//, 'role' => $s['role']);
		if($s['role'] !== '') $opt['name'] .= ' <small>'. $s['role'] .'</small>';
		$options[] = $opt;
    }
    echo json_encode($options);
}