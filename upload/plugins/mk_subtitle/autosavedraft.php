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

	$post = array();
	$orgFile = file($_POST['markerfile']);
	$postedLine = explode("\n", $_POST['submarker']);
	$data = '';

	foreach ($postedLine as $line){
		$postedArray = explode("\t", $line);
		$post[$postedArray[2]] = $postedArray;
	}

	foreach ($orgFile as $line){
		$original = explode("\t", $line);

		if (isset($post[$original[2]])) {
			$data .= $post[$original[2]][0]."\t".$post[$original[2]][1]."\t".$post[$original[2]][2]."\t".$post[$original[2]][3]."\t".$post[$original[2]][4]."\n";
		}
		else{
			$data .= $original[0]."\t".$original[1]."\t".$original[2]."\t".$original[3]."\t".$original[4]."";
		}
	}


	updateFile($_POST['markerfile'], $data);
	echo 'Save : '.date("H:i:s");
}