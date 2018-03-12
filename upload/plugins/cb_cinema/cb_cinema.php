<?php
/*
Plugin Name: Watch in Cinema
Description: Give your users a nice of feel of cinema with this plugin.
Author: Fawaz
Author Website: http://clip-bucket.com/
ClipBucket Version: 2.0
Version: 1.0
*/
$Cbucket->add_header(PLUG_DIR.'/cb_cinema/header.html', array('watch_video'));

function cb_cinema() {
	echo '<a href="javascript:void(0)" id="lightButton" class="lightstoggle lightsoff">Lights Toggle</a>';	
}


register_anchor_function("cb_cinema","before_watch_player");

?>