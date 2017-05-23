<?php
/*
 * This file contains TagSelector class
 */ 
require_once PLUG_DIR.'/extend_search/extend_video_class.php';

// Global Object $tagSelector is used in the plugin
$tagSelector = new TagSelector();
$Smarty->assign_by_ref('tagSelector', $tagSelector);


/**
 * Class Containing actions for the TagSelector plugin 
 */
class TagSelector extends CBCategory{
	
	/**
	 * Constructor for TagSelector's instances
	 */
	function TagSelector()	{
	}
	

		
	/**
	 * Get all tags in each video and generate an array with single instance of each one 
	 *
	 * @return array
	 * 		an array of all tags
	 */
	function getAllTags()	{
		$query = " SELECT `tags` FROM ".tbl('video');
		$result = select( $query );
		$output=[];
		$dic=[];
		foreach ($result as $tagstring){
			if ($tagstring["tags"]){
				$tags=explode(',', $tagstring["tags"]);
				foreach ($tags as $tag){
					$dic[$tag]+=1;
				}
			}
		}
		foreach ($dic as $tag=>$nb){
			$tmp=array("name"=>$tag,"count"=>$nb);
			array_push($output,$tmp);
		}
		//return array_keys($dic);
		return $output;
	}

	/**
	 * Get all tags for the specified videoid
	 *
	 *@param int $vid
	 *		The video id
	 * @return array
	 * 		an array of all tags
	 */
	function getTags($vid)	{
		$query = " SELECT `tags` FROM ".tbl('video')." WHERE `videoid`=".$vid;
		$result = select( $query );
		$output=[];
		$dic=[];
		foreach ($result as $tagstring){
			if ($tagstring["tags"]){
				$tags=explode(',', $tagstring["tags"]);
				foreach ($tags as $tag){
					$dic[$tag]+=1;
				}
			}
		}
		foreach ($dic as $tag=>$nb){
			$tmp=array("name"=>$tag,"count"=>$nb);
			array_push($output,$tmp);
		}
		//return array_keys($dic);
		return $output;
	}
	
	
	
}

?>