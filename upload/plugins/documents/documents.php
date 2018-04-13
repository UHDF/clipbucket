<?php
/*
 Plugin Name: Documents
 Description: This plugin will add documents to a video.
 Author: Franck Rouze
 Author Website: http://semm.univ-lille1.fr/
 ClipBucket Version: 2.8
 Version: 1.0
 Website:
 */
require_once 'document_class.php';
if (!$cbplugin->is_installed('common_library.php'))
	e(sprintf(lang("plugin_not_installed : %s"),"Common Library"));
else
	require_once PLUG_DIR.'/common_library/common_library.php';

// Define Plugin's uri constants
define("SITE_MODE",'/admin_area');
define('DOCUMENT_BASE',basename(dirname(__FILE__)));
define('DOCUMENT_DIR',PLUG_DIR.'/'.DOCUMENT_BASE);
define('DOCUMENT_URL',PLUG_URL.'/'.DOCUMENT_BASE);
assign('document_url', DOCUMENT_URL);
define('DOCUMENT_ADMIN_DIR',DOCUMENT_DIR.'/admin');
define('DOCUMENT_ADMIN_URL',DOCUMENT_URL.'/admin');
define("DOCUMENT_MANAGEPAGE_URL",BASEURL.SITE_MODE."/plugin.php?folder=".DOCUMENT_BASE."/admin&file=manage_documents.php");
assign("document_managepage",DOCUMENT_MANAGEPAGE_URL);
define("DOCUMENT_LINKPAGE_URL",BASEURL.SITE_MODE."/plugin.php?folder=".DOCUMENT_BASE."/admin&file=link_documents.php");
assign("document_linkpage",DOCUMENT_LINKPAGE_URL);
define("DOCUMENT_DOWNLOAD_DIR",BASEDIR."/files/documents");


if(!function_exists('externalDocumentList')){
	/**
	 * Define the Anchor to display documents into description of a video main page
	 * 
	 * @param array $data
	 * 		a dictionary containing information about the requested documents
	 * 	@see Document.getDocumentForVideo() function for more details
	 */
	function externalDocumentList($data){
		global $documentquery;
		$data["selected"]="yes";
		$lnks=$documentquery->getDocumentForVideo($data);
		$str='';
		foreach ($lnks as $lnk) {
			//$str.='<li><a target="_blank" href="'.BASEURL.'/files/documents/'.$lnk['storedfilename'].'">'.$lnk['title'] .'</a></li>';
			$str.='<li><a target="_blank" href="'.DOCUMENT_URL.'/download.php?download='.$documentquery->encode_key($lnk['documentkey']).'">'.$lnk['title'] .'</a></li>';
			//return BASEURL."/download_photo.php?download=".$documentquery->encode_key($details['photo_key']);
		}
		echo $str;	
	}
	// use {ANCHOR place="externalDocumentList" data=$video} to display the formatted list above
	register_anchor_function('externalDocumentList','externalDocumentList');
}	

if(!function_exists('externalDocumentCount')){
	/**
	 * Get external documents count for the current video
	 *
	 * This function is registrered in smarty to be used directly into the template
	 * @return int
	 * 		the number of document linked to the current video
	 * @see Document.getLinkForVideo() function for more details
	 */
	function externalDocumentCount(){
		global $Smarty;
		global $documentquery;
		global $db;
		if ($_GET["v"]){
			$vid=$_GET["v"];
			$result=$db->_select("SELECT `videoid` FROM ".tbl('video')." WHERE `videokey`='".$vid."'");
			if (count($result)==1) $vid=$result[0]['videoid'];
			$data=["videoid"=> $vid, "selected" => "yes","count_only"=>True];
			$cnt=$documentquery->getDocumentForVideo($data);
			return intval($cnt);
		}
		else return 0;
	}
	global $Smarty;
	$Smarty->register_function('externalDocumentCount','externalDocumentCount');
}

/**
 * Remove associate between any documents and a video
 *
 * @param int $vid
 * 		the video's id
 */
function unlinksDocuments($vid){
	global $documentquery;
	if(is_array($vid))
		$vid = $vid['videoid'];
		$documentquery->unlinkAllDocuments($vid);
}

/** Remove documents associated a video when video is deleted */
register_action_remove_video("unlinksDocuments");

/**
 * Add a new entry "Link document" into the video manager menu named "Actions" associated to each video
 * 
 * @param int $vid 
 * 		the video id
 * @return string
 * 		the html string to be inserted into the menu
 */
function addDocumentMenuEntry($vid){
	$idtmp=$vid['videoid'];
	return '<li><a role="menuitem" href="'.DOCUMENT_LINKPAGE_URL.'&video='.$idtmp.'">'.lang("link_document").'</a></li>';
}
if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("documents")]=='yes')
	$cbvid->video_manager_link[]='addDocumentMenuEntry';

/**
 * Add entries for the plugin in the administration pages
 */
if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("documents")]=='yes')
	add_admin_menu(lang('video_addon'),lang('document_manager'),'manage_documents.php',DOCUMENT_BASE.'/admin');

	
/**
 * insert js code into the HEADER of the edit_video.php page
 */
