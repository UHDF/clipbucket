<?php
/**
 * Class Containing actions for the live plugin 
 */
class LiveUDL extends CBCategory{
    
    private $db;
    private $cb_columns;
    private $max_per_page = 50;

    /**
     * Constructor
     */
    public function LiveUDL()	{
        global $db, $cb_columns;
        
        $fields = array('id', 'title','description', 'date', 'active', 'homepage', 'url');
    	$cb_columns->object('liveudl')->register_columns($fields);
        
        $fields = array('id', 'rtmp','http');
    	$cb_columns->object('liveudl_rtmp')->register_columns($fields);
        
        $this->db = $db;
        $this->cb_columns = $cb_columns;
    }
    
    public function mysql_clean($str){
        return $this->db->mysqli->real_escape_string($str);
    }
    
    
    
    public function getTotal(){
        $query = 'SELECT COUNT(live.id) as res FROM '. tbl('liveudl'). 'AS live';
        $res = select($query);
        return $res['res'];
    }
    
    public function getMax(){
        return $this->max_per_page;
    }
    
    public function getDefault(){
        return array('id' => 0, 'thumb' => null, 'title' => '', 'description' => '', 'date' => null, 'visible' => true, 'active' => false, 'homepage' => false, 'rtmpid' => 0);
    }
    
    public function live_link($live = null){
        if($live === null){
            $live = array('id' => 'LIVEID', 'title' => 'LIVE_TITLE');
        } elseif(is_numeric($live)){
            $lid = intval($live);
            if($lid){
                $query = 'SELECT live.* FROM '.tbl('liveudl').' AS live WHERE live.id = '. $lid;
                $res = select($query);

                $live = count($res) === 1 ? $res[0] : null;
            }
        }
        
        if($live){
            $link = BASEURL.'/watch_live.php?l='.$live['id'];
            if(SEO == 'yes'){
                $title = SEO(clean(str_replace(' ','-',strtolower($live['title']))));
                $title = substr($title, 0, 1) === '-' ? substr($title, 1) : $title;
                $title = substr($title, -1) === '-' ? preg_replace('#-$#', '', $title) : $title;
                
                if($live['id'] === 'LIVEID') $title = 'LIVE_TITLE';
                
                switch(config('seo_vido_url')){
                    case 1:
                        $link = BASEURL.'/'.$title.'_l'.$live['id'];
                        break;

                    case 2:
                        $link = BASEURL.'/live/'.$live['id'].'/'.$title;
                        break;

                    case 3:
                        $link = BASEURL.'/live/'.$live['id'].'_'.$title;
                        break;

                    default:
                        $link = BASEURL.'/live/'.$live['id'].'/'.$title;
                        break;
                }
            }

            return $link;
        }
        
        return BASEURL;
    }
    
    public function getFront(){
        $query = 'SELECT * FROM '.tbl('liveudl').' WHERE homepage = 1 ORDER BY date ASC';
        return select($query);
    }
    
    public function getLiveRtmp($lid){
        $id = intval($lid);
        if($id === 0) return null;

        $query = 'SELECT live.id, live.thumb, live.title, live.description, live.date, live.visible, live.active, live.homepage, live.rtmpid';
        $query .= ', rtmp.fms, rtmp.fmsid ';
        $query .= 'FROM '.tbl('liveudl').' AS live LEFT JOIN '. tbl('liveudl_rtmp') .' rtmp ON live.rtmpid = rtmp.id ';
        $query .= 'WHERE live.id = '. $id;
        $res = select($query);
        
        if(count($res) === 1){
            $live = $res[0];
            $qualities = select('SELECT * FROM '. tbl('liveudl_http') .' WHERE rtmpid = '. $live['rtmpid']);
            $live['qualities'] = $qualities;
            
            $today = new DateTime();
            $d = new DateTime($live['date']);
            $diff = $d->diff($today);
            if($diff->invert === 1){
                $live['diff'] = $today->format('Ymd') === $d->format('Ymd') ? 0 : -1;
            } else {
                $live['diff'] = $today->format('Ymd') === $d->format('Ymd') ? 0 : 1;
            }
            
            return $live;
        }
        else null;
    }
    
