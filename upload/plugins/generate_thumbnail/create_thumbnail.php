<?php
    require_once '../../includes/admin_config.php';
    $userquery->admin_login_check();
    $userquery->login_check('admin_access');
    $pages->page_redir();

    $ffmpeg_path = $GLOBALS['Cbucket']->configs['ffmpegpath'];	
	if($ffmpeg_path === ''){
		echo json_encode(array('error' => 'Error : ffmpeg not found'));
		return;
	}
	
    require_once '../../includes/classes/video.class.php';
    $cbvid = new CBvideo;
	
	$return = '';

    if(isset($_POST['thumb_time']) && isset($_POST['video_id'])){
		$time = filter_input(INPUT_POST, 'thumb_time', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $video = filter_input(INPUT_POST, 'video_id', FILTER_SANITIZE_NUMBER_INT);
		
        $data = get_video_details($video);

        $thumbs = get_thumb($video, 1, true, false, false, true, false);	// Liste des fichiers thumbnails
        $counter = get_thumb_num($thumbs[count($thumbs) - 1]) + 1;		// Connaitre le prochain element
        $thumbs_settings_28 = thumbs_res_settings_28();				// Dimension des vignettes a generer
		
        /**
         *	On cherche le plus gros fichier video (suppose que c'est la meilleur resolution)
         *
         * 	NB : La fonction "get_high_res_file" ne semble pas operationnelle
         */
        $file_name = $data['file_name'];
        $file_dir = $data['file_directory'];
		
        // *** Chemin de base de la video
        $file_video = BASEDIR .'/files/videos/'. $data['file_directory'] .'/'. $data['file_name'] .'-*';
		
        // Nom du plus gros fichier
        $max_video_file_by_size = shell_exec('ls -S '.$file_video.' | head -n 1');
        $max_video_file_by_size = str_replace("\n", "", $max_video_file_by_size);
		
        if(trim($max_video_file_by_size) !== ''){
            foreach($thumbs_settings_28 as $key => $thumbs_size){		
                if($key == 'original'){
                    $output = THUMBS_DIR .'/'. $file_dir .'/'. $file_name .'-original-'. $counter .'.jpg';
                    $command = $ffmpeg_path .' -ss '. $time .' -i '. $max_video_file_by_size .' -f image2 -vframes 1 '. $output;
                } else {
                    $original = THUMBS_DIR .'/'. $file_dir .'/'. $file_name .'-original-'. $counter .'.jpg';
                    $height_setting = $thumbs_size[1];
                    $width_setting = $thumbs_size[0];
                    $output = THUMBS_DIR .'/'. $file_dir .'/'. $file_name .'-'. $width_setting .'x'. $height_setting .'-'. $counter .'.jpg';
				
                    // Pour eviter d'utiliser le fichier video
                    if(file_exists($original)){
                        $command = $ffmpeg_path .' -i '. $original .' -vf scale='. $width_setting .':'. $height_setting .' '. $output;
                    } else {
                        $command = $ffmpeg_path .' -ss '. $time .' -i '. $max_video_file_by_size .' -vf scale='. $width_setting .':'. $height_setting .' -an -r 1 -y -f image2 -vframes 1 '. $output;
                    }
                }
                
                $cmd = shell_exec($command);
            }
            
            $cbvid->set_default_thumb($video, mysql_clean($counter));	
        }
		
		$lastThumb = array('file' => $file_name .'-original-'. $counter .'.jpg', 'name' => $file_name .'-original-'. $counter, 'ext' => '.jpg');
		if(file_exists(THUMBS_DIR .'/'. $file_dir .'/'. $file_name .'-original-'. $counter .'.jpg')){
			$thumbs = get_thumb($data, 1, true, false, true, false, 'original');
			foreach($thumbs as $t){
				$parts = explode('/', $t);
				$file = end($parts);
				if($file === $lastThumb['file']){
					$return = '<li><img src="'. $t .'" width="100" height="100" />';
					$return .= '<div class="tools"><input type="radio" value="'. $file .'" id="'. $lastThumb['name'] .'" name="default_thumb" checked />';
					$return .= '<a href="?video='. $video .'&delete='. $file .'"><i class="icon-remove red"></i></a></div></li>';
				}
			}
			echo json_encode(array('error' => '', 'res' => $return));
		} else {
			echo json_encode(array('error' => 'Error, cannot create thumbnail', 'res' => ''));
		}
		
		return;
    }
	
	echo json_encode(array('error' => 'Error, no valid data', 'res' => ''));
