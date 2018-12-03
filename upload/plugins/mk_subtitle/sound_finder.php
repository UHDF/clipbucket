<?php
	/**
	*	Some functions needed.
	*/
	require("functions.php");

	/**
	 *	Detect where audio on video by the silencedetect ffmpeg filter
	 */
	// Get or define default parameters
	$video = (isset($_POST['video'])) ? $_POST['video'] : '';
	$output = (isset($_POST['marker'])) ? $_POST['marker'] : '../../files/subtitle/marker/marker_no_video.txt';
	$ffmpeg_path = (isset($_POST['ffmpeg_path'])) ? $_POST['ffmpeg_path'] : 'ffmpeg';
	$threshold = (isset($_POST['threshold'])) ? $_POST['threshold'] : -26;
	$durationSilence = (isset($_POST['durationSilence'])) ? $_POST['durationSilence'] : 0.1;
	$delayBefore = (isset($_POST['delayBefore'])) ? $_POST['delayBefore'] : 0.200;
	$delayAfter = (isset($_POST['delayAfter'])) ? $_POST['delayAfter'] : 0.200;

	$originalLanguage = (isset($_POST['originalLanguage'])) ? $_POST['originalLanguage'] : 'fr';

	$ffmpeg_version = trim(`$ffmpeg_path -version | head -n 1 | cut -d" " -f3`, "\n");

	// If video parameter exist
	if ($video){

		/**
		*	Verbose ffmpeg command ffmpeg, print information about finded silence in a file.
		*
		*	Write in a temporary file the result of the ffmpeg command.
		*	Read the temporary in order to reorder the silence stop and silence start timestamp.
		*
		*	ffmpeg example output :
		*
		*		[silencedetect @ 0x3316c80] silence_start: -0.0213605
		*		[silencedetect @ 0x3316c80] silence_end: 11.0063 | silence_duration: 11.0276
		*		[silencedetect @ 0x3316c80] silence_start: 14.0964
		*
		*/
		$command = $ffmpeg_path.' -i '.$video.' -af silencedetect=n='.$threshold.'dB:d='.$durationSilence.' -f null - 2>&1 | grep silence > ../../files/subtitle/marker/tmp.txt';

		// Execute the command
		$cmd = shell_exec($command);

		/**
		*	Processing the temporary files
		*/
		// Read file (each line is a cell).
		$lines = file('../../files/subtitle/marker/tmp.txt');

		$firstEnd = 0;
		$cpt = 0;
		$tempArray = array();
		$nblines = count($lines);

		foreach ($lines as $key => $value){
			$value = str_replace("\n", "", str_replace("\r", "", $value));
			$begin = strpos($value, "silence_start:");
			$end = strpos($value, "silence_end:");
			$pipe = strpos($value, " | ");
			$len = strlen($value);

			if ($begin){

				$debut = substr($value, ($begin+15), $len);

				if ($firstEnd == 1){
					$tempArray[$cpt-1] = $tempArray[$cpt-1]."\t".$debut;
				}

			}

			if ($end){
				if ($firstEnd == 0){
					$firstEnd = 1;
				}

				$fin = substr($value, ($end+13), ($pipe-($end+13)));

				if ($ffmpeg_version < 4.1){
					// Version inferieur a 4.1
					if ($firstEnd == 1) {
						$tempArray[$cpt] = $fin;
					}
				}
				else{
					// Version superieur ou egal a 4.1
					if ( ($firstEnd == 1) && ($cpt != ($nblines-1)) ) {
						$tempArray[$cpt] = $fin;
					}
				}

			}

			$cpt++;
			unset($begin, $end, $pipe);
		}


		$arr = array();
		foreach ($tempArray as $key => $value){
			$t = explode("\t", $value);
			$t[2] = ($t[1]-$t[0]);

			$arr[] = $t;
		}

		/**
		*	Replace and delete where difference less than 1 seconds.
		*/
		for ($i = (Count($arr)-1); $i >= 0 ; $i--){
			if ($i >= 1){
				if ($arr[$i][2] < 1){								// Subtitle do not be less than 1 second.

					$arr[$i-1][1] = $arr[$i][1];					// Set the value
					$arr[$i-1][2] = ($arr[$i][1]-$arr[$i-1][0]);	// New diff
					unset($arr[$i]);
				}
			}
		}

		/**
		*	Write the marker file.
		*/
		$cpt = 1;
		$fp = fopen($output, "w+");
		foreach ($arr as $value){
			fwrite($fp, $value[0]."\t".$value[1]."\t".$cpt."\n");
			$cpt++;
		}
		fclose($fp);

		/**
		*	Write the meta who done the resulting file.
		*/
		$fic = str_replace("marker_", "marker_meta_", $output);
		$fp = fopen($fic, "w+");

		$jsonmeta = array(
			"threshold" => $threshold,
			"durationSilence" => $durationSilence,
			"delayBefore" => $delayBefore,
			"delayAfter" => $delayAfter,
			"originalLanguage" => $originalLanguage
		);
		fwrite($fp, json_encode($jsonmeta));
		fclose($fp);

	}
	else{
//		echo 'No video found !';
	}
?>

{"phrases":[
<?php

	// Read the file (FILE_IGNORE_NEW_LINES delete the carriage return at end of line).
	$lines = file($output, FILE_IGNORE_NEW_LINES);

	foreach ($lines as $line_num => $line) {

		$t = explode("\t", $line);

		$begin = substr($t[0],0,(strpos($t[0], ".")+4));
		$end = substr($t[1],0,(strpos($t[1], ".")+4));

		echo '
			{
				"id": "'.$t[2].'",
				"begin": "'.$begin.'",
				"end": "'.$end.'",
				"tbegin": "'.secondToTime($begin).'",
				"tend": "'.secondToTime($end).'",
				"duration": "'.round(($end-$begin), 2).'"
			}
		';

		if ($line_num < (count($lines)-1)){
			echo ',';
		}
	}	// end foreach

	echo '],';
	echo '"nbmarker": "'.count($lines).'",';		// nbMarker
?>
"datatime": ""}