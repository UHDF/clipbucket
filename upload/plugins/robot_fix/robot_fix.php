<?php
/*
Plugin Name: Robot Fix
Description: Block Robots from extra pages
Author: Bradford Knowlton
Author Website: http://bradknowlton.com/
ClipBucket Version: 2
Version: 1.0
Website: http://clip-bucket.com/plugin-page
*/

global $Cbucket;
$Cbucket->add_header(BASEDIR.'/plugins/robot_fix/robot_fix.html', 
    					array('download','user_contacts','private_message','upload','groups','collections', 'user_videos', 'watch_video'));