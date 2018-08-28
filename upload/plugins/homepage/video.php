<?php

require_once '../../includes/admin_config.php';
require_once 'homepage_class.php';

$page = intval(filter_input(INPUT_POST, 'p'));
$search = trim(filter_input(INPUT_POST, 's'));
$tags = explode(',', filter_input(INPUT_POST, 't'));
$notin = filter_input(INPUT_POST, 'ids');
$order = trim(filter_input(INPUT_POST, 'order'));

$sugHome = intval(filter_input(INPUT_POST, 'sug_home'));

$res = $sugHome ? $homepagequery->manageHpSuggestedVideos($sugHome, $page, $notin, $order) : $homepagequery->searchVideo($search, $tags, $page, $notin, $order);
$videos = array();
foreach($res['videos'] as $r){
	if($r['datecreated'] === '0000-00-00 00:00:00'){
		$d = '-';
	} else {
		$d = DateTime::createFromFormat('Y-m-d H:i:s', $r['datecreated']);
		$d = $d->format('d/m/Y');
	}
	$videos[] = array('id' => $r['videoid'], 'name' => trim($r['title']), 'date' => $d);
}

$total = intval($res['total']);
$page = intval($res['page']);
$max = intval($res['max']);
$pagination = '';
if(count($res['videos']) < $total){
	$nbPages = ceil($total / count($res['videos']));
	$pages->paginate(count_pages($total, $max), $page);
	$pagination = $Smarty->get_template_vars('pagination');
	$pagination = preg_replace('#<a>&hellip;<\/a>#', '<li class="disabled"><a>&hellip;</a></li>', $pagination);
	$pagination = preg_replace('#href="\?page=(\d+)"#', 'href="#" data-p="$1"', $pagination);
	
	$disabled = $page > 1 ? '' : ' class="disabled"';
	$pagination = '<li'. $disabled .'><a href="#" data-p="1">&laquo;</a></li><li'. $disabled .'><a href="#" data-p="'. ($page - 1) .'">&#8249;</a></li>'. $pagination;
	$disabled = $page !== $nbPages ? '' : ' class="disabled"';
	$pagination .= '<li'. $disabled .'><a href="#" data-p="'. ($page + 1) .'">&#8250;</a></li><li'. $disabled .'><a href="#" data-p="'. $nbPages .'">&raquo;</a></li>';
}

echo json_encode(array('total' => $res['total'], 'videos' => $videos, 'pagination' => $pagination));

