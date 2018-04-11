<?php
/*
Plugin Name: Generate thumbnail
Description: Add a button to generate the thumbnail from a time in the video<br /><span class="glyphicon glyphicon-warning-sign"></span> Require <strong>Video manager</strong> plugin to work <span class="glyphicon glyphicon-warning-sign"></span>
Author: Bastien Poirier, Adrien Ponchelet
Author Website: http://semm.univ-lille1.fr/
ClipBucket Version: 2.8
Version: 1.0
*/

if(intval($db->count(tbl('plugins'), 'plugin_id', 'plugin_folder = "generate_thumbnail" AND plugin_file = "generate_thumbnail.php" AND plugin_active = "yes"')) === 1)
	$Cbucket->add_admin_header(PLUG_DIR . '/generate_thumbnail/admin/header.html');
?>