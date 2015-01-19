<?php
require_once('../includes/view.php');
if(is_admin()) {
	view::$title="Manage messages";
	view::header();
	$messages=get_messages(array('since'=>strtotime("-1 month")));?>
	<script>
	var mess_id=null;
	$(function() {
		$('.btn_removemessage').click(function(e) {
			e.preventDefault();
			mess_id=$(this).attr('data-messageid');
			$('#removemessage_modal').modal('show');
		});
		$('#removemessage_modal').find('.btn-danger').click(function(e) {
			$.ajax({
				url: "<? echo href('ajax/admin.php');?>",
				dataType:'json',
				type:'POST',
				data: {method:'removemessage', message_id: mess_id}
			}).done(function(data) {
				if(data.success!==undefined && data.success) {
					$('#message_'+mess_id).remove();
				}
			});
			$('#removemessage_modal').modal('hide');
		});
	});
	</script>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Messages</h3>
		</div>
		<ul class="list-group">
		<? foreach($messages as $message) {?>
			<li class="list-group-item" id="message_<? echo $message['message_id'];?>">
				<div class="btn-group btn-group-xs pull-right">
				  <a href="" class="btn btn-danger btn_removemessage" data-messageid="<? echo $message['message_id'];?>">Delete</a>
				</div>
				<h4 class="list-group-item-heading"><a href="<? echo $message['link'];?>"><? echo $message['subject'];?></a></h4>
				<div class="list-group-item-text">
					<div class="message_info"><? echo format_date($message['date']);?> · By <a href="<? echo $message['sender_link'];?>" data-userid="<? echo $message['sender_id'];?>"><? echo $message['sender_name'];?></a> in <a href="<? echo $message['category_link'];?>"><? echo $message['category_name'];?></a></div>
					<? echo $message['excerpt'];?>
				</div>
			</li>
		<? 	}?>
		</ul>
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
						<div align="center">Are you sure you want to remove this message?</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
					<button type="button" class="btn btn-danger">Yes</button>
				</div>
			</div>
		</div>
	</div>
	<? 
	view::footer();
}?>