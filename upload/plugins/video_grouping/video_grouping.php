<?php
/*
Plugin Name: Video Grouping
Description: This plugin will add a generic video grouping functionnality (discipline, type, category, collection, series...)
Author: Franck Rouze
Author Website: http://semm.univ-lille1.fr/
ClipBucket Version: 2.8.1
Version: 1.0
Website: http://clip-bucket.com/plugin-page
*/

// Define Plugin's uri constants
define('VIDEO_GROUPING_BASE',basename(dirname(__FILE__)));
define('VIDEO_GROUPING_DIR',PLUG_DIR.'/'.VIDEO_GROUPING_BASE);
define('VIDEO_GROUPING_URL',PLUG_URL.'/'.VIDEO_GROUPING_BASE);
define('VIDEO_GROUPING_ADMIN_DIR',VIDEO_GROUPING_DIR.'/admin');
define('VIDEO_GROUPING_ADMIN_URL',VIDEO_GROUPING_URL.'/admin');
define("SITE_MODE","/admin_area");
define("VIDEO_GROUPINGS_MANAGE_PAGE_URL",BASEURL.SITE_MODE."/plugin.php?folder=".VIDEO_GROUPING_BASE."/admin/&file=manage_video_grouping.php");
assign("video_grouping_manage_page",VIDEO_GROUPINGS_MANAGE_PAGE_URL);
define("VIDEO_GROUPING_LINKPAGE_URL",BASEURL.SITE_MODE."/plugin.php?folder=".VIDEO_GROUPING_BASE."/admin&file=link_video_grouping.php");
assign("video_grouping_linkpage",VIDEO_GROUPING_LINKPAGE_URL);
define('VIDEO_GROUPING_UPLOAD',BASEDIR."/files/thumbs/video_grouping");
assign("video_grouping_thumbdir",BASEURL."/files/thumbs/video_grouping");

require_once VIDEO_GROUPING_DIR.'/video_grouping_class.php';

global $cbvid;

// Declare the function only once
if(!function_exists("videoGroupingMenuOutput")) {
	/**
	 * Create anchors that populate the template header menu with video grouping types menues
	 * 
	 * For each grouping type menu the function add all grouping marked as 'in_menu' 
	 *
	 * @uses
	 * 		add {ANCHOR place="groupingMenuOutput"} to display the menu extension
	 */
	function groupingMenuOutput($id){
		global $videoGrouping;
		$gt=$videoGrouping->getGroupingType($id);
		$count=$videoGrouping->countGroupingsOfType($id,false);
		$result = $videoGrouping->getGroupingsOfType($id,true);
		$txt = "";
		foreach($result as $grp){
			$str=$grp['name'];
			if (strlen($str)>30) {
				$str=substr($str, 0,30)."...";
			}
			$txt .=  "<li><a href=\"".BASEURL."/search_result.php?type=videogrouping&query=".$grp['name']."&gtype=".$grp['grouping_type_id']."\">".$str."</a></li>";
		}
		if ($count>sizeof($result)){
			$txt .=  "<li><a href=\"".BASEURL."/search_result.php?type=videogrouping&query=".$gt["name"]."\">Toutes ... </a></li>";
		}
		echo $txt;
	}
	register_anchor_function("groupingMenuOutput","groupingMenuOutput");
}

// Declare the function only once
if(!function_exists("groupingThumbOutput")) {
	/**
	 * Create anchors that display hyperlinks on video grouping in each video thumb
	 * 
	 * the groupings added are only thoses that are marked as in_thumbnail in the grouping manage page
	 * 
	 * @param int $id
	 * 		The video id
	 * @uses
	 * 		add {ANCHOR place="groupingThumbOutput" data=$video.videoid} to display the link in yout video thumbs
	 */
	function groupingThumbOutput($id){
		global $videoGrouping;
		$res = $videoGrouping->getGroupingOfVideo($id);
		$txt="";
		foreach ($res as $r){
			if ($r['in_thumb'])
				$txt.= '<a href="'.BASEURL."/search_result.php?type=videogrouping&query=".$r['name'].'&gtype='.$r['grouping_type_id'].'" style="color:'.$r['color'].';border-color:'.$r['color'].'">'.$r['name'].'</a>';	
				}
		echo $txt;
	}
	register_anchor_function("groupingThumbOutput","groupingThumbOutput");
}


/**
 * Add as many labels as grouping linked to the video to display in the Video Manager page the groupings of a video.
 * 
 * @param array $vid
 * 		the selected video object 
 * @return string
 * 		A concatenated html <span> containing groupings linked to the video
 */
function displayGroupingName($vid){
	global $videoGrouping;
	$grps = $videoGrouping->getGroupingOfVideo($vid['videoid']);
	$str="";
	foreach ($grps as $g)
		$str.='<span class="label label-default">'.$g['vdogroupingtype_name']." : ".$g['name'].'</span> ';
	return $str;
}
$cbvid->video_manager_link_new[] = 'displayGroupingName';

