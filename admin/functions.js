function post(data, account=0, callback=function(){}){
	$.post( "actions.php?account="+account, data).done(function( resp ){
		resp = JSON.parse(resp);
		if(resp.log) console.log(resp.log);
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

function openModal(file, account, data={}, newModal=false){
	$.post( file+".php?account="+account, data).done(function( resp ){
		$('#modal .modal-content .modal-body').html(resp);
		$('#modal .modal-content .modal-title').html($(resp).filter('title').text());
		if(newModal)
			$('#modal').modal();
	});
}

function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};
