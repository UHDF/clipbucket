<?php

if (isset($_POST['markerfile'])){
	$marker = $_POST['markerfile'];
}
else{
	return false;
}


if (file_exists($marker)){

	$lines = file($marker, FILE_IGNORE_NEW_LINES);

	foreach ($lines as $line_num => $line) {
		$t = explode("\t", $line);

		$t[3] = trim($t[3]);			// Delete unwanted space

		if ($t[3] <> ''){
			$subline .= $t[3]." ";
		}
	}

	echo $subline;
}