<?php

/**
 * Update the file
 *
 * @param string $file Path to the file
 *
 * @param string $data What to write
 *
 */
function updateFile($file, $data){

	$fp = fopen($file, "w+");			// Open the draft file
	fwrite($fp, $data);
	fclose($fp);

}

if ($_POST['markerfile']){
	updateFile($_POST['markerfile'], $_POST['submarker']);
	echo 'Save : '.date("H:i:s");
}