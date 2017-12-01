<?php
/**
 * Class Containing actions for the tag manager plugin 
 */
class TagManager extends CBCategory{
    
    private $db;
    private $cb_columns;
    
    private $total;
    private $max = 100;
    
    public function TagManager(){
        global $cb_columns, $db;
        
    	$fields = array('videoid', 'title','tags');
    	$cb_columns->object('tagm')->register_columns($fields);
        
        $this->db = $db;
        $this->cb_columns = $cb_columns;
    }
    
    public function getTotal(){
        return $this->total;
    }
    
    public function getMax(){
        return $this->max;
    }
    
    public function getAllTags(){
        $fields = array('video' => $this->cb_columns->object('tagm')->get_columns());
    	$query = 'SELECT '.tbl_fields($fields).' FROM '.tbl('video').' AS video ';
    	$res = select($query);
        
        return $this->tag($res);
    }
    
    public function getTags($page = 1, $length = null){
        if($length !== null)
            $this->max = (int) $length;
        
        $tags = $this->getAllTags();
    	
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $this->max;
        $this->total = count($tags);
        
        return array_slice($tags, $offset, $this->max, true);
    }
    
    public function getTagsByIndex($index = 'A', $page = 1, $length = null){
        if($length !== null)
            $this->max = (int) $length;
        
        $fields = array('video' => $this->cb_columns->object('tagm')->get_columns());
        $query = 'SELECT '.tbl_fields($fields).' FROM '.tbl('video').' AS video WHERE ';
        if($index !== '#'){
            $query .= 'video.tags LIKE "'. $index .'%" OR video.tags LIKE "%,'. $index .'%"';
        } else {
            $query .= 'video.tags REGEXP("^[^A-Za-z]") OR video.tags REGEXP(",[^A-Za-z]")';
        }
        
        $res = select($query);
        $res = $this->tag($res);
        $tags = array();
        foreach($res as $t => $v){
            $regex = $index === '#' ? '#^[^A-Z]#i' : '#^'. $index .'#i';
            if(preg_match($regex, $this->normalize($t))){
                $tags[$t] = $v;
            }
        }
        
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $this->max;
        $this->total = count($tags);
        
        return array_slice($tags, $offset, $this->max, true);
    }
    
    public function searchTag($s){
        $fields = array('video' => $this->cb_columns->object('tagm')->get_columns());
        $query = 'SELECT '.tbl_fields($fields).' FROM '.tbl('video').' AS video WHERE video.tags LIKE "%'. $s .'%"';
        $res = $this->tag(select($query));
        $tags = array();
        
        foreach($res as $t => $v){
            $regex = '#'. $s .'#i';
            if(preg_match($regex, $this->normalize($t))){
                $tags[$t] = $v;
            }
        }
        
        return $tags;
    }
    
    public function getVideos(){
        $fields = array('video' => $this->cb_columns->object('tagm')->get_columns());
        $query = 'SELECT '.tbl_fields($fields).' FROM '.tbl('video').' AS video ORDER BY TRIM(video.title) ASC';
        return select($query);
    }
    
    public function checkExist($t){
        $t = mb_strtolower($t);
        $fields = array('video' => $this->cb_columns->object('tagm')->get_columns());
        $query = 'SELECT video.tags FROM '.tbl('video').' AS video WHERE video.tags like "'. $t .'" OR video.tags LIKE "'. $t .',%" OR video.tags LIKE "%,'. $t .',%" OR video.tags LIKE "%,'. $t .'"';
        $res = select($query);
        foreach($res as $tagsList){
            $tags = explode(',', mb_strtolower($tagsList['tags']));
            if(in_array($t, $tags)){
                return true;
            }
        }
        return false;
    }
    
    public function createTag($tag, $video){
        $tag = trim(mb_strtolower($tag));
        
        $query = 'SELECT video.videoid, video.tags FROM '.tbl('video') .' AS video WHERE video.videoid IN ('. $video .')';
        $rows = select($query);
        foreach($rows as $row){
            $vtags = $row['tags'] !== '' ? $row['tags'] .','. $tag : $tag;
            $this->db->update(tbl('video'), array('tags'), array($vtags), 'videoid = '. $row['videoid']);
        }
        
        return $tag;
    }
    
    public function editTag($old, $new, $video){
        $tag = trim(mb_strtolower($new));
        $query = 'SELECT video.videoid, video.tags FROM '.tbl('video') .' AS video WHERE video.videoid IN ('. $video .')';
        $rows = select($query);
        foreach($rows as $row){
            $tags = explode(',', $row['tags']);
            $newtags = '';
            foreach($tags as $t){
                if($t === $old) $t = $new;
                $newtags .= ','. $t;
            }
            $newtags = substr($newtags, 1);
            $this->db->update(tbl('video'), array('tags'), array($newtags), 'videoid = '. $row['videoid']);
        }
        
        return $tag;
    }
    
    public function deleteTag($tag, $video){
        $tag = trim(mb_strtolower($tag));
        $query = 'SELECT video.videoid, video.tags FROM '.tbl('video') .' AS video WHERE video.videoid IN ('. $video .')';
        $rows = select($query);
        foreach($rows as $row){
            $tags = explode(',', $row['tags']);
            $newtags = '';
            foreach($tags as $t){
                if($t === $tag) continue;
                $newtags .= ','. $t;
            }
            $newtags = substr($newtags, 1);
            $this->db->update(tbl('video'), array('tags'), array($newtags), 'videoid = '. $row['videoid']);
        }
    }
        
    private function tag($vdoTags){
        $tags = array();
        foreach($vdoTags as $r){
            $vtags = explode(',', $r['tags']);
            foreach($vtags as $t){
                $ntag = trim(mb_strtolower($t));
                if(is_numeric($ntag)) $ntag = $ntag .' ';
                if($t !== '')
                    $tags[$ntag][] = $r;
            }
        }
        
        ksort($tags);
        return $tags;
    }
    
    private function normalize($s){
        $table = array(
            'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', 'Ğ'=>'G', 'İ'=>'I', 'Ş'=>'S', 'ğ'=>'g', 'ı'=>'i', 'ş'=>'s', 'ü'=>'u',
            'ă'=>'a', 'Ă'=>'A', 'ș'=>'s', 'Ș'=>'S', 'ț'=>'t', 'Ț'=>'T',
        );
        
        return strtr($s, $table);
    }
}

$tagmquery = new TagManager();
$Smarty->assign_by_ref('tagmquery', $tagmquery);

?>