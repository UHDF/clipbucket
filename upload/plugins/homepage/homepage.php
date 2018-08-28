<?php
/*
 Plugin Name: Homepage manager
 Description: This plugin will allow to manage the custom homepages.<br />Example of htaccess rule to access homepages : <strong>RewriteRule ^accueil/(.+) plugins/homepage/home.php?slug=$1</strong>
 Author: Bastien Poirier
 Author Website: http://semm.univ-lille1.fr/
 ClipBucket Version: 2.8
 Version: 1.0
 Website:
 */

require_once 'homepage_class.php';

global $cbplugin;

if(!$cbplugin->is_installed('common_library.php'))
	e(sprintf(lang('plugin_not_installed : %s'), 'Common Library'));
else
	require_once PLUG_DIR .'/common_library/common_library.php';

if(!$cbplugin->is_installed('homepage.php')) return;
if(!$homepagequery->isActive()) return;
if(!$userquery->permission[getStoredPluginName('homepage')] === 'yes') return;

define('SITE_MODE', '/admin_area');
define('CHP_BASE', basename(dirname(__FILE__)));
define('CHP_DIR', PLUG_DIR .'/'. CHP_BASE);
define('CHP_URL', PLUG_URL .'/'. CHP_BASE);
define('CHP_ADMIN_DIR', CHP_DIR .'/admin');
define('CHP_ADMIN_URL', CHP_URL .'/admin');

