<?php
/**
 * This File contains a class that extends cbsearch in order to be able to search a video using multiple instance of cbsearch or cbsearch children.
 * It agreagate all result in one
 */
class MultiSearch extends cbsearch {
	var $searchObjects=[];	
	
	function addSearchObject($obj){
		global ${$obj};
		$this->searchObjects[]=${$obj};
	}
	
	/**
	 * Filter word from the search edit line to remove unwanted terms
	 * For this moment all unwanted words are french one.
	 * @todo : Externalise the word into a database table in order to edit the list and localise words
	 */
	function filterWord($word){
		$stopwords=['alors','au','aucuns','aussi','autre','avant','avec','avoir','bon','car','ce','cela','ces','ceux','chaque','ci','comme',
				'comment','dans','de','des','du','dedans','dehors','depuis','devrait','doit','donc','dos','début','elle','elles','en','encore',
				'essai','est','et','eu','fait','faites','fois','font','hors','ici','il','ils','je 	juste','la','le','les','leur','là',
				'ma','maintenant','mais','mes','mine','moins','mon','mot','même','ni','nommés','notre','nous','ou','où','par','parce',
				'pas','peut','peu','plupart','pour','quand','que','quel','quelle','quelles','quels','qui','sa','sans','ses',
				'seulement','si','sien','son','sont','sous','soyez 	sujet','sur','ta','tandis','tellement','tels','tes','ton','tous',
				'tout','trop','très','tu','voient','vont','votre','vous','vu','ça','étaient','état','étions','été','être'];
		return in_array($word, $stopwords) || strlen($word)<2;
	}
	
	/**
	 * Run the database search request. 
	 * This search engine is quite complex. The target is to search multiple words separatly in different search engine and return the most relevant result, 
	 * ordered by number of words found and then by reverse event date
	 */
	 function search(){
	 	global $db;
	 	$results=[];  // this array will store all video ids found (no matter the word searched nor the search engine used)
	 	$resultByWord=[]; // this array wiil store all videos found word by word
	 	$this->total_results=0;
	 	$nword=0; //number of effective searched word (after filtering and without many occurence of the same word)
	 	$searchkeys=strtolower($this->key);
	 	$keys=array_unique(explode(" ", $searchkeys));
	 	//run the research on each word separatly
	 	foreach ($keys as $akey){
	 		if (!$this->filterWord($akey)) {
	 			$nword++;
		 		$this->key=$akey;
		 		$resultByWord[$akey]=array();
			 	// First search with all connected search engine.
				foreach ($this->searchObjects as $obj){
					$obj->search->key=$this->key;
						
					// Remove each search engine limit to get all results and set the limit later
					$obj->search->limit="";
					// Concatenate all search results in one
					$return=$obj->search->search();
					// for each search word store the video ids found
					foreach ($return as $videoentry){
						$resultByWord[$akey][] = $videoentry["videoid"];
						$results[]=$videoentry["videoid"];
					}
					$this->total_results+= $obj->search->total_results;
			 	}
			 	$resultByWord[$akey]=array_unique($resultByWord[$akey]);
	 		}
	 	}
	 	$results=array_unique($results);
	 	$strContat="";
	 	foreach ($resultByWord as $tabTemp){
	 		$strContat.="#".implode("#", $tabTemp)."#";
	 	}
	 	 
	 	$occurs=[];
	 	foreach ($results as $tmpvid){
	 		$occurs[substr_count($strContat, "#".$tmpvid."#")][]=$tmpvid;
	 	}
	 	$finalresult=[];
	 	$total=0;
	 	for ($ii=1; $ii<=$nword; $ii++){
	 		$videoids=implode(",",$occurs[$ii]);
	 		if ($videoids!=""){
	 			// Get result without duplicate. Data is orderd and only one page is returned
	 			$results=$db->_select("SELECT * from ".tbl("video")." WHERE `videoid` IN (".$videoids.") ORDER BY `datecreated` ASC ");
	 		
	 			// Get the total number of results in order to paginate
	 			$total+=count($occurs[$ii]);
	 			// Reverse the result because it's reversed again in search-result.php then the result is in right order in the page.
	 			$results=array_reverse($results);
		 		$finalresult=array_merge($results,$finalresult);
	 		}
	 	}
	 	$this->total_results =$total;
	 	if ($this->limit){
	 		$limits=explode(',',$this->limit);
	 		$finalresult=array_slice($finalresult, $limits[0],$limits[1]);
	 		$finalresult=array_reverse($finalresult);
	 		 }
	 	return $finalresult;
	}
}

?>