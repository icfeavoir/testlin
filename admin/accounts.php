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
			post({'action': 'saveNewAccount', 'email': email, 'password': password}, selectedAccount, function(resp){
				newId = resp.newId;
				$('.account-manager').append('<tr id="'+newId+'"><td>'+newId+'</td><td>'+email+'</td><td>'+password+'</td><td><i class="fa fa-trash delete"></i></td></tr>');
				// delete
				$('.account-manager tr[id="'+newId+'"] i.delete').click(function(){
					var clicked = $(this);
					bootbox.confirm({
					    message: "Are you sure you want to delete this account : "+email,
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
					        	post({'action':'delete', 'table':'accounts', 'id':newId}, selectedAccount);
					        }
					    }
					});
				});
			});
			$('#email').val('');
			$('#password').val('');
		}
	});
	// all accounts
	post({'action':'getAllAccounts'}, selectedAccount, function(resp){
		$.each(resp.value, function(key, value){
			$('.account-manager').append('<tr id="'+value.ID+'"><td>'+value.ID+'</td><td>'+value.email+'</td><td>'+value.password+'</td><td><i class="fa fa-trash delete"></i></td></tr>');
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
				        	post({'action':'delete', 'table':'accounts', 'id':value.ID}, selectedAccount);
				        }
				    }
				});
			});
		});
	});
});
</script>