/**
 * Remove associate between any grouping and a video
 *
 * @param int $vid
 * 		the video's id
 */
function unlinksGroupings($vid){
	global $videoGrouping;
	if(is_array($vid))
		$vid = $vid['videoid'];
		$videoGrouping->unlinkAllGrouping($vid);
}

/** Remove all groupings associated a video when video is deleted */
register_action_remove_video("unlinksGroupings");


/**
 * Add a new entry "Link video grouping" into the video manager menu named "Actions" associated to each video
 *
 *  input $vid : the video id
 *  output : the html string to be inserted into the menu
 */
function addLinkVideoGroupingMenuEntry($vid){
        $idtmp=$vid['videoid'];
        return '<li><a role="menuitem" href="'.VIDEO_GROUPING_LINKPAGE_URL.'&video='.$idtmp.'">'.lang("link_video_grouping").'</a></li>';
}
/** Add the previous function in the list of entries into the video manager "Actions" button */
if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("videogrouping")]=='yes')
        $cbvid->video_manager_link[]='addLinkVideoGroupingMenuEntry';

/**Add entries for the plugin in the administration pages */
if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("videogrouping")]=='yes')
	add_admin_menu(lang('video_addon'),lang("manage_video_grouping"),'manage_video_grouping.php','video_grouping/admin/');

/**
 * insert js code into the HEADER of the edit_video.php page
 */
if ($cbplugin->is_installed('common_library.php') &&
		$userquery->permission[getStoredPluginName("videogrouping")]=='yes' &&
		substr($_SERVER['SCRIPT_NAME'], -14, 14) == "edit_video.php"){
	assign("videoid",$_GET['video']);
	$Cbucket->add_admin_header(PLUG_DIR . '/video_grouping/admin/header2.html', 'global');
	
	register_anchor_function('addNavTabVidGrp', 'vidm_navtab');
	register_anchor_function('addPanelVidGrp', 'vidm_tabcontent');
	register_anchor_function('addAfterFormVidGrp', 'vidm_afterForm');

	$plgvidgrp = filter_input(INPUT_POST, 'plgvidgrp');
	if($plgvidgrp){
		$groupes = !empty($_POST['vidgrp']) ? $_POST['vidgrp'] : array();
		$videoGrouping->unlinkAllGrouping($_GET['video']);
		foreach($groupes as $g){
			$videoGrouping->linkGrouping($g, $_GET['video']);
		}
	}
}

function addNavTabVidGrp(){
    echo '<li role="presentation"><a href="#vidgrp-panel" aria-controls="required" role="tab" data-toggle="tab">'. lang('grouping') .'</a></li>';
}
	
function addPanelVidGrp(){
    global $videoGrouping, $Smarty;
    $groupes = $videoGrouping->getGroupingForVideo(array('selected' => 'yes', 'videoid' => $Smarty->get_template_vars('videoid'), 'order' => 'vdogrouping_type.name ASC, vdogrouping.name ASC'));
	$type = '';
    echo '
                    <div id="vidgrp-panel" role="tabpanel" class="tab-pane">
                        <label for="vidgrp-related">'. lang('vidgrp_linked') .'</label> 
                        <button type="button" class="btn btn-xs btn-primary" id="btnAddVidgrp" data-toggle="modal" data-target="#addVidgrpModal">'. lang('link_video_grouping') .'</button>
                        <a class="btn btn-xs btn-primary" id="btnCreateVidgrp" target="_blank" href="'. VIDEO_GROUPINGS_MANAGE_PAGE_URL .'">'. lang('vidgrp_create') .'</a>';
	
    foreach($groupes as $g){
		if($type !== $g['vdogroupingtype_name']){
			if($type !== '') 
				echo '
							</table>
						</div>';
			
			$type = $g['vdogroupingtype_name'];
			echo ' 
						<div id="type_'. $g['grouping_type_id'] .'">
							<h4>'. $type .'</h4>
							<table class="table table-striped">';	
		}
        echo '
								<tr>
									<td class="vidgrpAction">
										<button class="btn deleteVidgrp" type="button" title="'. lang('vidgrp_unlink') .'"><span class="glyphicon glyphicon-remove"></span></button>
										<input type="hidden" name="vidgrp[]" value="'. $g['id'] .'" />
									</td>
									<td>'. $g['name'] .'</td>
								</tr>
            ';
    }
	
	if($type !== '') echo ' 
							</table>
						</div>';
	
    echo '
                        <input type="hidden" name="plgvidgrp" value="update" />
                    </div>
        ';
}	

