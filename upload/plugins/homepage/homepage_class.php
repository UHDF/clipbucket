<?php

class Homepage extends CBCategory{
	private $db;
    private $active;
	private $max;
	
	public function Homepage(){
        global $db;
        
        $this->db = $db;
		$this->active = $this->checkActivation('homepage', 'homepage.php');
		$this->max = 100;
    }
	
	private function checkActivation($folder, $file){
		$count = intval($this->db->count(tbl('plugins'), 'plugin_id', 'plugin_folder = "'. $folder .'" AND plugin_file = "'. $file .'" AND plugin_active = "yes"'));
        return $count === 1;
	}
	
	public function isActive(){
		return $this->active;
	}
	
	public function canManage($userlevel, $home){
		$count = intval($this->db->count(tbl('custom_home_page_permission'), 'id', 'userlevel_id = '. intval($userlevel) .' AND home_id = '. intval($home)));
        return $count > 0;
	}
	
	public function getAllHomepages(){
		$query = 'SELECT h.*, ulvl.user_level_name FROM '. tbl('custom_home_page') .' h LEFT JOIN '. tbl('custom_home_page_permission') . ' hperm ON hperm.home_id = h.id ';
		$query .= 'LEFT JOIN '. tbl('user_levels') .' ulvl ON hperm.userlevel_id = ulvl.user_level_id ORDER BY h.name ASC, ulvl.user_level_name ASC';
		return $this->db->_select($query);
	}
	
	public function getByHome($id){
		$query = 'SELECT h.*, ulvl.* FROM '. tbl('custom_home_page') .' h LEFT JOIN '. tbl('custom_home_page_permission') . ' hperm ON hperm.home_id = h.id ';
		$query .= 'LEFT JOIN '. tbl('user_levels') .' ulvl ON hperm.userlevel_id = ulvl.user_level_id ';
		$query .= 'WHERE h.id = '. intval($id) .' ORDER BY h.name ASC, ulvl.user_level_name ASC';
		return $this->db->_select($query);
	}
	
	public function getByLevel($lid){
		$query = 'SELECT h.*, ulvl.user_level_name FROM '. tbl('custom_home_page') .' h LEFT JOIN '. tbl('custom_home_page_permission') . ' hperm ON hperm.home_id = h.id ';
		$query .= 'LEFT JOIN '. tbl('user_levels') .' ulvl ON hperm.userlevel_id = ulvl.user_level_id ';
		$query .= 'WHERE hperm.userlevel_id = '. $lid .' ORDER BY h.name ASC, ulvl.user_level_name ASC';
		return $this->db->_select($query);
	}
	
	public function exists($name, $slug, $homeid = 0){
		$count = intval($this->db->count(tbl('custom_home_page'), 'id', 'id <> '. intval($homeid).' AND (name = "'. mysql_clean($name) .'" OR slug = "'. mysql_clean($slug) .'")'));
		return $count !== 0;
	}
	
	public function create($name, $slug){
		$this->db->insert(tbl('custom_home_page'), array('name','slug'), array(mysql_clean($name), mysql_clean($slug)));
		$last = $this->db->select(tbl('custom_home_page'), '*', 'name = "'. mysql_clean($name) .'" AND slug = "'. mysql_clean($slug) .'"');
		if(count($last)){
			return $last[0];
		}
		
		return false;
	}
	
	public function update($id, $name, $slug){
		$this->db->insert(tbl('custom_home_page'), array('name','slug'), array(mysql_clean($name), mysql_clean($slug)), 'where id = '. intval($id));
	}
	
	public function addPermission($home, $ulvl){
		if($this->canManage($ulvl, $home)){
			return;
		}
		
		$this->db->insert(tbl('custom_home_page_permission'), array('home_id','userlevel_id'), array(intval($home), intval($ulvl)));
	}
	
	public function removeAllPermissions($id){
		$this->db->delete(tbl('custom_home_page_permission'), array('home_id'), array(intval($id)));
	}
	
	public function delete($id){
		$this->db->delete(tbl('custom_home_page'), array('id'), array(intval($id)));
		$this->db->delete(tbl('custom_home_page_permission'), array('home_id'), array(intval($id)));
		$this->db->delete(tbl('custom_home_page_video'), array('home_id'), array(intval($id)));
	}
	
	public function getBySlug($slug){
		$query = 'SELECT * FROM '.tbl('custom_home_page') .' WHERE slug = "'. mysql_clean($slug) .'"';
		$res = $this->db->_select($query);
		if(count($res)){
			return $res[0];
		}
		
		return false;
	}
	
	public function getHomeVideos($id){
		$query = 'SELECT v.*, hvid.picked chppicked FROM '. tbl('video') .' v LEFT JOIN '. tbl('custom_home_page_video') . ' hvid ON v.videoid = hvid.video_id ';
		$query .= 'WHERE hvid.home_id = '. $id .' ORDER BY hvid.position ASC';
		return $this->db->_select($query);
	}
	
	public function addHomeVideos($homeid, $vids, $picked){
		$vids = explode(',', $vids);
		$picked = explode(',', $picked);
		if(count($vids) !== count($picked)) return false;
		
		$this->removeHomeVideos($homeid);
		for($i = 0; $i < count($vids); $i++){
			$this->db->insert(tbl('custom_home_page_video'), array('home_id','video_id', 'position', 'picked'), array(intval($homeid), intval($vids[$i]), $i, intval($picked[$i])));
		}
		return true;
	}
	
	public function removeHomeVideos($id){
		$this->db->delete(tbl('custom_home_page_video'), array('home_id'), array(intval($id)));
	}
	
