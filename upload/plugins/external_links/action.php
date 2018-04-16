<?php

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die('NOT XMLHTTPREQUEST');
}

ini_set('error_reporting', E_ALL);
ini_set("display_errors", 1);

require_once '../../includes/admin_config.php';
require_once 'link_class.php';

$getOpt = filter_input(INPUT_POST, 'getOpt');
if($getOpt !== null){
    $links = $linkquery->getModalSelect($getOpt);
	$options = array();
    foreach($links as $l){
        $opt = array('id' => $l['id'], 'name' => $l['title'], 'href' => $l['url']);
		$options[] = $opt;
    }
    echo json_encode($options);
}