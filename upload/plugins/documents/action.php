<?php

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die('NOT XMLHTTPREQUEST');
}

ini_set('error_reporting', E_ALL);
ini_set("display_errors", 1);

require_once '../../includes/admin_config.php';
require_once 'document_class.php';

$getOpt = filter_input(INPUT_POST, 'getOpt');
if($getOpt !== null){
	$params = array('limit' => '', 'order' => 'title');
	if($getOpt !== '') $params['cond'] = 'id NOT IN ('. $getOpt .')';
    $documents = $documentquery->getDocuments($params);
    $options = array();
    foreach($documents as $d){
        $options[] = array('id' => $d['id'], 'name' => $d['title'], 'filename' => $d['filename'], 'link' => $documentquery->getHref($d['documentkey']));
    }
    echo json_encode($options);
}