<?php

define('THIS_PAGE','index');

require_once __DIR__. '/../../includes/config.inc.php';
require_once __DIR__. '/homepage_class.php';
$pages->page_redir();

$slug = filter_input(INPUT_GET, 'slug');
$homepage = $homepagequery->getBySlug($slug);
if(!$homepage){
	template_files('404.html');
} else {
	template_files('index.html', PLUG_DIR .'/homepage/layout');

	$videos = $homepagequery->getHomeVideos($homepage['id']);
	Assign('videos', $videos);
}

display_it();