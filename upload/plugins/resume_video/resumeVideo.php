<?php
/*
Plugin Name: Resume Video
Description: Display a confirmation box in order to ask the client if he want to resume the video from where he stop play. With this
Author: Adrien Ponchelet
Author Website: https://www.u-picardie.fr
ClipBucket Version: 2.8.2
Version: 0.1
*/



ob_start(); ?>
<!-- SWEET ALERT CSS -->
<link rel="stylesheet" type="text/css" href="plugins/resume_video/sweetalert.css">
<!-- SWEET ALERT JS -->
<script src="plugins/resume_video/sweetalert.min.js"></script>


<script type="text/javascript">

/**
*	Test 
*/
function localStorageIsAvailable()
{
	try{
		return 'localStorage' in window && window['localStorage'] !== null;
	} catch(e){
		return false;
	}
}


// Small Utility for retrieving a QueryString param.
var RequestUtils = {
    queryParam: function(name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }
};


var PlayerRecordingLoop;

var VideoPlayerTracker = {
	initialize: function(player, time, autoplay){
		if (localStorageIsAvailable()) {
			this.player = player;
			this.time = time;
			this.autoplay = autoplay;
			this.addEventListeners();
		}
		else{
			alert('You need to have the localStorage in order to use this tracker.')
		}
	},

	addEventListeners: function(){
		this.player.ready(this.onLoad.bind(this));
		this.player.on("play", this.onPlay.bind(this));
		this.player.on("ended", this.onEnd.bind(this));
		this.player.on("volumechange", this.onVolumeChange.bind(this));

		// Event Listeners for PlaybackRates item
		var playbackrates = document.querySelectorAll('.vjs-menu-item');
		for (var i = 0; i < playbackrates.length; i++){
			playbackrates[i].addEventListener('click', this.onPlaybackChange.bind(this));
		}
	},

	onLoad: function(){
		this.setStartPoint().setDefaultVolume().setDefaultPlaybackSpeed().prompUser();
	},

	onPlay: function(){
		if (this.currentTime() < 1){
			this.startAt(0);
		}
		this.beginRecordingPosition();
	},

	onEnd: function(){
		this.stopRecordingPosition();
	},

	onVolumeChange: function() {
		localStorage.setItem("webtv_volume", this.player.volume());
	},

	onPlaybackChange: function(){
		localStorage.setItem("webtv_playback_speed", this.player.playbackRate());
	},

	setStartPoint: function(){
		this.startAt(this.getVideoStartTime());

		return this;
	},

	setDefaultVolume: function() {
		var currentVolume = localStorage.getItem('webtv_volume');

		if (currentVolume) {
			this.player.volume(currentVolume);
		}

		return this;
	},

	setDefaultPlaybackSpeed: function() {
		var currentPlaybackSpeed = localStorage.getItem('webtv_playback_speed');

		if (currentPlaybackSpeed) {
			this.player.playbackRate(currentPlaybackSpeed);
		}

		return this;
	},

	prompUser: function(){
		if (this.hasPreviouslyBeenWatched() && !this.time){
			return this.promptUserToContinue();
		}

		if (this.autoplay){
			this.play();
		}
		return this;
	},

	promptUserToContinue: function(){

		var that = this;

		swal({
			title: "Continuer la vidéo",
			text: "Souhaitez-vous reprendre la lecture là où vous vous êtes arrêté ?",
			showCancelButton: true,
			confirmButtonText: "Oui",
			cancelButtonText: "Non",
		}, function(confirm){
			if (confirm){
				return that.startAt(that.secondsWatchedSoFar() - 3).play();
			}
			that.stopRecordingPosition();
		
			//return that.startAt(that.getVideoStartTime()).play();
		});

	},

	beginRecordingPosition: function(){
		PlayerRecordingLoop = setInterval(function(){
			localStorage.setItem(this.id(), this.currentTime());
		}.bind(this), 3000);
	},

	stopRecordingPosition: function(){
		clearInterval(PlayerRecordingLoop);
		localStorage.removeItem(this.id());
	},

	startAt: function(time){
		return this.player.currentTime(time);
	},

	play: function(){
		return this.player.play();
	},

	currentTime: function(){
		return this.player.currentTime();
	},

	id: function(){
		return "webtv_id:" + document.URL;
	},

	hasPreviouslyBeenWatched: function(){
		var secondsWatched = this.secondsWatchedSoFar();

		return !! (secondsWatched && secondsWatched > 3);
	},

	secondsWatchedSoFar: function(){
		return localStorage.getItem(this.id());
	},

	getVideoStartTime: function(){
		if (!this.time) return 1;

		if (typeof this.time === 'string' && this.time.indexOf(":") > -1){
			var t = this.time.split(':');
			return 60 * parseInt(t[0]) + parseInt(t[1]);
		}

		return parseInt(this.time);
	}

}


VideoPlayerTracker.initialize(
	videojs('cb_video_js'),
	RequestUtils.queryParam("time"),
	RequestUtils.queryParam("autoplay")
);
</script>

<?php 
$jssnip = ob_get_clean();


register_anchor($jssnip,'after_watch_video');

?>