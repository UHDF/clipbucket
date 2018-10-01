<?php
/*
Plugin Name: Transcript
Description: Displays the subtitle downside of the video
Author: Adrien Ponchelet
Author Website: https://www.u-picardie.fr
ClipBucket Version: 4
Version: 0.1
*/

/**
 * Return only line of text
 *
 * @param string $string
 * @return void
 */
function notATimecode($string){

	$string = trim(str_replace("\n", "", str_replace("\r", "", $string)));
	$end = array(".", "?", "!");

	if (
		(!preg_match( "/-->/", $string))
		and ($string != '')
		and (!preg_match( "/NOTE:/", $string))
		and (!preg_match( "/NOTE :/", $string))
		and (!preg_match( "/WEBVTT/", $string))
	){
		return (in_array(substr($string, -1, 1), $end)) ? $string.'<br>' : $string.' ';
	}
}

/**
 * Display the content of subtitle file
 *
 * @param string $data
 * @return void
 */
function displayTranscript($data = ''){

	global $lang_obj;

	if ($data['videoid']){

		$deflang = $lang_obj->lang;

		$file = FILES_DIR."/subtitle/subtitle_".$data['videoid']."_".$deflang.".vtt";
		if (!file_exists($file)){
			$file = FILES_DIR."/subtitle/subtitle_".$data['videoid']."_fr.vtt";
			if (!file_exists($file)){
				$file = FILES_DIR."/subtitle/subtitle_".$data['videoid'].".vtt";
			}
		}

		if (file_exists($file)){
			$lines = file($file);

			$var = implode(array_filter($lines, "notATimecode"));

			echo '<a name="transcriptp"></a><h2>Transcription :</h2>';

			echo '<p class="transcript">';
				echo $var;
			echo '</p>';
			echo '<p class="read-more" id="readmore-btn"><a href="#transcriptp" class="button" id="readmore-transcript">Lire la suite</a></p>';

			echo '<hr>';
?>

<style>

	.transcript {
		max-height: 120px;
		position: relative;
		overflow: hidden;
		background-image: linear-gradient(to bottom, transparent, white);
		/* background-image: linear-gradient(to bottom, transparent, white); */
		/*border:1px solid red*/
	}


</style>

<script>
	document.querySelector("#readmore-transcript").addEventListener("click", function(){
		document.querySelector(".transcript").style.maxHeight = 'none';
		document.querySelector("#readmore-btn").style.display = 'none';
	});
</script>

<?php
		}
	}

}

// use {ANCHOR place="displayTranscript" data=$vdata} to add the HTML string into the file.
register_anchor_function('displayTranscript','displayTranscript');

?>