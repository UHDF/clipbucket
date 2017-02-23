<?php


include("../../includes/config.inc.php");

$videoid = $_GET["vid"];

thumbzup_vote($videoid);
thumbzup_get($videoid);
