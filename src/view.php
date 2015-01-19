<?php
require_once('includes/view.php');
if(isset($_GET['slug'])) {
	$slug=$_GET['slug'];
	$messages=get_messages(array('slug'=>$slug));
	if(isset($messages[0])) {
		$message=$messages[0];
		view::$title=$message['subject'];
		view::header();?>
		<script>
		var mess_id='<? echo $message['message_id'];?>';
		$(function() {
			$('#mf_remove_message').click(function(e) {
				e.preventDefault();
				$('#removemessage_modal').modal('show');
			});
			$('#removemessage_modal').find('.btn-danger').click(function(e) {
				$.ajax({
					url: "<? echo href('ajax/delete_message.php');?>",
					dataType:'json',
					type:'POST',
					data: {id: mess_id}
				}).done(function(data) {
					if(data.success!==undefined && data.success) {
						window.location.replace("<? echo href('');?>");
					}
				});
				$('#removemessage_modal').modal('hide');
			});
		});
		</script>
		<div class="page-header">
		  <h1><? echo $message['subject'];?></h1>
		</div>
		<div class="message_date"><? echo format_date($message['date']);?> · By <a href="<? echo $message['sender_link'];?>"data-userid="<? echo $message['sender_id'];?>"><? echo $message['sender_name'];?></a> in <a href="<? echo $message['category_link'];?>"><? echo $message['category_name'];?></a></div>
		<div class="panel">
			<div class="panel-body">
				<? echo $message['body'];?>
			</div>
		</div>
		<?
		if(is_admin() or get_user_id()==$message['sender_id']) {?>
			<div class="btn-group">
			  <a href="<? echo $message['link'];?>edit/" class="btn btn-primary">Edit Message</a>
			  <a id="mf_remove_message" href="" class="btn btn-danger">Delete Message</a>
			</div>
			<div id="removemessage_modal" class="modal fade">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title">Remove Message</h4>
						</div>
						<div class="modal-body">
							<div class="form-horizontal">
								<div align="center">Are you sure you want to remove this message? Remember, once it's gone, it's gone.</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
							<button type="button" class="btn btn-danger">Yes</button>
						</div>
					</div>
				</div>
			</div>
		<? }
	 	view::footer();
	}else{
		login(href($_GET['category'].'/'.$_GET['slug']));
	}
}
?>