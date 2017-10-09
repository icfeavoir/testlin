<!DOCTYPE html>
<html>
	<?php 
		require_once('../const.php');
		require_once('../db.php');

		if(!isset($_POST['template']))
			exit('no template selected');
		else
			$template = $_POST['template'];
	?>
	<head>
		<title>Message Template (ID = <?php echo $template; ?>)</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="style.css">

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  		<script src="functions.js"></script>
	</head>
	<body>

		<div class="ajax-response">
			<p class="alert"></p>
		</div>

		<div><button id="back" class="btn btn-sm btn-primary"><i class="fa fa-arrow-left"></i> Back</button></div>

		<p><b>Message: </b><br/><span class="to-load" id="getMessage"></span></p>
		<br/>
		<table class="table table-md table-striped table-hover unique-template-manager">
			<tr>
				<th>Number of messages sent</th>
				<td class="to-load" id="getNumberSent"></td>
			</tr>
			<tr>
				<th>Number of response</th>
				<td class="to-load" id="getNumberReceived"></td>
			</tr>
		</table>


	</body>
</html>


<script>
$(document).ready(function(){
	var template = '<?=$template?>';
	$('.to-load').html('<i class="fa fa-circle-o-notch fa-spin""></i>');
	$('.to-load').each(function(){
		var tag = $(this);
		post({'action':$(this).attr('id'), 'template':template}, 0, function(resp){
			tag.html(resp.value);
		});
	});

	$('#back').click(function(){
		openModal('template');
	});
});
</script>