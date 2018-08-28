<?php
require_once OVERLAY_DIR.'/overlay_class.php';

global $overlay;

$video = $_POST["data"]["video"];
Assign('video', $video);

/**
 *	/!\ Important to use Expand Video Manager
 *
 *	Do not display the template, just compute and assign to a variable
 */
$var = $cbtpl->fetch(PLUG_DIR.'/video_overlay/admin/set_overlays.html');
/**
 *	Display the variable
 */
echo $var;