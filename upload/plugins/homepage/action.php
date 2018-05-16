<?php

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die('NOT XMLHTTPREQUEST');
}

require_once '../../includes/admin_config.php';
require_once 'homepage_class.php';

$hp = intval(filter_input(INPUT_POST, 'hp'));

if($hp){
	$homepagequery->delete($hp);
}

$getOpt = filter_input(INPUT_POST, 'getOpt');
if($getOpt !== null){
    $homepages = $homepagequery->modalHomepages($getOpt);
	$options = array();
    foreach($homepages as $hp){
        $options[] = array('id' => $hp['id'], 'name' => $hp['name'] .' <small>('. $hp['slug'] .')</small>');
    }
    echo json_encode($options);
}

