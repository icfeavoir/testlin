function post(data, callback=function(){}){
	$.post( "actions.php", data).done(function( resp ){
		resp = JSON.parse(resp);
		if(resp.showMsg)
			showBar(resp.success, resp.msg);
		callback(resp);
	}, "json");
}

function showBar(isSuccess, msg){
	$('.ajax-response').css('visibility','visible').css('opacity', 1);
	$('.ajax-response p').html('<i class="fa fa-'+(isSuccess?"check":"exclamation-triangle")+'" aria-hidden="true"></i> '+msg);
	$('.ajax-response p').addClass(isSuccess?'alert-success':'alert-danger').removeClass(!isSuccess?'alert-success':'alert-danger');
	setTimeout(function(){$('.ajax-response').css('visibility','hidden').css('opacity', 0)}, 4000);
}

function openModal(file, data={}){
	$.post( file+".php", data).done(function( resp ){
		$('#modal .modal-content .modal-body').html(resp);
		$('#modal .modal-content .modal-title').html($(resp).filter('title').text());
		$('#modal').modal();
	});
}