<?php
/*
 Plugin Name: Video Speaker
 Description: This plugin will add a list of video speakers to a video with their specific role in the video.
 Author: Franck Rouze
 Author Website: http://semm.univ-lille1.fr/
 ClipBucket Version: 2.8
 Version: 1.0
 Website:
 */
require_once 'speaker_class.php';
global $cbplugin;
if (!$cbplugin->is_installed('common_library.php'))
	e(sprintf(lang("plugin_not_installed : %s"),"Common Library"));
else
	require_once PLUG_DIR.'/common_library/common_library.php';

if (!$cbplugin->is_installed('extend_search.php'))
	e(sprintf(lang("plugin_not_installed : %s"),"Extended  Search"));
else
	require_once PLUG_DIR.'/extend_search/extend_search.php';
		

/**
 * Define Plugin's uri constants. These constants represents folders or urls
 */
define("SITE_MODE",'/admin_area');
define('SPEAKER_BASE',basename(dirname(__FILE__)));
define('SPEAKER_DIR',PLUG_DIR.'/'.SPEAKER_BASE);
define('SPEAKER_URL',PLUG_URL.'/'.SPEAKER_BASE);
define('SPEAKER_ADMIN_DIR',SPEAKER_DIR.'/admin');
define('SPEAKER_ADMIN_URL',SPEAKER_URL.'/admin');
define("SPEAKER_MANAGEPAGE_URL",BASEURL.SITE_MODE."/plugin.php?folder=".SPEAKER_BASE."/admin&file=manage_speakers.php");
assign("speaker_managepage",SPEAKER_MANAGEPAGE_URL);
define("SPEAKER_LINKPAGE_URL",BASEURL.SITE_MODE."/plugin.php?folder=".SPEAKER_BASE."/admin&file=link_speaker.php");
assign("speaker_linkpage",SPEAKER_LINKPAGE_URL);


// Connect the speaker search engine to the mulitisearch object in order to extend the relust of the video search result to speakers.
if ($cbplugin->is_installed('extend_search.php')) { 
	global $multicategories;
	$multicategories->addSearchObject("speakerquery");
}
$Cbucket->search_types['speaker'] = "speakerquery";


if(!function_exists('speakerList')){
	/**
	 * Define the Anchor to display speakers into description of a video main page
	 */
	function speakerList($data){
		global $speakerquery;
		$data["selected"]="yes";
		$spk=$speakerquery->getSpeakerAndRoles($data);
		$str='';
		foreach ($spk as $sp) {
			$url=BASEURL.'/'.'search_result.php?type=speaker&query='.$sp['slug'];
			$str.='<li><a href="'.$url.'">'.$sp['firstname'] .' '. $sp['lastname'].'</a><span>,'.$sp['description'].'</span></li>'; 
		}
		echo $str;	
	}
	// use {ANCHOR place="speakerList" data=$video} to display the formatted list above
	register_anchor_function('speakerList','speakerList');
}	

if(!function_exists('speakerCount')){
	/**
	 * Get speakers count for the current video
	 *
	 * This function is registrered in smarty to be used directly into the template
	 * @return int
	 * 		the number of speakers linked to the current video
	 * @see Document.getSpeakerAndRoles() function for more details
	 */
	function speakerCount(){
		global $Smarty;
		global $speakerquery;
		global $db;
		if ($_GET["v"]){
			$vid=$_GET["v"];
			$result=$db->_select("SELECT `videoid` FROM ".tbl('video')." WHERE `videokey`='".$vid."'");
			if (count($result)==1) $vid=$result[0]['videoid'];
			$data=["videoid"=> $vid, "selected" => "yes","count_only"=>True];
			$cnt=$speakerquery->getSpeakerAndRoles($data);
			return intval($cnt);
		}
		else return 0;
	}
	global $Smarty;
	$Smarty->register_function('speakerCount','speakerCount');
}


