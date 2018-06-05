<?php

class SharingDlAuth extends CBCategory{
    
    private $db;
    private $active;
    
    public function SharingDlAuth(){
        global $db;        
        $this->db = $db;

        $this->active = $this->checkActivation('sharingdlauth', 'sharingdlauth.php') && $this->checkActivation('video_manager', 'video_manager.php');
    }
    
    public function isActivated(){
        return $this->active;
    }
	
	public function getVideo($id){
        $v = select('SELECT * FROM '. tbl('video') .' WHERE videoid = '. $id);
        if(count($v) === 1){
            return $v[0];
        }
        
        return null;
    }
	
	public function canDl($vid){
		$v = select('SELECT * FROM '. tbl('video') .' WHERE videoid = '. $vid .' AND authdl = 1');
		
		return count($v) === 1;
	}
	
	public function canShare($vid){
		$v = select('SELECT * FROM '. tbl('video') .' WHERE videoid = '. $vid .' AND authsharing = 1');
		
		return count($v) === 1;
	}
	
	public function update($vid){
		$sh = intval(filter_input(INPUT_POST, 'sharing_authorization'));
		$dl = intval(filter_input(INPUT_POST, 'dl_authorization'));
		
		$this->db->update(tbl('video'), array('authdl', 'authsharing'), array($dl, $sh), 'videoid='. intval($vid));
	}
    
    private function checkActivation($folder, $file){
        $count = intval($this->db->count(tbl('plugins'), 'plugin_id', 'plugin_folder = "'. $folder .'" AND plugin_file = "'. $file .'" AND plugin_active = "yes"'));
        return $count === 1;
    }
}

$shdlauthquery = new SharingDlAuth();
$Smarty->assign_by_ref('shdlauthquery', $shdlauthquery);

?>