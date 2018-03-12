<?php 
function uninstall_thumbzup() {
  global $db;
  $db->Execute("DROP TABLE ".tbl('thumbzup'));
}

/** install the plugin */
uninstall_thumbzup()
?>