/**
 * Connect the plugin to the video manager
 * 
 * Add a new entry "Link speaker" into the video manager menu named "Actions" for each video
 * 
 *  @param CBvideo $vid 
 *  	the CBVideo object returned by the video manager when senected "Actions" on a specific video
 *  @return  string
 *  	the html string to be inserted into the menu
 */
function addLinkSpeakerMenuEntry($vid){
	$idtmp=$vid['videoid'];
	return '<li><a role="menuitem" href="'.SPEAKER_LINKPAGE_URL.'&video='.$idtmp.'">'.lang("speaker_link").'</a></li>';
}

/** Add the previous function in the list of entries into the video manager "Actions" button */
if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("speaker")]=='yes')
	$cbvid->video_manager_link[]='addLinkSpeakerMenuEntry';


/**
 * Remove associate between any linked speaker's role and a video
 *
 * @param int $vid
 * 		the video's id
 */
function unlinksSpeakers($vid){
	global $speakerquery;
	if(is_array($vid))
		$vid = $vid['videoid'];
	$speakerquery->unlinkAllSpeaker($vid);
}

/** Remove speaker's associated a video when video is deleted */
register_action_remove_video("unlinksSpeakers");

/**Add entries for the plugin in the administration pages */
if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("speaker")]=='yes')
	add_admin_menu(lang('video_addon'),lang('speaker_manager'),'manage_speakers.php',SPEAKER_BASE.'/admin');
		
/**
 * insert js code into the HEADER of the edit_video.php page
 */
if ($cbplugin->is_installed('common_library.php') && 
		$userquery->permission[getStoredPluginName("speaker")]=='yes' && 
		substr($_SERVER['SCRIPT_NAME'], -14, 14) == "edit_video.php"){
	assign("videoid",$_GET['video']);
	$Cbucket->add_admin_header(PLUG_DIR . '/speaker/admin/header.html', 'global');
	
	register_anchor_function('addNavTabSpeaker', 'vidm_navtab');
	register_anchor_function('addPanelSpeaker', 'vidm_tabcontent');
	register_anchor_function('addAfterFormSpeaker', 'vidm_afterForm');

	$plgspk = filter_input(INPUT_POST, 'plgspeakers');
	if($plgspk){
		$speakers = !empty($_POST['speakers']) ? $_POST['speakers'] : array();
		$speakerquery->unlinkAllSpeaker($_GET['video']);
		foreach($speakers as $s){
			$speakerquery->linkSpeaker($s, $_GET['video']);
		}
	}
}

function addNavTabSpeaker(){
    echo '<li role="presentation"><a href="#speakers-panel" aria-controls="required" role="tab" data-toggle="tab">'. lang('speakers') .'</a></li>';
}

function addPanelSpeaker(){
    global $speakerquery, $Smarty;
    $speakers = $speakerquery->getSpeakersVideo($Smarty->get_template_vars('videoid'));
    
    echo '
                    <div id="speakers-panel" role="tabpanel" class="tab-pane">
                        <label for="speakers-related">'. lang('speakers_linked') .'</label> 
                        <button type="button" class="btn btn-xs btn-primary" id="btnAddSpeaker" data-toggle="modal" data-target="#addSpeakerModal">'. lang('speakers_addlink') .'</button>
                        <a class="btn btn-xs btn-primary" id="btnCreateSpeaker" target="_blank" href="'. SPEAKER_MANAGEPAGE_URL .'">'. lang('speakers_createlink') .'</a>
                        <table class="table table-striped">';
    foreach($speakers as $s){
        echo '
                            <tr>
                                <td class="speakerAction">
                                    <button class="btn deleteSpeaker" type="button" title="'. lang('speakers_removeLinked') .'"><span class="glyphicon glyphicon-remove"></span></button>
                                    <input type="hidden" name="speakers[]" value="'. $s['id'] .'" />
                                </td>
                                <td>'. $s['firstname'] .' '. $s['lastname'] .' <small>'. $s['role'] .'</small></td>
                            </tr>
            ';
    }
    echo '
                        </table>
                        <input type="hidden" name="plgspeakers" value="update" />
                    </div>
        ';
}

