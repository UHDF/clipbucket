<?php

	require_once '../../includes/admin_config.php';
	require_once OVERLAY_DIR.'/overlay_class.php';

	$userquery->admin_login_check();
	$userquery->login_check('admin_access');
	$pages->page_redir();
	global $overlay;
	global $db;


	if (isset($_POST['action'])){

		switch ($_POST['action']){
			case 'init':
				$overlay->getOverlayHtmlList($_POST['video']);
			break;
			case 'remove':
				$overlay->deleteOverlay($_POST['id']);
			break;
			case 'save':
					$fld = array('id', 'content', 'videoid');

					$value = array('NULL', '{"align": "'.$_POST['ovalign'].'","showBackground": "'.$_POST['ovshowbg'].'","class": "'.$_POST['ovclass'].'","content": "'.htmlspecialchars($_POST['ovcontent']).'","start": "'.$_POST['ovstart'].'","end": "'.$_POST['ovend'].'"}', $_POST['videoid']);

					$overlay->setOverlays($fld, $value);
			break;
			case 'update':
				if (!empty($_POST['content'])){
					for ($i = 0; $i <= count($_POST['content']); $i++){
						$tab = explode("<sep>", $_POST['content'][$i]);
						$overlay->updateOverlay($tab[1], $tab[2], $tab[3], $tab[4], $tab[5], $tab[6], $tab[0]);
						//echo $tab[1];
					}
				}
			break;
		}

	}
