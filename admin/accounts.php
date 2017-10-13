<!DOCTYPE html>
<html>
	<head>
		<title>Account Manager</title>
	</head>
	<body>

		<div class="ajax-response">
			<p class="alert"></p>
		</div>

		<div>
			<input class="form-control" type="text" placeholder="Email" id="email" /><br/>
			<input class="form-control" type="text" placeholder="Password" id="password" /><br/>
			<button class="btn btn-md btn-primary" id="newAccount">Save account</button>
		</div>
		<br />
		<table class="table table-striped table-hover account-manager">
			<tr>
				<th>Active</th>
				<th>ID</th>
				<th>Email</th>
				<th>Password</th>
				<th>Delete</th>
			</tr>
		</table>


	</body>
</html>


<script>
$(document).ready(function(){
	var selectedAccount = getUrlParameter('account');
	$('#newAccount').click(function(){
		if($('#email').val() != '' && $('#password').val() != ''){
			var newId = -1;
			var email = $('#email').val();
			var password = $('#password').val();
			post({'action': 'saveNewAccount', 'email': email, 'password': password}, selectedAccount);
			console.log('ok');
		}
	});
	// all accounts
	post({'action':'getAllAccounts'}, selectedAccount, function(resp){
		$.each(resp.value, function(key, value){
			$('.account-manager').append('<tr id="'+value.ID+'"><td><div class="checkbox"><label><input type="checkbox" class="active" value="'+value.ID+'" '+(value.active==1?'checked':'')+'></label></div></td><td>'+value.ID+'</td><td>'+value.email+'</td><td>'+value.password+'</td><td><i class="fa fa-trash delete"></i></td></tr>');
			// delete
			$('.account-manager tr[id="'+value.ID+'"] i.delete').click(function(){
				var clicked = $(this);
				bootbox.confirm({
				    message: "Are you sure you want to delete this account : "+value.email,
				    buttons: {
				        confirm: {
				            label: 'Yes',
				            className: 'btn-success'
				        },
				        cancel: {
				            label: 'No',
				            className: 'btn-danger'
				        }
				    },
				    callback: function (result) {
				        if(result){
				        	clicked.parents('tr').remove();
				        	post({'action':'deleteAccount', 'id':value.ID}, selectedAccount);
				        }
				    }
				});
			});
			//change active
			$('.account-manager tr[id="'+value.ID+'"] .active').click(function(){
				post({'action': 'changeAccountActive', 'state': $(this).prop('checked'), 'id':$(this).val()}, selectedAccount);
			});
		});
	});
});
</script>