function addAfterFormSpeaker(){
?>
<div class="modal fade" tabindex="-1" role="dialog" id="addSpeakerModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
			<form method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo lang('speakers_addlink'); ?></h4>
				</div>
				<div class="modal-body">
                    <div class="form-group">
                        <select name="selectedSpeakers[]" id="speakers_selectedSpeaker" class="form-control"></select>
						<div id="beforeLoadedSpeakers">Chargement des intervenants...</div>
                    </div>
				</div>
				<div class="modal-footer">
					<div class="text-right">
                        <button type="button" class="btn btn-default btn-xs speakermCancelModal" data-dismiss="modal"><?php echo lang('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary btn-xs speakermValidate"><?php echo lang('validate'); ?></button>
                    </div>
				</div>
			</form>
        </div>
    </div>
</div>

<script src="<?php echo SPEAKER_URL; ?>/admin/mgsg/magicsuggest-min.js"></script>
<script type="text/javascript">
    $(function(){
        var spkOpt = '';
        
        $('#speakers-panel').on('click', 'button.deleteSpeaker', function(e){
            $(e.currentTarget).closest('tr').remove();
        });
        
        var msspk = $('#speakers_selectedSpeaker').magicSuggest({
            allowFreeEntries: false,
            noSuggestionText: '<?php echo lang('speakers_NoResultForQuery'); ?> : <strong>{{query}}</strong>',
            placeholder: '',
            toggleOnClick: true,
			renderer: function(data){
				return '<div class="custom-res">'+ data.name +'</div>';
			}
        });
		
		$(msspk).on('selectionchange', function(e, m){
			setTimeout(function(){
				$('#speakers_selectedSpeaker .ms-sel-ctn .ms-sel-item').each(function(i){
					var s = $(this).find('small');
					if(s.length){
						$(this).attr('title', s.html());
					}
				});
			}, 100);
		});
		
		$(msspk).on('expand', function(e, m){
			//$('#beforeLoadedSpeakers').fadeOut('fast');
		});
		
		$(msspk).on('collapse', function(e, m){
			$('#beforeLoadedSpeakers').fadeOut('fast');
		});
		
		$(msspk).on('triggerclick', function(e, m){ console.log('triggerclick', $('#speakers_selectedSpeaker .ms-res-ctn').is(':visible'));
			/*if(!$('#speakers_selectedSpeaker .ms-res-ctn').is(':visible')) */$('#beforeLoadedSpeakers').fadeIn('fast');
		});		
        
        $('#addSpeakerModal').on('show.bs.modal', function(e){
            msspk.setSelection([]);
            
            spkOpt = '';
            $('#speakers-panel table input[name="speakers[]"]').each(function(e){
                spkOpt += ','+ $(this).val();
            });
            if(spkOpt !== '') spkOpt = spkOpt.substring(1);

            $.ajax({
               url: '<?php echo SPEAKER_URL .'/action.php' ?>',
               method: 'POST',
               data: {getOpt: spkOpt}
            }).done(function(msg){
				if(msg === '') return;
                spkOpt = $.parseJSON(msg);
                msspk.setData(spkOpt);
            });
        });
        
        $('#addSpeakerModal form').submit(function(e){
            var opt = msspk.getValue();
            $.each(spkOpt, function(index, s){
                if($.inArray(s.id, opt) !== -1){
                    var resHtml = '<tr><td class="speakerAction">'
                    resHtml += '<button class="btn deleteSpeaker" type="button" title="<?php echo lang('speakers_removeLinked') ?>"><span class="glyphicon glyphicon-remove"></span></button>';
                    resHtml += '<input type="hidden" name="speakers[]" value="'+ s.id +'" />';
                    resHtml += '</td>';
                    resHtml += '<td>'+ s.name +'</td></tr>';
                    $('#speakers-panel table').append(resHtml);
                }
            });
            
            $('#addSpeakerModal').modal('hide');
            return false; 
        });
    });
</script>

<?php
}