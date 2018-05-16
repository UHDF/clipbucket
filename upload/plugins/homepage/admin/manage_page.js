var chpVideos = {
	ajaxUrl: '',
	tpl: '',
	page: 1,
	suggestedPage: 1,
	search: '',
	tags: '',
	titlePicked: '',
	titleUnpicked: '',
	litpl: '',
	table: $('table.table#chpvideos'),
	suggestedTable: $('#chpsuggestedvideos'),
	mgsgTag: null,
	
	init: function(){
		if(!($('table.table#chpvideos').length && $('#chpSelectedVideos #chplimodel').length)) return;
		
		this.titlePicked = $('#chpSelectedVideos #chplimodel').attr('data-picked');
		this.titleUnpicked = $('#chpSelectedVideos #chplimodel').attr('data-unpicked');
		this.litpl = $('#chpSelectedVideos #chplimodel').html();
		$('#chpSelectedVideos #chplimodel').remove();
		this.setTitle($('#chpSelectedVideos li:not(.picked) button.picked'), false);
		this.setTitle($('#chpSelectedVideos li.picked button.picked'), true);
		$('#chpSelectedVideos').sortable({
			placeholder: 'chp-state-highlight'
		});
		$('#chpSelectedVideos').disableSelection();
		
		$('#chpSelectedVideos').on('click', 'li .action .picked', this.setPick);
		$('#chpSelectedVideos').on('click', 'li .action .remove', this.removeVideo);
		
		this.tpl = $('table.table#chpvideos tr#chptrmodel').html();
		this.ajaxUrl = $('table.table#chpvideos tr#chptrmodel').attr('data-v');
		$('table.table#chpvideos tr#chptrmodel').remove();
		this.getVideos();
		
		this.getTags();
		
		$('#chpOrder').change(function(e){ chpVideos.getVideos(); });
		
		$('#chpSearchForm').submit(this.submitSearch);
		$('.chpnav').on('click', 'ul.pagination li a', function(e){
			e.preventDefault();
			var li = $(this).closest('li');
			if(!li.hasClass('disabled')){
				chpVideos.page = parseInt($(this).attr('data-p'));
				chpVideos.getVideos();
			}
			
			return false;
		});
		
		this.getSuggestedVideos();
		
		$('.chpnavsugg').on('click', 'ul.pagination li a', function(e){
			e.preventDefault();
			var li = $(this).closest('li');
			if(!li.hasClass('disabled')){
				chpVideos.suggestedPage = parseInt($(this).attr('data-p'));
				chpVideos.getSuggestedVideos();
			}
			
			return false;
		});
		
		$('#chpSuggestedOrder').change(function(e){ chpVideos.getSuggestedVideos(); });
		
		$('#chpvideos').on('click', '.action .selectVideo', this.addVideo);
		$('#chpsuggestedvideos').on('click', '.action .selectVideo', this.addVideo);
		
		$('#formManageHomepage').submit(this.submitForm);
	},
	
	setTitle: function(target, isPicked){
		target.attr('title', (isPicked ? chpVideos.titlePicked : chpVideos.titleUnpicked));
	},
	
	setPick: function(e){
		var btn = $(e.currentTarget);
		var li = btn.closest('li');
		if(li.hasClass('picked')){
			li.removeClass('picked');
			chpVideos.setTitle(btn, false);
		} else {
			li.addClass('picked');
			chpVideos.setTitle(btn, true);
		}
	},
	
	removeVideo: function(e){
		var li = $(e.currentTarget).closest('li');
		li.slideUp('fast', function(){
			if($(this).closest('ul').find('li').length === 1) $('#chpNoVideoSelected').slideDown('fast');
			$(this).remove();
			
			chpVideos.getVideos();
			chpVideos.getSuggestedVideos();
		});
		$('#chpSelectedVideos').sortable('refresh');
	},
	
	addVideo: function(e){
		var tr = $(e.currentTarget).closest('tr');
		var li = $('<li>').attr('data-v', tr.attr('data-v')).css('display', 'none').html(chpVideos.litpl);
		li.find('.date').html($.trim(tr.find('.date').html()));
		li.find('.title').html($.trim(tr.find('.title').html()));
		
		tr.slideUp('fast', function(){
			if($('#chpSelectedVideos li').length === 0){
				$('#chpNoVideoSelected').slideUp('fast', function(){
					$('#chpSelectedVideos').append(li);
					li.slideDown('fast');
					$('#chpSelectedVideos').sortable('refresh');
				});
			} else {
				$('#chpSelectedVideos').append(li);
				li.slideDown('fast');
				$('#chpSelectedVideos').sortable('refresh');
			}
			tr.remove();
		});
		
		var t = $('#chpvideos');
		if(tr.closest('table').attr('id') === 'chpvideos') t = $('#chpsuggestedvideos');
		else {
			var nb = $('#chpsuggestedvideos tr').length;
			if(nb === 1) $('#nbSuggestedVideos').html('').removeClass('badge');
			else $('#nbSuggestedVideos').html(nb);
		}
		t.find('tr[data-v="'+ tr.attr('data-v') +'"]').slideUp('fast');
	},
	
	getTags: function(){
		var _that = this;
		$.ajax({
			url: this.ajaxUrl.replace(/video\.php$/, 'tags.php'),
			method: 'POST',
			data: {action: 'getTags'},
			success: function(r){
				if(r === '') return;
				r = $.parseJSON(r);
				_that.mgsgTag = $('#chpTag').magicSuggest({
					allowFreeEntries: false,
					data: r,
					valueField: 'name',
					sortOrder: 'name',
					placeholder: 'Tags',
					renderer: function(data){ return data.name +' ('+ data.nb +')'; },
					noSuggestionText: $('#chpTag').attr('data-nores') +' : <strong>{{query}}</strong>'
				});
			}
		});
	},
	
	getVideos: function(){
		var _that = this;
		var ids = '';
		$('#chpSelectedVideos li').each(function(i){
			ids += ','+ $(this).attr('data-v');
		});
		ids = ids !== '' ? ids.substr(1) : ids;
		
		$.ajax({
			url: this.ajaxUrl,
			method: 'POST',
			data: {s: this.search, t: this.tags, p: this.page, ids: ids, order: $('#chpOrder').val()},
			success: function(r){
				if(r === '') return;
				r = $.parseJSON(r);
				_that.table.html('');
				
				if(r.videos.length === 0){
					$('#chpNoVideoFound').slideDown('fast');
				} else {
					$('#chpNoVideoFound').slideUp('fast');
					$.each(r.videos, function(i, v){
						var tr = $('<tr>').html(_that.tpl);
						tr.attr('data-v', v.id);
						tr.find('td.title').html(v.name);
						tr.find('td.date').html(v.date);
						_that.table.append(tr);
					});
				}
				
				$('.chpnav .pagination').html(r.pagination);
				$('html, body').animate({scrollTop: 0}, 'slow');
			}
		});
	},
	
	getSuggestedVideos: function(){
		var _that = this;
		var ids = '';
		$('#chpSelectedVideos li').each(function(i){
			ids += ','+ $(this).attr('data-v');
		});
		ids = ids !== '' ? ids.substr(1) : ids;
		
		$.ajax({
			url: this.ajaxUrl,
			method: 'POST',
			data: {sug_home: $('input[name="homeid"]').val(), p: this.suggestedPage, order: $('#chpSuggestedOrder').val(), ids: ids},
			success: function(r){
				if(r === '') return;
				r = $.parseJSON(r);
				_that.suggestedTable.html('');
				
				if(r.videos.length === 0){
					//$('#chpNoSuggestedVideoFound').slideDown('fast');
				} else {
					//$('#chpNoSuggestedVideoFound').slideUp('fast');
					$.each(r.videos, function(i, v){
						var tr = $('<tr>').html(_that.tpl);
						tr.attr('data-v', v.id);
						tr.find('td.title').html(v.name);
						tr.find('td.date').html(v.date);
						_that.suggestedTable.append(tr);
					});
				}
				if(r.pagination === '') $('.chpnavsugg').slideUp('fast');
				else $('.chpnavsugg').slideDown('fast');
				$('.chpnavsugg .pagination').html(r.pagination);
				if(r.total !== '0') $('#nbSuggestedVideos').html(r.total).addClass('badge');
				else $('#nbSuggestedVideos').html('').removeClass('badge');
				$('html, body').animate({scrollTop: 0}, 'slow');
			}
		});
	},
	
	submitSearch: function(e){
		e.preventDefault();
		
		var s = $(this).find('input[name="chpSearch"]').val();
		//if(s === '' && chpVideos.search === '') return false;
		chpVideos.search = s;
		chpVideos.tags = chpVideos.mgsgTag.getValue().join(',');
		chpVideos.page = 1;
		chpVideos.getVideos();
		return false;
	},
	
	submitForm: function(e){
		var ids = [];
		var picked = [];
		$('#chpSelectedVideos li').each(function(i){
			ids.push($(this).attr('data-v'));
			picked.push($(this).hasClass('picked') ? 1 : 0);
		});
		
		$(this).append('<input type="hidden" name="vids" value="'+ ids.join(',') +'" />');
		$(this).append('<input type="hidden" name="picked" value="'+ picked.join(',') +'" />');
		
		return true;
	}
};

$(function(){
	chpVideos.init();
});
