<!DOCTYPE html>
<html>
	<head>
		<title>Template Manager</title>
	</head>
	<body>

		<div class="ajax-response">
			<p class="alert"></p>
		</div>

		<table class="table table-striped table-hover template-manager">
			<tr>
				<th>Activate</th>
				<th>ID</th>
				<th>Message</th>
				<th>Created</th>
				<th>More</th>
				<th>Delete</th>
			</tr>
		</table>


	</body>
</html>


<script>
$(document).ready(function(){
	// all templates
	post({'action':'getAllTemplates'}, function(resp){
		$.each(resp.templates, function(key, value){
			$('.template-manager').append('<tr id="'+value.ID+'"><td><div class="checkbox"><label><input type="checkbox" class="active" value="'+value.ID+'" '+(value.active==1?'checked':'')+'></label></div></td><td>'+value.ID+'</td><td>'+value.msg+'</td><td>'+value.created+'</td><td><i class="fa fa-plus plus"></i></td><td><i class="fa fa-trash delete"></i></td></tr>');
			// show more
			$('.template-manager tr[id="'+value.ID+'"] i.plus').click(function(){
				openModal('template_unique', {'template':value.ID});
			});
			// delete
			$('.template-manager tr[id="'+value.ID+'"] i.delete').click(function(){
				var clicked = $(this);
				bootbox.confirm({
				    message: "Are you sure you want to delete this template : "+value.ID,
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
				        	post({'action':'delete', 'table':'msg_template', 'id':value.ID});
				        }
				    }
				});
			});
			//change active
			$('.template-manager tr[id="'+value.ID+'"] .active').click(function(){
				post({'action': 'changeTemplateState', 'state': $(this).prop('checked'), 'id':$(this).val()});
			});
		});
	});
});
</script>