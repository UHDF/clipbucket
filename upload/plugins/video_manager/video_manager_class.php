<?php
/**
 * Class Containing actions for the tag manager plugin 
 */
class VideoManager extends CBCategory{
    
    private $db;
    private $cb_columns;
    private $epInstalled;
    private $active;
    private $notpl;
    
    public function VideoManager(){
        global $db;        
        $this->db = $db;
        
        $this->epInstalled = $this->checkActivation('editors_pick', 'editors_picks.php');
        $this->active = $this->checkActivation('video_manager', 'video_manager.php');
        $this->notpl = $this->checkNoTpl();
    }
    
    public function isActivated(){
        return $this->active && !$this->notpl;
    }
    
    public function getEpInstalled(){
        return $this->epInstalled;
    }
    
    public function getVideo($id){
        $v = select('SELECT * FROM '. tbl('video') .' WHERE videoid = '. $id);
        if(count($v) === 1){
            return $v[0];
        }
        
        return null;
    }
    
    public function is_featured($video){
        $v = null;
        if(is_array($video) && is_numeric($video['videoid'])){
            $v = $this->getVideo($video['videoid']);
        } elseif(is_numeric($video)){
            $v = $this->getVideo($video);
        }
        
        if($v !== null){
            return $v['featured'] === 'yes';
        }
        
        return false;
    }
    
    public function inEditorsPicks($video){
        if(!$this->epInstalled){
           return false; 
        }
        
        $vid = 0;        
        if(is_array($video) && is_numeric($video['videoid'])){
            $vid = $video['videoid'];
        } elseif(is_numeric($video)){
            $vid = $video;
        }
        
        $count = intval($this->db->count(tbl('editors_picks'), 'videoid', ' videoid='. $vid));
        return $count > 0;
    }
	
	public function updateDateCreated($vid, $date){
		$datetime = DateTime::createFromFormat('d/m/Y H:i', $date);
		if($datetime === false) return false;
		$this->db->update(tbl('video'), array('datecreated'), array($datetime->format('Y-m-d H:i:s')), 'videoid = '. intval($vid));
		
		return true;
	}
    
    private function checkActivation($folder, $file){
        $count = intval($this->db->count(tbl('plugins'), 'plugin_id', 'plugin_folder = "'. $folder .'" AND plugin_file = "'. $file .'" AND plugin_active = "yes"'));
        return $count === 1;
    }
    
    private function checkNoTpl(){
        return filter_input(INPUT_GET, 'notpl') !== null;
    }
}

$vidmquery = new VideoManager();
$Smarty->assign_by_ref('vidmquery', $vidmquery);

?>