<?php

require_once '../../includes/admin_config.php';
require_once 'homepage_class.php';

$action = filter_input(INPUT_POST, 'action');
if($action !== 'getTags') return;

$res = $homepagequery->getTags();
$tag = array();
foreach($res as $r){
	$r = explode(',', $r['tags']);
	foreach($r as $t){
		$t = trim($t);
		if($t !== ''){
			$tag[$t]++;
		}
	}
}
$tags = array();
foreach($tag as $t => $nb){
	$tags[] = array('name' => $t, 'nb' => $nb);
}

echo json_encode($tags);

