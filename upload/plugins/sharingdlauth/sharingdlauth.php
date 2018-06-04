<?php
/*
 Plugin Name: Sharing / Download authorization
 Description: Adds authorizations to share and/or download videos with the video manager plugin
 Author: Bastien Poirier
 Author Website: http://semm.univ-lille1.fr/
 ClipBucket Version: 2.8
 Version: 1.0
 Website:
 */

require_once 'sharingdlauth_class.php';

global $cbplugin;
if (!$cbplugin->is_installed('common_library.php'))
	e(sprintf(lang("plugin_not_installed : %s"),"Common Library"));
else
	require_once PLUG_DIR.'/common_library/common_library.php';

if(!function_exists('shdlauth_sh')){

	/*function shdlauth_sh($video){
		global $shdlauthquery;
		
		return $shdlauthquery->canDl(filter_input(INPUT_GET, 'video'))	
	}
	
	register_anchor_function('speakerList','speakerList');*/
}

if($shdlauthquery->isActivated()){
	if($_SERVER['PHP_SELF'] === '/admin_area/edit_video.php'){
		$Cbucket->add_admin_header(PLUG_DIR . '/sharingdlauth/admin/header.html');
		register_anchor_function('addShDlAuth', 'vidm_sharing_before_link_video');
		
		$shdlupd = filter_input(INPUT_POST, 'shdlauthupdate');
		if($shdlupd !== null){
			$shdlauthquery->update(filter_input(INPUT_GET, 'video'));
		}
	}
	
	if(THIS_PAGE === 'watch_video'){
		$shdlauth_v = @$_GET['v'];
		if(intval($Cbucket->configs['video_download'])){
			$Cbucket->configs['video_download'] = $shdlauthquery->canDl($shdlauth_v) ? '1' : '0';
		}
		
		if(!$shdlauthquery->canShare($shdlauth_v)) $Cbucket->add_header(PLUG_DIR . '/sharingdlauth/header.html');
	}
}



function addShDlAuth(){
	global $shdlauthquery, $Smarty;
	$v = $shdlauthquery->getVideo($Smarty->get_template_vars('videoid'));
?>
	<div id="shdlauth_container">
		<div class="form-group" id="sharing_authorization_container">
			<label for="sharing_authorization_yes"><?= lang('shdlauth_SharingAuthorization') ?></label> 
			<label class="radio-inline"><input type="radio" name="sharing_authorization" id="sharing_authorization_yes" value="1"<?= $v['authsharing'] === '1' ? ' checked' : '' ?>><?= lang('shdlauth_Yes') ?></label>
			<label class="radio-inline"><input type="radio" name="sharing_authorization" value="0"<?= $v['authsharing'] === '0' ? ' checked' : '' ?>><?= lang('shdlauth_No') ?></label>
		</div>

		<div class="form-group" id="dl_authorization_container">
			<label for="dl_authorization_yes"><?= lang('shdlauth_DlAuthorization') ?></label> 
			<label class="radio-inline"><input type="radio" name="dl_authorization" id="dl_authorization_yes" value="1"<?= $v['authdl'] === '1' ? ' checked' : '' ?>><?= lang('shdlauth_Yes') ?></label>
			<label class="radio-inline"><input type="radio" name="dl_authorization" value="0"<?= $v['authdl'] === '0' ? ' checked' : '' ?>><?= lang('shdlauth_No') ?></label>
		</div>
		<input type="hidden" name="shdlauthupdate" />
	</div>
<?php
}
?>