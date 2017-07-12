<?php

	// This script runs only via command line
	//sleep(5);
	include_once(dirname(__FILE__)."/../../includes/config.inc.php");
	include_once(dirname(__FILE__)."/../../includes/functions.php");
	include_once(dirname(__FILE__)."/functions_system.php");
	
	$videoRootFolder=BASEDIR."/files/videos/";
	$originalRootFolder=BASEDIR."/files/original/";

	/**
	 * Prefix generated by the encoder job used to specify that the coresponding file is the original.
	 * The original files are stored in a different folder (files/original and not files/videos)
	 * @var string $prefixOriginal
	 */
	$prefixOriginal="original";
	
	/**
	 * Prefix generated by the encoder job used to specify that the coresponding file is an audio file.
	 * Audio files  are stored in the /file/videos  sunfloders but with this prefix added to prevent errors
	 * when playing the video into the playing page
	 * @var string $prefixAudio
	 */
	$prefixAudio="audio";
	global $Cbucket;
	$ffmpegpath = $Cbucket->configs['ffmpegpath'];
	$ffprobegpath = $Cbucket->configs['ffprobepath'];
	
	global $db;
	// get all enconded videos file that have been connected to a video data 
	$query='SELECT * FROM '.table("job").' WHERE `status`="Encoded" AND `idvideo` IS NOT NULL AND `idvideo`<>0';
	$result=$db->_select($query);

	if (count($result)>0){
		foreach ($result as $res){
			echo date("Y-m-d H:i:s"); 
			$jobName=$res["name"];
			echo " job name : ".$jobName."\n";
			$jobExtension=$res["extension"];
			echo "\tjob extension : ".$jobExtension."\n";
			// Create the full path of the file to be donwloaded
			$srcFullpath= $res["encodedsrc"].$jobName.'.'.$jobExtension;
			echo "\tsrc fullpath : ".$srcFullpath."\n";
				
			$query='SELECT * FROM '.table("video").' WHERE `videoid`='.$res["idvideo"];
			$vresult=$db->_select($query);
			echo "\tidvideo : ".$res["idvideo"]."\n";
				
			if (count($vresult>0)){
				$filename=$vresult[0]["file_name"];
				echo "\tfilename : ".$filename."\n";
				$fileDirectory=$vresult[0]['file_directory'];
				echo "\tfileDirectory : ".$fileDirectory."\n";
				/**
				 * $jobName is formated like this : "prefix_uniquekey". The prefix may be a video format size (480,720,1080...) 
				 * or the "audio" string for audio files or the "original" string for the full quality file. 
				 * The operation to compute in this script depends on this prefi :
				 * <ul>
				 * 		<li> If the prefix is equal to the extension stored into the job fields then we considere that the name 
				 * 			don't have to be changed and the output file pattern will be "FileName.extension" where FileName 
				 * 			come from the video table and the extension come from the job table. The file will be stored in 
				 * 			the /files/videos sunbolders.
				 * 		</li>
				 * 		<li> If the prefix is 'original" then the name will be unchanged like in the previous case 
				 * 			but it will be stored into the /files/original subfolders.
				 * 		</li>
				 * 		<li> If the extension corresponds to a video size then his size will be concatenated at the end
				 * 			of video name and the file will be stored into tih files/videos subfolders. (ie: videoXYZ-720.mp4)
				 * 		</li>
				 * </ul>
				 */
				$table=explode("_", $jobName);
				if ($table[0]==$prefixOriginal)
					$dstFullpath=dirname(__FILE__)."/../../files/original/".$fileDirectory."/".$filename;
				if ($table[0]==$prefixAudio)
					$dstFullpath=dirname(__FILE__)."/../../files/videos/".$fileDirectory."/audio_".$filename;
				else {
					$dstFullpath=dirname(__FILE__)."/../../files/videos/".$fileDirectory."/".$filename;
					if ($table[0]!=$jobExtension)
						$dstFullpath.="-".$table[0];
				}
				$dstFullpath.=".".$jobExtension;
				echo "\tdstFullpath : ".$dstFullpath."\n";
				$process = new Process("wget \"$srcFullpath\" -O \"$dstFullpath\"");
				$query='UPDATE '.table("job").' SET `status` = "Completed" WHERE id="'.$res["id"].'"';
				echo "\t".$query."\n";
				$db->Execute($query);
				
				$durationCmd="ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ".$dstFullpath;
				$output = shell_output($durationCmd);
				$query='UPDATE '.table("video").' SET `duration`='.$output.', `status` = "Successful" WHERE `videoid`='.$res["idvideo"];
				
				
			}
		}
	}
	else {
		//echo "Pas de vidéo à transferer\n";
	}
	