    public function getAllRtmp($asc = false){
        $query = 'SELECT rtmp.*, COUNT(live.id) as usedBy FROM '.tbl('liveudl_rtmp').' AS rtmp LEFT OUTER JOIN '. tbl('liveudl') .' AS live ';
        $query .= 'ON live.rtmpid = rtmp.id GROUP BY rtmp.id';
        
        if($asc){
            $query .= ' ORDER BY rtmp.fms ASC, rtmp.fmsid ASC';
        }
        
        return select($query);
    }
    
    public function getRtmp($id){
        $id = intval($id);
        if($id === 0){
            return array('id' => 0, 'fms' => null, 'fmsid' => null);
        }
        
        $query = 'SELECT * FROM '.tbl('liveudl_rtmp').' AS rtmp WHERE id = '. $id;
        $res = select($query);
        
        if(count($res) === 1) return $res[0];
        else return false;
    }
    
    private function getRtmpByFms($fms, $fmsid){
        $query = 'SELECT * FROM '.tbl('liveudl_rtmp').' AS lopt WHERE fms = "'. $fms .'" AND fmsid = "'. $fmsid .'"';
        $res = select($query);
        
        if(count($res) === 1){
            return $res[0];
        }
        
        return null;
    }
    
    public function uniqRtmp($fms, $fmsid, $id = 0){
        $query = 'SELECT * FROM '.tbl('liveudl_rtmp').' AS lopt WHERE fms = "'. $fms .'" AND fmsid = "'. $fmsid .'"';
        $res = select($query);
        
        if(!count($res)) return true;
        elseif(count($res) === 1) {
            if($id === intval($res[0]['id'])) return true;
        }
        
        return false;
    }
    
    public function setRtmp($fms, $fmsid, $qualities, $id = 0){
        if(!error()){
            if($this->uniqRtmp($fms, $fmsid, $id)){
                if($id === 0){
                    $this->db->insert(tbl('liveudl_rtmp'), array('fms', 'fmsid'), array($fms, $fmsid));
                    $rtmp = $this->getRtmpByFms($fms, $fmsid);
                    if($rtmp !== null) $id = $rtmp['id'];
                } else {
                    $query = 'SELECT * from '. tbl('liveudl_rtmp') .' WHERE id = '. $id;
                    $res = select($query);
                    if(count($res) === 1){
                        $this->db->update(tbl('liveudl_rtmp'), 
                            array('fms', 'fmsid'), 
                            array($fms, $fmsid), 'id='. $id);
                    }
                }
                
                $this->setQualities($id, $qualities);
                
                return '';
            }
            
            return lang('liveudl_rtmpNotUniq', $fms .'/'. $fmsid);
        }
    }
    
    public function deleteRtmp($id){
        if(!error()){
            $query = 'SELECT * from '. tbl('liveudl_rtmp') .' WHERE id = '. $id;
            $res = select($query);
            if(count($res) === 1){
                $title = $res[0]['fms'] .'/'. $res[0]['fmsid'];
                $query = 'SELECT * from '. tbl('liveudl') .' WHERE rtmpid = '. $id;
                $res = select($query);
                if(!count($res)){
                    $this->db->delete(tbl('liveudl_http'), array('rtmpid'), array($id));
                    $this->db->delete(tbl('liveudl_rtmp'), array('id'), array($id));
                    return $title;
                }
            }
        }
    }
    
    private function setQualities($id, $qualities){
        $this->db->delete(tbl('liveudl_http'), array('rtmpid'), array($id));
        
        foreach($qualities as $q => $http){
            if($http['dash'] || $http['hls']) $this->db->insert(tbl('liveudl_http'), array('rtmpid', 'quality', 'dash', 'hls'), array($id, intval($q), $http['dash'], $http['hls']));
        }
    }
    
    public function getQualities($id){
        $id = intval($id);
        if($id === 0) return false;
        $query = 'SELECT * FROM '.tbl('liveudl_http').' AS lhttp WHERE rtmpid = '. $id .' ORDER BY quality DESC';
        $res = select($query);
        
        return $res;
    }
    
