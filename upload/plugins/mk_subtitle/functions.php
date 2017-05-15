<?php

function secondToTime($t){

	$p = explode(".", $t);

	$hours = floor($p[0] / 3600);
	$mins = floor($p[0] / 60 % 60);
	$secs = floor($p[0] % 60);

	if (strlen($hours) < 2){ $hours = "0".$hours; }
	if (strlen($mins) < 2){ $mins = "0".$mins; }
	if (strlen($secs) < 2){ $secs = "0".$secs; }

	$microsecs = (isset($p[1])) ? substr($p[1], 0, 3) : '000';

	return $hours.":".$mins.":".$secs.".".$microsecs;
}
