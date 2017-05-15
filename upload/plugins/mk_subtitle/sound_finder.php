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
	$output = (isset($_POST['marker'])) ? $_POST['marker'] : '../../files/marker/marker_no_video.txt';
	$threshold = (isset($_POST['threshold'])) ? $_POST['threshold'] : -26;
	$durationSilence = (isset($_POST['durationSilence'])) ? $_POST['durationSilence'] : 0.1;
	$delayBefore = (isset($_POST['delayBefore'])) ? $_POST['delayBefore'] : 0.200;
	$delayAfter = (isset($_POST['delayAfter'])) ? $_POST['delayAfter'] : 0.200;


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
		*
		*/
		$command = 'ffmpeg -i '.$video.' -af silencedetect=n='.$threshold.'dB:d='.$durationSilence.' -f null - 2>&1 | grep silence > ../../files/marker/tmp.txt';

		// Execute the command
		$cmd = shell_exec($command);



		/**
		*	Processing the temporary files
		*/
		// Read file (each line is a cell).
		$lines = file('../../files/marker/tmp.txt');

		$firstEnd = 0;
		$cpt = 0;
		$tempArray = array();

		foreach ($lines as $key => $value){
			$value = str_replace("\n", "", str_replace("\r", "", $value));
			$begin = strpos($value, "silence_start:");
			$end = strpos($value, "silence_end:");
			$pipe = strpos($value, " | ");
			$len = strlen($value);

			if ($begin){

				if ($pipe){
					$debut = substr($value, ($begin+15), ($pipe-($begin+15)));
				}
				else{
					$debut = substr($value, ($begin+15), $len);
				}

				if ($firstEnd == 1){
					$tempArray[$cpt-1] = $tempArray[$cpt-1]."\t".$debut;
				}

			}

			if ($end){
				if ($firstEnd == 0){
					$firstEnd = 1;
				}


				if ($pipe){
					$fin = substr($value, ($end+13), ($pipe-($end+13)));
				}
				else{
					$fin = substr($value, ($end+13), $len);
				}

				if ($firstEnd == 1){
					$tempArray[$cpt] = $fin;
				}
			}

			$cpt++;
			unset($begin, $end, $pipe);
		}


		/**
		*	Write the marker file.
		*/
		$cpt = 1;
		$fp = fopen($output, "w+");
		foreach ($tempArray as $value){
			fwrite($fp, $value."\t".$cpt."\n");
			$cpt++;
		}
		fclose($fp);

		/**
		*	Write the meta who done the resulting file.
		*/
		$fic = str_replace("marker_", "marker_meta_", $output);
		$fp = fopen($fic, "w+");
		fwrite($fp, $threshold."\t".$durationSilence."\t".$delayBefore."\t".$delayAfter);
		fclose($fp);

	}
	else{
		echo 'No video found !';
	}


?>

	<form method="POST">
		
	<?php

		// Read the file (FILE_IGNORE_NEW_LINES delete the carriage return at end of line).
		$lines = file($output, FILE_IGNORE_NEW_LINES);

		foreach ($lines as $line_num => $line) {

			$t = explode("\t", $line);

			$begin = substr($t[0],0,(strpos($t[0], ".")+4));
			$end = substr($t[1],0,(strpos($t[1], ".")+4));

			echo secondToTime($begin).' --> '.secondToTime($end).' - Durée : '.round(($end-$begin), 2).'<br>';
			echo "\n";
			echo '<input type="text" name="phrase'.$t[2].'" id="phrase'.$t[2].'" size="80" onfocus="inputPlay('.($t[0]-$delayBefore).', '.($t[1]+$delayAfter).');" onblur="inputStop();" '.((isset($t[3])) ? 'value ="'.$t[3].'"' : '').'><br>';
			echo "\n";

		}

		echo '<input name="nbMarker" id="nbMarker" value="'.count($lines).'">';
?>

		<input type="submit" name="envoyer" value="Envoyer">
	</form>