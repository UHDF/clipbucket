<?php

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die('NOT XMLHTTPREQUEST');
}

ini_set('error_reporting', E_ALL);
ini_set("display_errors", 1);

require_once '../../includes/admin_config.php';
require_once 'speaker_class.php';

$getOpt = filter_input(INPUT_POST, 'getOpt');
$addSpeaker = filter_input(INPUT_POST, 'addSpeaker');
if($getOpt !== null){
    $speakers = $speakerquery->getModalSelect($getOpt);
	$options = array();
    foreach($speakers as $s){
        $opt = array('id' => $s['id'], 'name' => $s['firstname'] .' '. $s['lastname']);//, 'role' => $s['role']);
		if($s['role'] !== '') $opt['name'] .= ' <small>'. $s['role'] .'</small>';
		$options[] = $opt;
    }
    echo json_encode($options);
	exit;
}

if($addSpeaker === '1'){
	$firstname = trim(filter_input(INPUT_POST, 'f'));
	$lastname = trim(filter_input(INPUT_POST, 'l'));
	$role = array(trim(filter_input(INPUT_POST, 'r')));
	//$roles = filter_input(INPUT_POST, 'r', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	
	$res = $speakerquery->addSpeaker(array('firstname' => $firstname, 'lastname' => $lastname, 'description' => $role));
	if($res === false) echo '';
	else{
		$id = $speakerquery->getLastSpeakerRole($res, $role[0]);
		echo $id !== false ? $id : '0';
	}
	exit;
}