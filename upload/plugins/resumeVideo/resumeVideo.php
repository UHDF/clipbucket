<?php
/*
Plugin Name: Resume Video
Description: Display a confirmation box in order to ask the client if he want to resume the video from where he stop play.
Author: Adrien Ponchelet
Author Website: https://www.u-picardie.fr
ClipBucket Version: 2.8.2
Version: 0.1
*/

function shareVideoOptions() {
	echo '

	<!-- RESUME VIDEO CSS -->
	<link rel="stylesheet" type="text/css" href="plugins/resumeVideo/css/resumeVideo.css">

	<!-- SWEET ALERT CSS -->
	<link rel="stylesheet" type="text/css" href="plugins/resumeVideo/css/sweetalert.css">

	<!-- RESUME VIDEO JS -->
	<script src="plugins/resumeVideo/js/resumeVideo.js"></script>

	<!-- SWEET ALERT JS -->
	<script src="plugins/resumeVideo/js/sweetalert.min.js"></script>

	<!-- FORM -->
	<div class="form-group row">
		<input type="checkbox" id="chkBegin" class="begin-checkbox">
		<label for="startVideoTime" class="begin-label"> '.lang("startat").' : </label>
		<input type="hidden" name="startVideoSeconds" id="startVideoSeconds" value="" class="form-control begin-input" readonly>
		<input type="text" name="startVideoTime" id="startVideoTime" value="" class="form-control begin-input">
	</div>
	
	
	<div class="form-group row">
		<input type="checkbox" name="chkAutoplay" id="chkAutoplay" class="begin-checkbox"> <label for="chkAutoplay" class="begin-label"> '.lang("autoplay").'</label>
	</div>
		
	<div class="form-group row">
		<input type="checkbox" id="chkEnd" class="begin-checkbox">
		<label for="stopVideoTime" class="begin-label"> '.lang("stopat").' : </label>
		<input type="hidden" name="stopVideoSeconds" id="stopVideoSeconds" value="" class="form-control begin-input" readonly>
		<input type="text" name="stopVideoTime" id="stopVideoTime" value="" class="form-control begin-input">
	</div>

	';
}

register_anchor_function("shareVideoOptions","shareVideoOptions");

?>