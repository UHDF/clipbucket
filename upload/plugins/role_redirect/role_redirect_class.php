<?php
class RoleRedirect extends CBCategory{
    
    private $db;
    private $active;
    
    public function RoleRedirect(){
        global $db;        
        $this->db = $db;

        $this->active = $this->checkActivation('role_redirect', 'role_redirect.php');
    }
    
    public function isActivated(){
        return $this->active;
    }
    
    private function checkActivation($folder, $file){
        $count = intval($this->db->count(tbl('plugins'), 'plugin_id', 'plugin_folder = "'. $folder .'" AND plugin_file = "'. $file .'" AND plugin_active = "yes"'));
        return $count === 1;
    }
	
	public function getRedirByGID($gid, $page, $id = null){ 
		$res = $this->db->select(tbl('roleredirect'), '*', 'role_user = "'. mysql_clean($gid) .'" AND pagefrom LIKE "%'. mysql_clean($page) .'" AND id <> '. intval($id));
		return count($res) ? $res[0]['redirectto'] : null;
	}
	
	public function getRoles($id = null){
		global $userquery;
		
		$res = array();
		foreach($userquery->get_levels() as $ulvl){
			$req = $this->db->select(tbl('roleredirect'), '*', 'role_user = "'. mysql_clean($ulvl['user_level_id']) .'" AND pagefrom LIKE "%admin_area/index.php"');
			if(count($req) === 0 || ($id !== null && $req[0]['id'] === strval($id))) $res[] = $ulvl;
		}
		
		return $res;
	}
	
	public function create($gid, $from, $to){
		if($this->getRedirByGID($gid, $from) !== null)
			return false;
		
		$this->db->insert(tbl('roleredirect'), array('role_user', 'pagefrom', 'redirectto'), array(mysql_clean($gid), mysql_clean($from), mysql_clean($to)));
		return true;
	}
	
	public function edit($id, $gid, $from, $to){
		if($this->getRedirByGID($gid, $from, $id) !== null)
			return false;
		
		$this->db->update(tbl('roleredirect'), array('role_user', 'pagefrom', 'redirectto'), array(mysql_clean($gid), mysql_clean($from), mysql_clean($to)), 'id = '. intval($id));
		return true;
	}
	
	public function get($id){
		$res = $this->db->select(tbl('roleredirect'), '*', 'id = '. intval($id));
		return count($res) ? $res[0] : null;
	}
	
	public function getAll(){
		return $this->db->_select('SELECT rr.*, ulvl.user_level_name '
				. 'FROM '. tbl('roleredirect') .' rr LEFT JOIN '. tbl('user_levels') .' ulvl ON ulvl.user_level_id = rr.role_user '
				. 'ORDER BY ulvl.user_level_name ASC');
	}
	
	public function delete($id){
		$this->db->delete(tbl('roleredirect'), array('id'), array(intval($id)));
	}
}

$rredirquery = new RoleRedirect();
$Smarty->assign_by_ref('rredirquery', $rredirquery);

?>