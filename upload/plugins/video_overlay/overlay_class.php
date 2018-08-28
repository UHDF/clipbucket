<?php
/*
 * This file contains Overlay class
 */

// Global Object $overlay is used in the plugin
$overlay = new Overlay();
$Smarty->assign_by_ref('overlay', $overlay);

/**
 * Class Containing actions for the overlay plugin
 */
class Overlay extends CBCategory{

	/**
	 * AffOption : Permet d'afficher une ligne d'un select
	 * Utilisation conjointe avec AffSelected pour afficher l'option choisie
	 *
	 * @param [string] $valeur
	 * @param [string] $choix
	 * @param [string] $titre
	 * @param string $desc
	 * @return void
	 */
	function AffOption($valeur, $choix, $titre, $desc = ''){
		$attr = ($desc <> '') ? ' title="'.$desc.'"' : '';
		echo '<option value="'.$valeur.'"'.$attr.''.$this->AffSelected($valeur, $choix).'>'.$titre.'</option>';
	}

	/**
	 * AffSelected
	 *
	 * @param [string] $valeur
	 * @param [string] $choix
	 * @return void
	 */
	function AffSelected($valeur, $choix){
		if ($choix == $valeur){
			return " selected";
		}
	}

	function getOverlayHtmlList($vid){
		//$html = '';
		$respons = $this->getOverlays($vid);

		$position = array("top-left", "top", "top-right", "right", "bottom-right", "bottom", "bottom-left", "left", "fullpage");
		$bool = array("true", "false");

		$i = 1;
		echo '<form class="form-horizontal">';
		if (count($respons) > 0) {
			foreach ($respons as $key => $val){
				$id = $respons[$key]["id"];
				$json = json_decode($respons[$key]["content"]);

				echo '<div id="ovform'.$id.'">';
				echo '<input type="hidden" name="ovid'.$i.'" id="ovid'.$i.'" value="'.$id.'">';

				echo '<div class="form-group">';
					echo '<label class="col-sm-2 control-label" for="ovalign'.$i.'">'.lang("position_overlay").'</label>';
					echo '<div class="col-sm-4">';
						echo '<select class="form-control" name="ovalign'.$i.'" id="ovalign'.$i.'">';
							foreach ($position as $item){
								$this->AffOption($item, $json->{'align'}, $item);
							}
						echo '</select>';
					echo '</div>';

					echo '<label class="col-sm-2 control-label" for="ovshowbg'.$i.'">'.lang("showbg_overlay").'</label>';
					echo '<div class="col-sm-4">';
						echo '<select class="form-control" name="ovshowbg'.$i.'" id="ovshowbg'.$i.'">';
							foreach ($bool as $item){
								$this->AffOption($item, $json->{"showBackground"}, $item);
							}
						echo '</select>';
					echo '</div>';
				echo '</div>';

				echo '<div class="form-group">';
					echo '<label class="col-sm-2 control-label" for="ovclass'.$i.'">'.lang("css_overlay").'</label>';
					echo '<div class="col-sm-10">';
						echo '<input type="text" class="form-control" name="ovclass'.$i.'" id="ovclass'.$i.'" value="'.$json->{"class"}.'">';
					echo '</div>';
				echo '</div>';

				echo '<div class="form-group">';
					echo '<label class="col-sm-2 control-label" for="ovcontent'.$i.'">'.lang("content_overlay").'</label>';
					echo '<div class="col-sm-10">';
						echo '<input type="text" class="form-control" name="ovcontent'.$i.'" id="ovcontent'.$i.'" value="'.$json->{"content"}.'&lt;a href=&quot;plop.com&quot;>plop</a>">';
					echo '</div>';
				echo '</div>';

				echo '<div class="form-group">';
					echo '<label class="col-sm-2 control-label" for="ovstart'.$i.'">'.lang("begin_overlay").'</label>';
					echo '<div class="col-sm-4">';
						echo '<input type="text" class="form-control" name="ovstart'.$i.'" id="ovstart'.$i.'" value="'.$json->{"start"}.'">';
					echo '</div>';

					echo '<label class="col-sm-2 control-label" for="ovend'.$i.'">'.lang("end_overlay").'</label>';
					echo '<div class="col-sm-4">';
						echo '<input type="text" class="form-control" name="ovend'.$i.'" id="ovend'.$i.'" value="'.$json->{"end"}.'">';
					echo '</div>';
				echo '</div>';

				echo '<div class="form-group">';
					echo '<div class="col-sm-12">';
						echo '<a onclick="deleteOverlay('.$id.');" class="btn btn-primary btn-xs" name="ovdelete'.$i.'" id="ovdelete'.$i.'"><span class="glyphicon glyphicon-trash"></span> '.lang("delete_overlay").'</a>';
					echo '</div>';
				echo '</div>';

				echo '<hr>';
				echo '</div>';

				$i++;
			}
		}
		else{
			echo lang("no_overlay");
		}

		echo '<input type="hidden" name="ovnumber" id="ovnumber" value="'.($i-1).'">';
		echo '</form>';

		// return $html;
		// echo $html;

	}

	function getOverlays($vid) {
		$query = "SELECT * FROM ".tbl('video_overlay')." WHERE `videoid` = '".$vid."' ORDER BY `id`;";
		$result = select($query);
		return $result;
	}

	function setOverlays($flds, $vls) {
		global $db;
		$db->mysqli->query("INSERT INTO `video_overlay` VALUES (NULL, '".addslashes($vls[1])."', '".$vls[2]."');");
	}

	function deleteOverlay($id) {
		global $db;
		if (is_numeric($id)){
			$db->delete(tbl("video_overlay"),array("id"),array($id));
		}
	}

	function updateOverlay($ovalign, $ovshowbg, $ovclass, $ovcontent, $ovstart, $ovend, $ovid) {
		global $db;

		$content = '{"align":"'.$ovalign.'","showBackground":"'.$ovshowbg.'","class":"'.$ovclass.'","content":"'.htmlspecialchars($ovcontent).'","start":"'.$ovstart.'","end":"'.$ovend.'"}';

		$db->mysqli->query("UPDATE `video_overlay` SET `content` = '".addslashes($content)."' WHERE `id` = '".$ovid."';");
	}

}

?>