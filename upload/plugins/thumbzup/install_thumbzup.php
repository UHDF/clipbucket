<?php

function install_thumbzup() {
	global $db;
	$db->Execute(
  'CREATE TABLE IF NOT EXISTS '.tbl("thumbzup").' (
    `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `video_id` bigint(20) NOT NULL,
    `fingerprint` varchar(32) NOT NULL ,
    `user_id` bigint(20)  DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;;'
  );
  
  //TODO: add constraints
}

/** install the plugin */
install_thumbzup()
?>
