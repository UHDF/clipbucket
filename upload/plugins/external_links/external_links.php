<?php
/*
 Plugin Name: External links
 Description: This plugin will add external links to a video.
 Author: Franck Rouze
 Author Website: http://semm.univ-lille1.fr/
 ClipBucket Version: 2.8
 Version: 1.0
 Website:
 */
require_once 'link_class.php';
if (!$cbplugin->is_installed('common_library.php'))
	e(sprintf(lang("plugin_not_installed : %s"),"Common Library"));
else
	require_once PLUG_DIR.'/common_library/common_library.php';

// Define Plugin's uri constants
define("SITE_MODE",'/admin_area');
define('LINK_BASE',basename(dirname(__FILE__)));
define('LINK_DIR',PLUG_DIR.'/'.LINK_BASE);
define('LINK_URL',PLUG_URL.'/'.LINK_BASE);
define('LINK_ADMIN_DIR',LINK_DIR.'/admin');
define('LINK_ADMIN_URL',LINK_URL.'/admin');
define("LINK_EDITPAGE_URL",BASEURL.SITE_MODE."/plugin.php?folder=".LINK_BASE."/admin&file=edit_link.php");
assign("link_editpage",LINK_EDITPAGE_URL);
define("LINK_MANAGEPAGE_URL",BASEURL.SITE_MODE."/plugin.php?folder=".LINK_BASE."/admin&file=manage_links.php");
assign("link_managepage",LINK_MANAGEPAGE_URL);
define("LINK_LINKPAGE_URL",BASEURL.SITE_MODE."/plugin.php?folder=".LINK_BASE."/admin&file=link_links.php");
assign("link_linkpage",LINK_LINKPAGE_URL);


if(!function_exists('externalLinkList')){
	/**
	 * Define the Anchor to display links into description of a video main page
	 * 
	 * @param array $data
	 * 		a dictionary containing information about the requested documents
	 * 	@see Link.getLinkForVideo() function for more details
	 */
	function externalLinkList($data){
		global $linkquery;
		$data["selected"]="yes";
		$lnks=$linkquery->getLinkForVideo($data);
		$str='';
		foreach ($lnks as $lnk) {
			$str.='<li><a target="_blank" href="'.$lnk['url'].'">'.$lnk['title'] .'</a></li>'; 
		}
		echo $str;	
	}
	// use {ANCHOR place="externalLinkList" data=$video} to display the formatted list above
	register_anchor_function('externalLinkList','externalLinkList');
}	

if(!function_exists('externalLinkCount')){
	/**
	 * Get external links count for the current video
	 *
	 * This function is registrered in smarty to be used directly into the template
	 * @return int
	 * 		the number of external urls linked to the current video
	 * @see Document.getLinkForVideo() function for more details
	 */
	function externalLinkCount(){
		global $Smarty;
		global $linkquery;
		global $db;
		if ($_GET["v"]){
			$vid=$_GET["v"];
			$result=$db->_select("SELECT `videoid` FROM ".tbl('video')." WHERE `videokey`='".$vid."'");
			if (count($result)==1) $vid=$result[0]['videoid'];
			$data=["videoid"=> $vid, "selected" => "yes","count_only"=>True];
			$cnt=$linkquery->getLinkForVideo($data);
			return intval($cnt);
		}
		else return 0;
	}
	global $Smarty;
	$Smarty->register_function('externalLinkCount','externalLinkCount');
}

/**
 * Remove associate between any external links and a video
 *
 * @param int $vid
 * 		the video's id
 */
function unlinksAllLinks($vid){
	global $linkquery;
	if(is_array($vid))
		$vid = $vid['videoid'];
		$linkquery->unlinkAllLinks($vid);
}

/** Remove external links associated a video when video is deleted */
register_action_remove_video("unlinksAllLinks");


/**
 * Add a new entry "Link external link" into the video manager menu named "Actions" associated to each video
 * 
 *  @param int $vid 
 *  	the video id
 *  @return string
 *  	the html string to be inserted into the menu
 */
function addExternalLinkMenuEntry($vid){
	$idtmp=$vid['videoid'];
	return '<li><a role="menuitem" href="'.LINK_LINKPAGE_URL.'&video='.$idtmp.'">'.lang("link_external_link").'</a></li>';
}
if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("links")]=='yes')
	$cbvid->video_manager_link[]='addExternalLinkMenuEntry';

/**
 * Add entries for the plugin in the administration pages
 */
if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("links")]=='yes')
	add_admin_menu(lang('video_addon'),lang('external_links_manager'),'manage_links.php',LINK_BASE.'/admin');

/**
 * insert js code into the HEADER of the edit_video.php page
 */