function addAfterFormVidGrp(){
?>
<div class="modal fade" tabindex="-1" role="dialog" id="addVidgrpModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
			<form method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo lang('link_video_grouping'); ?></h4>
				</div>
				<div class="modal-body">
                    <div class="form-group">
						<label class="control-label" for="selectTypegrp"><?php echo lang('grouping_type'); ?></label>
                        <select name="selectTypegrp" id="selectTypegrp" class="form-control"></select>
                    </div>
					<div class="form-group vidgrp">
						<label class="control-label" for="selectVidgrp"><?php echo lang('grouping'); ?></label>
                        <select name="selectVidgrp[]" id="selectVidgrp" class="form-control"></select>
                    </div>
				</div>
				<div class="modal-footer">
					<div class="text-right">
                        <button type="button" class="btn btn-default btn-xs vidgrpmCancelModal" data-dismiss="modal"><?php echo lang('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary btn-xs vidgrpmValidate"><?php echo lang('validate'); ?></button>
                    </div>
				</div>
			</form>
        </div>
    </div>
</div>

<script src="<?php echo VIDEO_GROUPING_ADMIN_URL; ?>/mgsg/magicsuggest-min.js"></script>
<script type="text/javascript">
    $(function(){  
		var vidgrpOpt;
		
        $('#vidgrp-panel').on('click', 'button.deleteVidgrp', function(e){
			var t = $(e.currentTarget).closest('table');
			if(t.find('tr').length > 1){
				$(e.currentTarget).closest('tr').remove();
			} else {
				t.closest('div').slideUp('fast', function(){ this.remove(); });
			}
        });
        
        var mstypegrp = $('#selectTypegrp').magicSuggest({
            allowFreeEntries: false,
            noSuggestionText: '<?php echo lang('vidgrp_NoResultForQuery'); ?> : <strong>{{query}}</strong>',
			maxSelection: 1,
			maxSelectionRenderer: function(v){ return ''; },
            placeholder: '',
			sortOrder: 'name',
            toggleOnClick: true
        });	
	
		var msvidgrp = $('#selectVidgrp').magicSuggest({
            allowFreeEntries: false,
            noSuggestionText: '<?php echo lang('vidgrp_NoResultForQuery'); ?> : <strong>{{query}}</strong>',
			maxSelection: 1,
			maxSelectionRenderer: function(v){ return ''; },
            placeholder: '',
			sortOrder: 'name',
            toggleOnClick: true
        });
		
		var vidgrpLoadSelect = function($obj){
			msvidgrp.setSelection([]);
            
            vidgrpOpt = '';
            $('#vidgrp-panel table input[name="vidgrp[]"]').each(function(e){
                vidgrpOpt += ','+ $(this).val();
            });
            if(vidgrpOpt !== '') vidgrpOpt = vidgrpOpt.substring(1);
			
			var d = {getOpt: vidgrpOpt, entity: $obj};
			if($obj === 'groupe') d.type = mstypegrp.getValue()[0];
			
            $.ajax({
               url: '<?php echo VIDEO_GROUPING_URL .'/action.php' ?>',
               method: 'POST',
               data: d
            }).done(function(msg){
				if(msg === '') return;
                vidgrpOpt = $.parseJSON(msg);
                if($obj === 'type'){
					mstypegrp.setData(vidgrpOpt);
				} else if($obj === 'groupe'){
					if(vidgrpOpt.length) $('#addVidgrpModal .form-group.vidgrp').slideDown('fast');
					msvidgrp.setData(vidgrpOpt);
				}
            });
		};
        
        $('#addVidgrpModal').on('show.bs.modal', function(e){
            mstypegrp.setSelection([]);
			vidgrpLoadSelect('type');
        });
		
		$(mstypegrp).on('selectionchange', function(e, m){
			if(m.getValue().length){
				vidgrpLoadSelect('groupe');
			} else{
				$('#addVidgrpModal .form-group.vidgrp').slideUp('fast', function(){
					msvidgrp.setData([]);
					msvidgrp.clear();
				});
			}
		});
        
        $('#addVidgrpModal form').submit(function(e){
			var t = mstypegrp.getSelection();
			var g = msvidgrp.getSelection();
			
			if(t.length === 1 && g.length === 1){
				var resHtml = '<tr><td class="vidgrpAction">';
				resHtml += '<button class="btn deleteVidgrp" type="button" title="<?php echo lang('vidgrp_unlink') ?>"><span class="glyphicon glyphicon-remove"></span></button>';
				resHtml += '<input type="hidden" name="vidgrp[]" value="'+ g[0].id +'" /></td>';
				resHtml += '<td>'+ g[0].name +'</td></tr>';
				
				if($('#vidgrp-panel #type_'+ t[0].id).length){
					$('#vidgrp-panel #type_'+ t[0].id +' table').append(resHtml);
				} else {
					resHtml = '<div id="type_'+ t[0].id +'"><h4>'+ t[0].name +'</h4><table class="table table-striped">'+ resHtml +'</table>';
					$('#vidgrp-panel').append(resHtml);
				}
				
				$('#addVidgrpModal').modal('hide');
			}

            return false; 
        });
    });
</script>

<?php
}

global $videoGrouping;
Assign("videoGrouping", $videoGrouping)
	
?>