if ($cbplugin->is_installed('common_library.php') && $userquery->permission[getStoredPluginName("documents")]=='yes' && substr($_SERVER['SCRIPT_NAME'], -14, 14) == "edit_video.php"){
	assign("videoid", $_GET['video']); 
	$Cbucket->add_admin_header(PLUG_DIR . '/documents/admin/header.html');
        
	register_anchor_function('addNavTab', 'vidm_navtab');
	register_anchor_function('addPanel', 'vidm_tabcontent');
	register_anchor_function('addAfterForm', 'vidm_afterForm');

	$plgdoc = filter_input(INPUT_POST, 'plgdocuments');
	if($plgdoc){
		$documents = !empty($_POST['documents']) ? $_POST['documents'] : array();
		$documentquery->setVideoDocuments($_GET['video'], $documents);
	}
}


function addNavTab(){
    echo '<li role="presentation"><a href="#documents-panel" aria-controls="required" role="tab" data-toggle="tab">'. lang('documents') .'</a></li>';
}

function addPanel(){
    global $documentquery, $Smarty;
    $sDocs = $documentquery->getDocumentForVideo(array('limit' => '', 'order' => 'title', 'selected' => 'yes', 'videoid' => $Smarty->get_template_vars('videoid')));
    
    echo '
                    <div id="documents-panel" role="tabpanel" class="tab-pane">
                        <label for="documents-related">'. lang('documents_linked') .'</label> 
                        <button type="button" class="btn btn-xs btn-primary" id="btnAddDocument" data-toggle="modal" data-target="#addDocModal">'. lang('documents_addDoc') .'</button>
                        <table class="table table-striped">';
    foreach($sDocs as $doc){
        echo '
                            <tr>
                                <td class="docAction">
                                    <button class="btn deleteDocument" type="button" title="'. lang('documents_removeLinked') .'"><span class="glyphicon glyphicon-remove"></span></button>
                                    <input type="hidden" name="documents[]" value="'. $doc['id'] .'" />
                                </td>
                                <td><a href="'. $documentquery->getHref($doc['documentkey']) .'" target="_blank">'. $doc['title'] .'</a></td>
                            </tr>
            ';
    } //DOCUMENT_URL .'/download.php?download='. $documentquery->encode_key($doc['documentkey'])
    echo '
                        </table>
                        <input type="hidden" name="plgdocuments" value="update" />
                    </div>
        ';
}

function addAfterForm(){
?>
<div class="modal fade" tabindex="-1" role="dialog" id="addDocModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
			<form method="post" enctype="multipart/form-data">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo lang('documents_addDoc'); ?></h4>
				</div>
				<div class="modal-body">
                    <div class="form-group">
                        <select name="selectedDocuments[]" id="documents_selectedDoc" class="form-control"></select>
                    </div>
				</div>
				<div class="modal-footer">
					<div class="text-right">
						<button type="button" class="btn btn-default btn-xs docmCancelModal" data-dismiss="modal"><?php echo lang('cancel'); ?></button>
						<button type="submit" class="btn btn-primary btn-xs docmValidate"><?php echo lang('validate'); ?></button>
					</div>
				</div>
			</form>
        </div>
    </div>
</div>

<script src="<?php echo DOCUMENT_URL; ?>/admin/mgsg/magicsuggest-min.js"></script>
<script type="text/javascript">
    $(function(){
        var docOpt = '';
        
        $('#documents-panel').on('click', 'button.deleteDocument', function(e){
            $(e.currentTarget).closest('tr').remove();
        });
        
        var ms = $('#documents_selectedDoc').magicSuggest({
            allowFreeEntries: false,
            noSuggestionText: '<?php echo lang('documents_NoResultForQuery'); ?> : <strong>{{query}}</strong>',
            placeholder: '',
            toggleOnClick: true
        });
        
        $('#addDocModal').on('show.bs.modal', function(e){
            ms.setSelection([]);
            
            docOpt = '';
            $('#documents-panel table input[name="documents[]"]').each(function(e){
                docOpt += ','+ $(this).val();
            });
            if(docOpt !== '') docOpt = docOpt.substring(1);

            $.ajax({
               url: '<?php echo DOCUMENT_URL .'/action.php' ?>',
               method: 'POST',
               data: {getOpt: docOpt}
            }).done(function(msg){
				if(msg === '') return;
                docOpt = $.parseJSON(msg);
                ms.setData(docOpt);
            });
        });
        
        $('#addDocModal form').submit(function(e){
            var opt = ms.getValue();
            $.each(docOpt, function(index, d){
                if($.inArray(d.id, opt) !== -1){
                    var resHtml = '<tr><td class="docAction">'
                    resHtml += '<button class="btn deleteDocument" type="button" title="<?php echo lang('documents_removeLinked') ?>"><span class="glyphicon glyphicon-remove"></span></button>';
                    resHtml += '<input type="hidden" name="documents[]" value="'+ d.id +'" />';
                    resHtml += '</td>';
                    resHtml += '<td><a href="'+ d.link +'" target="_blank">'+ d.name +'</a></td></tr>';
                    $('#documents-panel table').append(resHtml);
                }
            });
            
            $('#addDocModal').modal('hide');
            return false; 
        });
    });
</script>

<?php
}