	public function removeHomeVideo($homeid, $vid){
		$this->db->delete(tbl('custom_home_page_video'), array('home_id', 'video_id'), array(intval($homeid), intval($vid)));
	}
	
	public function getSuggestedHp($vid){
		return $this->db->select(tbl('custom_home_page_suggest'), 'home_id', 'video_id = '. intval($vid));
	}
	
	public function getSuggestedVideos($homeid, $sort = 'title'){
		$query = 'SELECT v.* FROM '. tbl('video') . ' v LEFT JOIN '. tbl('custom_home_page_suggest') .' chps ON chps.video_id = v.videoid';
		$query .= ' WHERE chps.home_id = '. intval($homeid) .' ORDER BY ';
		$query .= $sort === 'title' ? 'v.title ASC' : 'v.created DESC';
		return $this->db->_select($query);
	}
	
	public function addSuggestedVideo($homeid, $vid){
		$this->db->insert(tbl('custom_home_page_suggest'), array('home_id','video_id'), array(intval($homeid), intval($vid)));
	}
	
	public function removeSuggestedVideo($homeid, $vid){
		$this->db->delete(tbl('custom_home_page_suggest'), array('home_id', 'video_id'), array(intval($homeid), intval($vid)));
	}
	
	public function removeSuggestedVideos($homeid){
		$this->db->delete(tbl('custom_home_page_suggest'), array('home_id'), array(intval($homeid)));
	}
	
	public function isVideoSuggested($homeid, $vid){
		$count = intval($this->db->count(tbl('custom_home_page_suggest'), 'id', 'home_id = '. intval($homeid) .' AND video_id = '. intval($vid)));
		return $count !== 0;
	}
	
	public function searchVideo($search = '', $tags = array(), $page = 1, $notin = '', $order = 'title'){
		if($search !== ''){
			$search = 'title like "%'. mysql_clean($search) .'%"';
		}
		
		$sTags = '';
		if(count($tags)){
			for($i = 0; $i < count($tags); $i++){
				$t = mysql_clean($tags[$i]);
				$t = preg_replace('#&amp;#', '&', $t);
				if($t !== ''){
					$sTags .= ($sTags === '' ? '' : ' OR ') . 'tags LIKE "'. $t .'" OR tags LIKE "%,'. $t .'" OR tags LIKE "'. $t .',%" OR tags LIKE "%,'. $t .',%"';
				}
			}
		}
		
		if($search !== '' && $sTags !== ''){
			$search = ' AND ('. $search .' OR '. $sTags .')';
		} elseif($search !== ''){
			$search = ' AND '. $search;
		} elseif ($sTags !== ''){
			$search = ' AND ('. $sTags .')';
		}
		
		if($notin !== ''){
			$notin = ' AND videoid NOT IN ('. $notin .')';
		}
		
		$page = intval($page) > 0 ? intval($page) : 1;
		$limit = (($page - 1) * $this->max) .', '. $this->max;
		$cond = ' active = "yes" AND broadcast = "public"'. $search . $notin;
		
		if($order !== 'title' AND $order !== 'date' OR $order === 'title') $order = 'title ASC';
		if($order === 'date') $order .= 'created DESC';
		
		return array(
			'total' => $this->countSearchVideo($cond), 
			'videos' => $this->db->select(tbl('video'), '*', $cond, $limit, $order), 
			'page' => $page, 
			'max' => $this->max
		);
	}
	
	public function getTags(){
		$query = 'SELECT tags FROM '. tbl('video') .' WHERE active = "yes" AND broadcast = "public"';
		return $this->db->_select($query);
	}
	
	public function getSuggested($videoid){
		$query = 'SELECT * FROM '. tbl('custom_home_page') . ' chp LEFT JOIN '. tbl('custom_home_page_suggest') .' chps ON chp.id = chps.home_id';
		$query .= ' WHERE chps.video_id = '. mysql_clean($videoid) .' ORDER BY chp.name ASC';
		return $this->db->_select($query);
	}
	
	public function manageHpSuggestedVideos($homeid, $page = 1, $notin = '', $order = 'title'){				
		$page = intval($page) > 0 ? intval($page) : 1;
		$limit = (($page - 1) * $this->max) .', '. $this->max;
		$from = 'FROM '. tbl('video') .' v LEFT JOIN '. tbl('custom_home_page_suggest') .' chps ON chps.video_id = v.videoid';
		$cond = 'chps.home_id = '. intval($homeid) .' AND v.active = "yes" AND v.broadcast = "public"';
		if($notin !== ''){
			$cond .= ' AND v.videoid NOT IN ('. $notin .')';
		}
		
		if($order !== 'title' AND $order !== 'date' OR $order === 'title') $order = 'title ASC';
		if($order === 'date') $order .= 'created DESC';
		
		$query = 'SELECT COUNT(v.videoid) res '. $from .' WHERE '. $cond;
		$total = $this->db->_select($query);
		$query = 'SELECT v.* '. $from .' WHERE '. $cond .' ORDER BY '. $order .' LIMIT '. $limit;
		
		return array(
			'total' => $total[0]['res'], 
			'videos' => $this->db->_select($query), 
			'page' => $page, 
			'max' => $this->max
		);
	}
	
	public function modalHomepages($notIn = ''){
		$query = 'SELECT * FROM '. tbl('custom_home_page') .' h ';
		if($notIn !== '') $query .= 'WHERE id NOT IN('. mysql_clean($notIn) .') ';
		$query .= 'ORDER BY name ASC';
		return $this->db->_select($query);
	}
	
	private function countSearchVideo($cond){
		return intval($this->db->count(tbl('video'), 'videoid', $cond));
	}
}

$homepagequery = new Homepage();
$Smarty->assign_by_ref('homepagequery', $homepagequery);