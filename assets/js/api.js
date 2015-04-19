$(function() {
	$('#shorten-btn').click(function(){
		$('#shorten-btn').click();
	});
	
	$('#form1').submit(function(event){
		event.preventDefault();
		request();
	});
	
});

function hideError() {
	$('#error-msg').slideUp('slow');
}

function request() {
		$.getJSON( "api.php",{url:$('#url').val()}, function( data ) {
		  if (data.error) {
			  $('#error-msg-text').html(data.error);
			  $('#error-msg').slideDown(function(){
				  setTimeout('hideError()',10000);
			  });
		  } else {
			  showResult(data.data);
		  }
		});	
}

function showResult(data) {
	$('#result').slideUp(function(){
		$('#result #result-img').attr('src', 'http://api.webthumbnail.org/?width=250&height=250&screen=1024&url=' + encodeURI(data.url));
		$('#result #result-qr').attr('src', '//chart.googleapis.com/chart?cht=qr&chs=100x100&choe=UTF-8&chld=H|0&chl=' + encodeURI(data.shortcode));
		$('#result #surl').val(data.shortcode);
		$('#result #result-url').prop('href', data.url);
		$('#result #result-url').text(data.url);
		$('#result #result-created').text(data.created);
		$('#result #result-clicks').text(data.clicks);
		
		$('#result').slideDown(function(){
		});
	});
}