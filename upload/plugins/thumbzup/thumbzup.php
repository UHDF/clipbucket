<?php
/*
Plugin Name: Thumbzup
Description: Simple alternative to the rating system allowing unregistered users to vote
Author: Sylvain Tognola
Author Website: http://stognola.ovh
ClipBucket Version: 2
Version: 1.0
Plugin Type: global

* you must call the anchor "thumbzup_get" on the video page to get the votes
* The ANCHOR "thumbzup_vote_onclick" gives us a javascript to upvote, you can use it that way :
  <a style="cursor:pointer;" onclick="javascript:{ANCHOR place='thumbzup_vote_onclick' data=$video }" target="_blank">...</a>
* you can define a "thumbzup_callback" javascript function to update your view when the user upvoted, for example:
  function thumbzup_callback(nblikes){
    $("#nblikes").html(nblikes);
  }


 _______ _________________ ______ 
|       |                 |      |
|videoid| fingerprint(32) |userid|
|_______|_________________|______|
|   18  |  4de4e4d6e4de   | NULL | ok
|   18  |  4de4e4d6e4de   | 3    | ok 
|   18  |  4de4e4d6e4de   | 1    | ok now remove (18,4de4e4d6e4de, NULL)
|   18  |  4de4e4d6e4de   | 1    | < NO ! you have already voted
|   18  |  4de4e4d6e4de   | NULL | < NO ! registered users voted with this computer
|   18  |  9ac9c9a9c9ac   | 3    | < NO ! you have already voted


*vote*
  the user is registered
    yes:
      (18,*,3) exists ?
        yes: 
          exit already voted
        no :
          (18,4de4e4d6e4de,NULL) exists ? 
            yes: remove that row
          add row (18,4de4e4d6e4de,3)
    no:
      (18,4de4e4d6e4de,*) exists ?
        yes:
          exit can't vote, a registered user already voted with this computer
        no:
          add row (18,4de4e4d6e4de,NULL)
       
       
            
limit of the process : 
  (easy to do) You can vote 2 times if you vote unlogged then logged: 
    simple solution, 1- allow userid=NULL only if no user with this fingerprint already voted, remove userid=NULL entries when a user with the same fingerprint vote 
  (quite easy to overcome) you can vote anytimes you want if you can change the user agent or IP
    simple solution, to complexify script attacks, compute a token with javascript 
provides:
  the ability to vote unregistered even sharing the same ip adress (NAT)
  simple protection against spaming clicks as unregistered
  

      
*/

function thumbzup_vote($videoid){
  global $db;
  $videoid = intval($videoid);
  
  $fingerprint = hash('ripemd128', $_SERVER["REMOTE_ADDR"].$_SERVER ['HTTP_USER_AGENT']);
  $userid = userid();
  $registered = $userid != false;
  
  if($registered){
    $alreadyvoted = $db->select(tbl('thumbzup'),'id',"video_id=$videoid AND user_id=$userid");
    if( count($alreadyvoted) )
      return array(false,"already_voted");  
    else{
      $unregisteredvote = $db->select(tbl('thumbzup'),'id',
        "video_id=$videoid AND user_id IS NULL AND fingerprint='$fingerprint'");
      if(count($unregisteredvote)){
        $db->Execute("delete from ".tbl('thumbzup')." where video_id=$videoid AND fingerprint='$fingerprint' AND user_id is null"); 
        //$db->delete(tbl('thumbzup'),array('video_id','fingerprint','user_id'), array($videoid, $fingerprint, null));
      }
      $db->insert(tbl('thumbzup'), array('video_id','fingerprint','user_id'), array($videoid,$fingerprint,$userid));
      return array(true);
    }
  }
  else{
    $computervote = $db->select(tbl('thumbzup'),'id',"video_id=$videoid AND fingerprint='$fingerprint'");
    if(count($computervote)){
      return array(false,"unregistered_already_voted"); //can't vote, you have already voted or a registered user already voted on this computer
    }
    else{
      $db->insert(tbl('thumbzup'), array('video_id','fingerprint'), array($videoid,$fingerprint));
      return array(true);
    }
  }    
}

function thumbzup_get($videoid){
  $videoid = intval($videoid);
  global $db;
  $thumbzups = $db->select(tbl('thumbzup'),'id',"video_id=$videoid");
  echo count($thumbzups);    
}

function thumbzup_vote_onclick($videoid){
  $videoid = intval($videoid);
  $url = BASEURL."/plugins/thumbzup/thumbzupvote.php?vid=".$videoid;
  echo "\$.get('$url',function(data){if(typeof(thumbzup_callback) == 'function'){thumbzup_callback(data);}});";
}


register_anchor_function('thumbzup_get','thumbzup_get');
register_anchor_function('thumbzup_vote_url','thumbzup_vote_url');
register_anchor_function('thumbzup_vote_onclick','thumbzup_vote_onclick');