define('CHP_CREATEPAGE_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. CHP_BASE .'/admin&file=create_homepage.php');
define('CHP_EDITPAGE_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. CHP_BASE .'/admin&file=edit_homepage.php');
define('CHP_MANAGEPAGE_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. CHP_BASE .'/admin&file=manage_page.php');
define('CHP_MANAGEPAGES_URL', BASEURL.SITE_MODE .'/plugin.php?folder='. CHP_BASE .'/admin&file=manage_homepages.php');

assign('chp_url', CHP_URL);
assign('chp_admin', CHP_ADMIN_URL);
assign('chp_createpage', CHP_CREATEPAGE_URL);
assign('chp_editpage', CHP_EDITPAGE_URL);
assign('chp_actionpage', CHP_URL .'/action.php');
assign('chp_managepage', CHP_MANAGEPAGE_URL);
assign('chp_managepages', CHP_MANAGEPAGES_URL);
assign('chp_js', CHP_ADMIN_DIR. '/js.html');
assign('chp_css', CHP_ADMIN_DIR. '/css.html');
assign('chp_mgsg', CHP_ADMIN_URL. '/mgsg');

add_admin_menu(lang('video_addon'), lang('chp_title'), 'manage_homepages.php', CHP_BASE .'/admin');

if($userquery->permission[getStoredPluginName('homepage')] === 'yes' && substr($_SERVER['SCRIPT_NAME'], -14, 14) === 'edit_video.php'){
	$video = filter_input(INPUT_GET, 'video');
	$Cbucket->add_admin_header(CHP_ADMIN_DIR . '/header.html');
	register_anchor_function('addNavTabHp', 'vidm_navtab');
	register_anchor_function('addPanelHp', 'vidm_tabcontent');
	register_anchor_function('addAfterFormHp', 'vidm_afterForm');

	$plghp = filter_input(INPUT_POST, 'plghomepage');
	if($plghp){
		$sh = $homepagequery->getSuggestedHp($video);
		$suggested = array();
		foreach($sh as $suho){
			$suggested[] = $suho['home_id'];
		}
		
		$chps = filter_input(INPUT_POST, 'chp', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
		foreach($chps as $chp){
			$exist = $homepagequery->getByHome($chp);
			if(count($exist)){
				if(!in_array($chp, $suggested)) $homepagequery->addSuggestedVideo($chp, $video);
			}
			
			$key = array_search($chp, $suggested);
			if($key !== false) unset($suggested[$key]);
		}
		
		foreach($suggested as $sh){
			$homepagequery->removeSuggestedVideo($sh, $video);
		}
	}
}

function addNavTabHp(){
	echo '<li role="presentation"><a href="#homepage-panel" aria-controls="required" role="tab" data-toggle="tab">'. lang('chp_tabSuggest') .'</a></li>';
}

function addPanelHp(){
	global $homepagequery;
	$chps = $homepagequery->getSuggested($_GET['video']);
	
	echo '
                    <div id="homepage-panel" role="tabpanel" class="tab-pane">
                        <label>'. lang('chp_suggestHp') .'</label> 
                        <button type="button" class="btn btn-xs btn-primary" id="btnAddHp" data-toggle="modal" data-target="#addHpModal">'. lang('chp_addHp') .'</button>
                        <table class="table table-striped">';
    foreach($chps as $chp){
        echo '
                            <tr>
                                <td class="hpAction">
                                    <button class="btn deleteHp" type="button" title="'. lang('chp_removeSuggested') .'"><span class="glyphicon glyphicon-remove"></span></button>
                                    <input type="hidden" name="chp[]" value="'. $chp['home_id'] .'" />
                                </td>
                                <td>'. $chp['name'] .' <small>('. $chp['slug'] .')</small></td>
                            </tr>
            ';
    }
    echo '
                        </table>
                        <input type="hidden" name="plghomepage" value="update" />
                    </div>
        ';
}

function addAfterFormHp(){
?>
<div class="modal fade" tabindex="-1" role="dialog" id="addHpModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
			<form method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo lang('chp_addHp'); ?></h4>
				</div>
				<div class="modal-body">
                    <div class="form-group">
                        <select name="suggestHomepage" id="suggestHomepage" class="form-control"></select>
                    </div>
				</div>
				<div class="modal-footer">
					<div class="text-right">
						<button type="button" class="btn btn-default btn-xs chpCancelModal" data-dismiss="modal"><?php echo lang('cancel'); ?></button>
						<button type="submit" class="btn btn-primary btn-xs chpValidate"><?php echo lang('validate'); ?></button>
					</div>
				</div>
			</form>
        </div>
    </div>
</div>

<script src="<?php echo CHP_ADMIN_URL; ?>/mgsg/magicsuggest-min.js"></script>
<script type="text/javascript">
    $(function(){
        var chpOpt = '';
        
        $('#homepage-panel').on('click', 'button.deleteHp', function(e){
            $(e.currentTarget).closest('tr').remove();
        });
        
        var msgschp = $('#suggestHomepage').magicSuggest({
            allowFreeEntries: false,
            noSuggestionText: '<?php echo lang('chp_noResultForQuery'); ?> : <strong>{{query}}</strong>',
            placeholder: '',
            toggleOnClick: true
        });
        
        $('#addHpModal').on('show.bs.modal', function(e){
            msgschp.setSelection([]);
            
            chpOpt = '';
            $('#homepage-panel table input[name="chp[]"]').each(function(e){
                chpOpt += ','+ $(this).val();
            });
            if(chpOpt !== '') chpOpt = chpOpt.substring(1);
			
            $.ajax({
               url: '<?php echo CHP_URL .'/action.php' ?>',
               method: 'POST',
               data: {getOpt: chpOpt}
            }).done(function(msg){
				if(msg === '') return;
                chpOpt = $.parseJSON(msg);
                msgschp.setData(chpOpt);
            });
        });
        
        $('#addHpModal form').submit(function(e){
            var opt = msgschp.getValue();
            $.each(chpOpt, function(index, d){
                if($.inArray(d.id, opt) !== -1){
                    var resHtml = '<tr><td class="hpAction">';
                    resHtml += '<button class="btn deleteHp" type="button" title="<?php echo lang('chp_removeSuggested') ?>"><span class="glyphicon glyphicon-remove"></span></button>';
                    resHtml += '<input type="hidden" name="chp[]" value="'+ d.id +'" />';
                    resHtml += '</td>';
                    resHtml += '<td>'+ d.name +'</td></tr>';
                    $('#homepage-panel table').append(resHtml);
                }
            });
            
            $('#addHpModal').modal('hide');
            return false; 
        });
    });
</script>

<?php
}