if ($cbplugin->is_installed('common_library.php') &&
		$userquery->permission[getStoredPluginName("links")]=='yes' &&
		substr($_SERVER['SCRIPT_NAME'], -14, 14) == "edit_video.php"){
			assign("videoid",$_GET['video']);
			$Cbucket->add_admin_header(PLUG_DIR . '/external_links/admin/header.html', 'global');
			
	register_anchor_function('addNavTabExternalLinks', 'vidm_navtab');
	register_anchor_function('addPanelExternalLinks', 'vidm_tabcontent');
	register_anchor_function('addAfterFormExternalLinks', 'vidm_afterForm');

	$plgextlk = filter_input(INPUT_POST, 'plgextlinks');
	if($plgextlk){
		$links = !empty($_POST['extlinks']) ? $_POST['extlinks'] : array();
		$linkquery->unlinkAllLinks($_GET['video']);
		foreach($links as $l){
			$linkquery->linkLink($l, $_GET['video']);
		}
	}
}

function addNavTabExternalLinks(){
    echo '<li role="presentation"><a href="#extlinks-panel" aria-controls="required" role="tab" data-toggle="tab">'. lang('external_links') .'</a></li>';
}
	
function addPanelExternalLinks(){
    global $linkquery, $Smarty;
    $links = $linkquery->getLinkForVideo(array('selected' => 'yes', 'videoid' => $Smarty->get_template_vars('videoid'), 'order' => 'links.title ASC'));
    
    echo '
                    <div id="extlinks-panel" role="tabpanel" class="tab-pane">
                        <label for="extlinks-related">'. lang('extlink_linked') .'</label> 
                        <button type="button" class="btn btn-xs btn-primary" id="btnAddExtLink" data-toggle="modal" data-target="#addExtLinkModal">'. lang('link_external_link') .'</button>
                        <a class="btn btn-xs btn-primary" id="btnCreateExtLink" target="_blank" href="'. LINK_MANAGEPAGE_URL .'">'. lang('extlink_create') .'</a>
                        <table class="table table-striped">';
    foreach($links as $l){
        echo '
                            <tr>
                                <td class="extLinkAction">
                                    <button class="btn deleteExtLink" type="button" title="'. lang('extlink_unlink') .'"><span class="glyphicon glyphicon-remove"></span></button>
                                    <input type="hidden" name="extlinks[]" value="'. $l['id'] .'" />
                                </td>
                                <td><a href="'. $l['url'] .'" target="_blank">'. $l['title'] .'</a></td>
                            </tr>
            ';
    }
    echo '
                        </table>
                        <input type="hidden" name="plgextlinks" value="update" />
                    </div>
        ';
}	

function addAfterFormExternalLinks(){
?>
<div class="modal fade" tabindex="-1" role="dialog" id="addExtLinkModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
			<form method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo lang('link_external_link'); ?></h4>
				</div>
				<div class="modal-body">
                    <div class="form-group">
                        <select name="selectedExtLinks[]" id="selectedExtLinks" class="form-control"></select>
                    </div>
				</div>
				<div class="modal-footer">
					<div class="text-right">
                        <button type="button" class="btn btn-default btn-xs extlinkmCancelModal" data-dismiss="modal"><?php echo lang('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary btn-xs extlinkmValidate"><?php echo lang('validate'); ?></button>
                    </div>
				</div>
			</form>
        </div>
    </div>
</div>

<script src="<?php echo LINK_URL; ?>/admin/mgsg/magicsuggest-min.js"></script>
<script type="text/javascript">
    $(function(){  
		var extlkOpt;
		
        $('#extlinks-panel').on('click', 'button.deleteExtLink', function(e){
            $(e.currentTarget).closest('tr').remove();
        });
        
        var msextlk = $('#selectedExtLinks').magicSuggest({
            allowFreeEntries: false,
            noSuggestionText: '<?php echo lang('extlink_NoResultForQuery'); ?> : <strong>{{query}}</strong>',
            placeholder: '',
            toggleOnClick: true
        });	
        
        $('#addExtLinkModal').on('show.bs.modal', function(e){
            msextlk.setSelection([]);
            
            extlkOpt = '';
            $('#extlinks-panel table input[name="extlinks[]"]').each(function(e){
                extlkOpt += ','+ $(this).val();
            });
            if(extlkOpt !== '') extlkOpt = extlkOpt.substring(1);
			
            $.ajax({
               url: '<?php echo LINK_URL .'/action.php' ?>',
               method: 'POST',
               data: {getOpt: extlkOpt}
            }).done(function(msg){
				if(msg === '') return;
                extlkOpt = $.parseJSON(msg);
                msextlk.setData(extlkOpt);
            });
        });
        
        $('#addExtLinkModal form').submit(function(e){
            var opt = msextlk.getValue();
            $.each(extlkOpt, function(index, l){
                if($.inArray(l.id, opt) !== -1){
                    var resHtml = '<tr><td class="extLinkAction">';
                    resHtml += '<button class="btn deleteExtLink" type="button" title="<?php echo lang('extlink_unlink') ?>"><span class="glyphicon glyphicon-remove"></span></button>';
                    resHtml += '<input type="hidden" name="extlinks[]" value="'+ l.id +'" />';
                    resHtml += '</td>';
                    resHtml += '<td><a href="'+ l.href +'" target="_blank">'+ l.name +'</a></td></tr>';
                    $('#extlinks-panel table').append(resHtml);
                }
            });
            
            $('#addExtLinkModal').modal('hide');
            return false; 
        });
    });
</script>

<?php
}