    public function getLivesFront(){
        $page = $page > 0 ? $page : 1;
        $tomorrow = new DateTime('tomorrow');
        $query = 'SELECT * FROM '.tbl('liveudl').' WHERE visible = 1 AND date >= '. $tomorrow->format('Y-m-d') .' ORDER BY date ASC';
        $lives = select($query);
        
        for($i = 0; $i < count($lives); $i++){
            $lives[$i]['link'] = $this->live_link($lives[$i]);
        }
        
        return $lives;
    }
    
    public function getLives($page = 1){ 
        $page = $page > 0 ? $page : 1;

        $query = 'SELECT live.* FROM '.tbl('liveudl').' AS live ORDER BY live.id DESC';
        
        if($page === 1){
            $query .= ' LIMIT '. $this->max_per_page;
        } else {
            $query .= ' LIMIT '. (($page - 1) * $this->max_per_page) .', '. $this->max_per_page;
        }
        
        return select($query);
    }
    
    public function getLive($id){
        $id = intval($id);
        if($id === 0) return $this->getDefault();

        $query = 'SELECT live.* FROM '.tbl('liveudl').' AS live WHERE live.id = '. $id;
        $res = select($query);
        
        if(count($res) === 1) return $res[0];
        else return $this->getDefault();
    }
    
    public function setLive($title, $desc, $date, $visible, $active, $front, $fmsid, $thumb, $lid = 0, $oldfile = ''){
        if(!error()){
            $filename =  '';
            if(is_array($thumb)){
                $filename = uniqid() . strtolower(strrchr($thumb['name'], '.'));
                $thumb['new_name'] = $filename;
            }
            
            
            if($lid === 0){
                $this->db->insert(tbl('liveudl'), array('thumb', 'title', 'description', 'date', 'visible', 'active', 'homepage', 'rtmpid'), 
                        array($filename, $this->mysql_clean($title), $desc, $date->format('Y-m-d H:i:s'), $visible, $active, $front, $fmsid));
            } else {
                $query = 'SELECT * FROM '.tbl('liveudl').' AS live WHERE id = '. $lid;
                $res = select($query);
                if(count($res) === 1){
                    $this->db->update(tbl('liveudl'), 
                        array('thumb', 'title', 'description', 'date', 'visible', 'active', 'homepage', 'rtmpid'), 
                        array($filename, $this->mysql_clean($title), $desc, $date->format('Y-m-d H:i:s'), $visible, $active, $front, $fmsid), 'id="'. $lid .'"');
                }
            }
            
            return $this->updateThumb($thumb, $oldfile);
        }
        
        return true;
    }
    
    public function deleteLive($id){
        if(!error()){
            $query = 'SELECT * FROM '.tbl('liveudl').' AS live WHERE id = '. $id;
            $res = select($query);
            if(count($res) === 1){
                $this->db->delete(tbl('liveudl'), array('id'), array($id));
                $this->deleteThumb($res[0]['thumb']);
                return $res[0]['title'];
            }
        }
    }
    
    private function updateThumb($file, $oldfile = ''){
        if($oldfile !== '') $this->deleteThumb($oldfile);
        
        if(!is_array($file)) $this->deleteThumb($file);
        else return move_uploaded_file($file['tmp_name'], LIVEUDL_THUMBSDIR . $file['new_name']);
        
        return true;
    }
    
    private function deleteThumb($file){
        if(file_exists(LIVEUDL_THUMBSDIR . $file)){
            unlink(LIVEUDL_THUMBSDIR . $file);
        }
    }
    
    public function setActive($id, $st = null){
        $this->setAction($id, 'active', $st);
    }
    
    public function setFront($id, $st = null){
        $this->setAction($id, 'homepage', $st);
    }
    
    public function setVisible($id, $st = null){
        $this->setAction($id, 'visible', $st);
    }

    private function setAction($id, $field, $st){
        if(!error()){
            if($id !== 0){
                $query = 'SELECT * FROM '.tbl('liveudl').' AS live WHERE id = '. $id;
                $res = select($query);
                if(count($res) === 1){
                     if($st === null) $st = !$res[0][$field];
                    $this->db->update(tbl('liveudl'), array($field), array($st), 'id="'. $id .'"');
                }
            }
        }
    }
}

$liveudlquery = new LiveUDL();
$Smarty->assign_by_ref('liveudlquery', $liveudlquery);
?>