<?php

	require_once(dirname(__FILE__)."/../../includes/config.inc.php");

	$ffmpeg_path = $Cbucket->configs['ffmpegpath'];

	/**
	 * Video Key Gen
	 * * it is use to generate video key
	 */
	function video_keygen()
	{
		global $db;
		
		$char_list = "ABDGHKMNORSUXWY";
		$char_list .= "123456789";
		while(1)
		{
			$vkey = '';
			srand((double)microtime()*1000000);
			for($i = 0; $i < 12; $i++)
			{
			$vkey .= substr($char_list,(rand()%(strlen($char_list))), 1);
			}
			
			if(!vkey_exists($vkey))
			break;
		}
		
		return $vkey;
	}    

	if ($_POST['id']){
		$d = getRssDetails($_POST['id']);
	}
	else{
		exit();
	}

	/**
	*	Generate file name
	*/
	$newfilename = time().RandomString(5);
	$newkey = video_keygen();

	/**
	*	Get the details of a row
	*/
	$t = getRssVideoDetails($_POST['id']);
	
	$debut = strrpos($t['url_thumnail'], "files/");
	// End of line
	$path = substr($t['url_thumnail'], 0, ($debut+6));
	$videopath = $path.'videos/'.$t['filename'].'-'.$t['default_quality'].'.mp4';

	$duration = 0;

	// Get the duration from ffmpeg
	$command = $ffmpeg_path." -i ".$videopath." 2>&1 | grep \"Duration\" | cut -d ' ' -f 4 | sed s/,// | sed 's@\..*@@g' | awk '{ split($1, A, \":\"); split(A[3], B, \".\"); print 3600*A[1] + 60*A[2] + B[1] }'";

	// Execute the command
	$duration = shell_exec($command);

	$q = "INSERT INTO `video` (`videoid`, `videokey`, `video_password`, `video_users`, `username`, `userid`, `title`, `flv`, `file_name`, `file_directory`, `description`, `tags`, `category`, `category_parents`, `broadcast`, `location`, `datecreated`, `country`, `allow_embedding`, `rating`, `rated_by`, `voter_ids`, `allow_comments`, `comment_voting`, `comments_count`, `last_commented`, `featured`, `featured_date`, `featured_description`, `allow_rating`, `active`, `favourite_count`, `playlist_count`, `views`,  `last_viewed`, `date_added`, `flagged`, `duration`, `status`, `failed_reason`, `flv_file_url`, `default_thumb`, `aspect_ratio`, `embed_code`, `refer_url`, `downloads`, `uploader_ip`, `mass_embed_status`, `is_hd`, `unique_embed_code`, `remote_play_url`, `video_files`, `server_ip`, `file_server_path`, `files_thumbs_path`, `file_thumbs_count`, `has_hq`, `has_mobile`, `filegrp_size`, `process_status`, `has_hd`, `video_version`, `extras`, `thumbs_version`, `re_conv_status`, `conv_progress`) VALUES('', '".$newkey."', '', ' ', '', 1, '".$t['title']."', '', '".$newfilename."', '', '".$t['description']."', '".$t['tags']."', '#".$t['default_cat']."# ', '', 'public', '', '".substr ( $t['date_uploaded'], 0, 10)."', 'FR', 'yes', 0, '0', '', 'no', 'no', 0, '0000-00-00 00:00:00', 'no', '0000-00-00 00:00:00', '', 'no', 'yes', '0', '0', 0, '0000-00-00 00:00:00', '".$t['date_uploaded']."', 'no', '".$duration."', 'Successful', 'none', NULL, 1, '', 'none', '', 0, '10.0.64.65', 'no', 'no', '', '".$videopath."', '', '', '', '', '', 'no', 'no', '', 0, 'no', '2.7', '', '2.8', '', '');";
	
	$ins=$db->execute($q);

	$content = file_get_contents($t['url_thumnail']);
	
	$thumb_file = '../../files/thumbs/'.$newfilename.'-original-1.jpg';
	sleep(2);
	file_put_contents($thumb_file, $content);

	/**
	 * Count of queud video from rss for menu badge
	 */
 	$nbqueued = $db->_select('SELECT COUNT(id) AS rssvid FROM '.tbl("import_rss_video_queued"));
	$rss_badge = $nbqueued[0]['rssvid'];
	
	$test2=$db->execute("DELETE FROM ".tbl("import_rss_video_queued")." WHERE id='".$_POST['id']."'");
?>