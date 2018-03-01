
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
	initialize: function(player, time, stop, autoplay){
		if (localStorageIsAvailable()) {
			this.player = player;
			this.time = time;
			this.stop = stop;
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
		// Event Listeners for Subtitles item
		var subtitleButton = document.querySelector('.vjs-subtitles-button').firstChild.firstChild.childNodes;
		for (var i = 0; i < subtitleButton.length; i++){
			subtitleButton[i].addEventListener('click', this.onSubtitleChange.bind(this));
		}
		// Event Listeners for PlaybackRates item
		var playbackrates = document.querySelectorAll('.vjs-menu-item');
		for (var i = 0; i < playbackrates.length; i++){
			playbackrates[i].addEventListener('click', this.onPlaybackChange.bind(this));
		}
	},

	onLoad: function(){
		this.setStartPoint().setDefaultVolume().setDefaultPlaybackSpeed().setDefaultSubtitle().prompUser();
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

	onSubtitleChange: function() {

		var subtitle = "none";
		var track = this.player.textTracks();

		for (i = 0; i < track.length; i++){
			// console.log(track[i].label);
			if (track[i].mode == 'showing'){
				subtitle = track[i].label;
			}
		}

		localStorage.setItem("webtv_subtitle", subtitle);
	},

	onPlaybackChange: function(){
		localStorage.setItem("webtv_playback_speed", this.player.playbackRate());
	},

	setStartPoint: function(){
		this.startAt(this.getVideoStartTime());

		return this;
	},

	setStopPoint: function(){
		return this.getVideoStopTime();
	},

	setDefaultVolume: function() {
		var currentVolume = localStorage.getItem('webtv_volume');

		if (currentVolume) {
			this.player.volume(currentVolume);
		}

		return this;
	},
	
	setDefaultSubtitle: function(){

		var sub2 = localStorage.getItem("webtv_subtitle");
		var track = this.player.textTracks();

		if ( (sub2 != null) ) {
			for (i = 0; i < track.length; i++){
				// console.log(track[i].label);
				track[i].mode = "hidden";

				if (track[i].label == sub2){
					track[i].mode = "showing";
				}
			}
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

	stopPlay: function(){
		if (!this.stop) return 1;

		if (this.currentTime() > this.getVideoStopTime(this.stop)) {
			this.player.pause();
			this.stop = 'undefined';
		}
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
			this.stopPlay();
		}.bind(this), 1000);
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
		var id = RequestUtils.queryParam("v");

		return "webtv_id:" + id;
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
	},

	getVideoStopTime: function(){
		if (!this.stop) return 1;

		if (typeof this.stop === 'string' && this.stop.indexOf(":") > -1){
			var t = this.stop.split(':');
			return 60 * parseInt(t[0]) + parseInt(t[1]);
		}

		return parseInt(this.stop);
	}

}


/* 
* Use only if the video id is the one we want. 
* Due to rtmp modification, a switch write a 
* video tag with other id
*/
if (document.querySelector("#cb_video_js")){

	VideoPlayerTracker.initialize(
		videojs('cb_video_js'),
		RequestUtils.queryParam("time"),
		RequestUtils.queryParam("stop"),
		RequestUtils.queryParam("autoplay")
	);

}




/**
* http://stackoverflow.com/a/10997390/11236
*/
function updateURLParameter(url, param, paramVal){
	var newAdditionalURL = "";
	var tempArray = url.split("?");
	var baseURL = tempArray[0];
	var additionalURL = tempArray[1];
	var temp = "";
	if (additionalURL) {
		tempArray = additionalURL.split("&");
		for (var i=0; i<tempArray.length; i++){
			if(tempArray[i].split('=')[0] != param){
				newAdditionalURL += temp + tempArray[i];
				temp = "&";
			}
		}
	}

	var rows_txt = temp + "" + param + "=" + paramVal;
	return baseURL + "?" + newAdditionalURL + rows_txt;
}


/**
 *	Delete parameter from URL
	*/
function removeParam(key, sourceURL) {
	var rtn = sourceURL.split("?")[0],
		param,
		params_arr = [],
		queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
	if (queryString !== "") {
		params_arr = queryString.split("&");
		for (var i = params_arr.length - 1; i >= 0; i -= 1) {
			param = params_arr[i].split("=")[0];
			if (param === key) {
				params_arr.splice(i, 1);
			}
		}
		rtn = rtn + "?" + params_arr.join("&");
	}
	return rtn;
}

/**
 *	Convert integer to human readable time hh:mm:ss
	*/
function secToTime(seconds){
	var date = new Date(null);
	date.setSeconds(seconds); // specify value for SECONDS here
	var result = date.toISOString().substr(11, 8);
	return result;
}

/**
 *	Convert human readable time hh:mm:ss to integer
	*/
function timeToSec(hms){
	//var hms = '02:04:33';   // your input string
	var a = hms.split(':'); // split it at the colons
	// minutes are worth 60 seconds. Hours are worth 60 minutes.
	var seconds = (+a[0]) * 60 * 60 + (+a[1]) * 60 + (+a[2]); 
	return seconds;
}

$(document).ready(function() {
	var theplayer = videojs('cb_video_js');
	var theChkBegin = document.querySelector("#chkBegin");
	var theChkEnd = document.querySelector("#chkEnd");
	var theChkAutoplay = document.querySelector("#chkAutoplay");
	var theBeginSecondInput = document.querySelector("#startVideoSeconds");
	var theBeginFakeInput = document.querySelector("#startVideoTime");

	var theEndSecondInput = document.querySelector("#stopVideoSeconds");
	var theEndFakeInput = document.querySelector("#stopVideoTime");

	var theLinkInput = document.querySelector('#link_video');

	
	/**
	 * Start
	 */

	// Change made in the input modifying the URL
	theBeginFakeInput.addEventListener("change", function() {
		if (theChkBegin.checked == true){
			theBeginSecondInput.value = timeToSec(theBeginFakeInput.value);
			theLinkInput.value = updateURLParameter(theLinkInput.value, 'time', theBeginSecondInput.value); // add param in url input
		}
		else{
			theLinkInput.value = removeParam('time', theLinkInput.value) // remove param from url input
		}
	});


	// Checkbox click
	theChkBegin.addEventListener("click", function() {
		if (theChkBegin.checked == true){
			theBeginSecondInput.value = Math.floor(theplayer.currentTime()); // Temps dans la video
			theBeginFakeInput.value = secToTime(theBeginSecondInput.value);
			// add param in url input
			theLinkInput.value = updateURLParameter(theLinkInput.value, 'time', theBeginSecondInput.value);
		}
		else{
			// remove param from url input
			theLinkInput.value = removeParam('time', theLinkInput.value)
		}
	});


	/**
	 * Stop
	 */

	// Change made in the input modifying the URL
	theEndFakeInput.addEventListener("change", function() {
		if (theChkEnd.checked == true){
			theEndSecondInput.value = timeToSec(theEndFakeInput.value);
			theLinkInput.value = updateURLParameter(theLinkInput.value, 'stop', theEndSecondInput.value); // add param in url input
		}
		else{
			theLinkInput.value = removeParam('stop', theLinkInput.value) // remove param from url input
		}
	});


	// Checkbox click
	theChkEnd.addEventListener("click", function() {
		if (theChkEnd.checked == true){
			theEndSecondInput.value = Math.floor(theplayer.currentTime()); // Temps dans la video
			theEndFakeInput.value = secToTime(theEndSecondInput.value);
			// add param in url input
			theLinkInput.value = updateURLParameter(theLinkInput.value, 'stop', theEndSecondInput.value);
		}
		else{
			// remove param from url input
			theLinkInput.value = removeParam('stop', theLinkInput.value)
		}
	});


	/**
	 * Autoplay
	 */

	// Checkbox click
	theChkAutoplay.addEventListener("click", function() {
		if (theChkAutoplay.checked == true){
			theLinkInput.value = updateURLParameter(theLinkInput.value, 'autoplay', 'true'); // add param in url input
		}
		else{
			theLinkInput.value = removeParam('autoplay', theLinkInput.value) // remove param from url input
		}
	});


	/**
	 * Player
	 */

	// Player "play" click
	theplayer.on("play", function() {
		PlayerRecordingLoop = setInterval(function(){
			if (theChkBegin.checked == true){
				theBeginSecondInput.value = Math.floor(theplayer.currentTime());
				theLinkInput.value = updateURLParameter(theLinkInput.value, 'time', theBeginSecondInput.value); // add param in url input
				theBeginFakeInput.value = secToTime(theBeginSecondInput.value);
			}

			if (theChkEnd.checked == true){
				theEndSecondInput.value = Math.floor(theplayer.currentTime());
				theLinkInput.value = updateURLParameter(theLinkInput.value, 'stop', theEndSecondInput.value); // add param in url input
				theEndFakeInput.value = secToTime(theEndSecondInput.value);
			}

		}, 1000);
	});

	// Player "pause" click
	theplayer.on("pause", function() {
		if (theChkBegin.checked == true){
			clearInterval(PlayerRecordingLoop);
		}
	});

});


