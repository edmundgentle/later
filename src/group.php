<?php
require_once('includes/view.php');
if(isset($_GET['id']) and is_logged_in()) {
	$group_id=$_GET['id'];
	$user_id=get_user_id();
	$groups=get_groups(array('group_id'=>$group_id,'manager'=>$user_id));
	if(isset($groups[0])) {
		$group=$groups[0];
		view::$title=$group['name'];
		view::header();
		?>
		<script>
		var group_id="<? echo $group['group_id'];?>";
		var group=<? echo json_encode($group);?>;
		$(function() {
			$('a.remove_from_group_member').click(function(e) {
				var t=$(this);
				if(t.attr('data-uid')!==undefined) {
					e.preventDefault();
					var user_id=t.attr('data-uid');
					$.ajax({
						url: "<? echo href('ajax');?>remove_from_group.php",
						dataType:'json',
						type:'POST',
						data: {id: group_id, user: user_id}
					}).done(function(data) {
						if(data.success!==undefined && data.success) {
							$('#gmem_'+user_id).remove();
						}
					});
				}
			});
			$('a.remove_from_group_manager').click(function(e) {
				var t=$(this);
				if(t.attr('data-uid')!==undefined) {
					e.preventDefault();
					var user_id=t.attr('data-uid');
					$.ajax({
						url: "<? echo href('ajax');?>remove_from_group.php",
						dataType:'json',
						type:'POST',
						data: {id: group_id, user: user_id,manager:true}
					}).done(function(data) {
						if(data.success!==undefined && data.success) {
							$('#gman_'+user_id).remove();
						}
					});
				}
			});
			$('#btn_renamegroup').click(function(e) {
				e.preventDefault();
				if(group!==null) {
					$('#modal_form_name').val(group.name);
					$('#renamegroup_modal').modal('show');
				}
			});
			$('#renamegroup_modal').find('.btn-primary').click(function(e) {
				var name=$('#modal_form_name').val();
				if(name!=null) {
					$.ajax({
						url: "<? echo href('ajax');?>rename_group.php",
						dataType:'json',
						type:'POST',
						data: {id: group_id, name:name}
					}).done(function(data) {
						if(data.success!==undefined && data.success) {
							$('.page-header h1').html(name+" <small>Group</small>");
						}
					});
					$('#renamegroup_modal').modal('hide');
				}
			});
			$('#btn_removegroup').click(function(e) {
				e.preventDefault();
				if(group!==null) {
					$('#removegroup_modal').modal('show');
				}
			});
			$('#removegroup_modal').find('.btn-danger').click(function(e) {
				$.ajax({
					url: "<? echo href('ajax');?>remove_group.php",
					dataType:'json',
					type:'POST',
					data: {id: group_id}
				}).done(function(data) {
					if(data.success!==undefined && data.success) {
						window.location.replace("<? echo href('groups');?>");
					}
				});
				$('#removegroup_modal').modal('hide');
			});
		});
		</script>
		<div class="page-header">
		  <h1><? echo $group['name'];?> <small>Group</small></h1>
		</div>
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">Group members</h3>
			</div>
			<? if($group['admin_id']==0) {?>
				<div class="panel-body" align="center">This is an automatically generated group. Therefore, the group members can't be altered.</div>
			<? }else{?>
			<ul class="list-group">
			<? 
			$members=get_group_members(array('group_id'=>$group_id));
			foreach($members as $member) {?>
				<li id="gmem_<? echo $member['user_id'];?>" class="list-group-item">
					<a href="<? echo $group['link'];?>remove/<? echo $member['user_id'];?>/" class="btn btn-danger btn-xs pull-right remove_from_group_member" data-uid="<? echo $member['user_id'];?>">Remove</a>
					<div class="user_name"><a href="<? echo $member['link'];?>" data-userid="<? echo $member['user_id'];?>"><? echo $member['name'];?></a></div>
					<em><? echo $member['email'];?></em>
				</li>
			<? }?>
			</ul>
			<div class="panel-footer"><a href="<? echo $group['link'];?>add/" class="btn btn-success">Add a member</a></div>
			<? }?>
		</div>
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">Group Managers</h3>
			</div>
			<ul class="list-group">
			<? 
			$members=get_group_members(array('group_id'=>$group_id,'managers'=>true));
			foreach($members as $member) {?>
				<li id="gman_<? echo $member['user_id'];?>" class="list-group-item">
					<? if($member['user_id']!=$group['admin_id']) {?>
						<a href="<? echo $group['link'];?>remove/manager/<? echo $member['user_id'];?>/" class="btn btn-danger btn-xs pull-right remove_from_group_manager" data-uid="<? echo $member['user_id'];?>">Remove</a>
					<? }?>
					<div class="user_name"><a href="<? echo $member['link'];?>" data-userid="<? echo $member['user_id'];?>"><? echo $member['name'];?></a></div>
					<em><? echo $member['email'];?></em>
				</li>
			<? }?>
			</ul>
			<div class="panel-footer"><a href="<? echo $group['link'];?>add/manager/" class="btn btn-success">Add a manager</a></div>
		</div>
		<? if($user_id==$group['admin_id']) {?>
		<div class="btn-group">
			<a href="<? echo $group['link'];?>edit/" class="btn btn-default" id="btn_renamegroup">Rename group</a>
			<a href="<? echo $group['link'];?>delete/" class="btn btn-default" id="btn_removegroup">Remove group</a>
		</div>
		<div id="renamegroup_modal" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">Rename Group</h4>
					</div>
					<div class="modal-body">
						<div class="form-horizontal">
							<div class="form-group">
								<label for="form_name" class="col-lg-2 control-label">Name</label>
								<div class="col-lg-10">
									<input type="text" class="form-control" name="name" id="modal_form_name" placeholder="name" />
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-primary">Save</button>
					</div>
				</div>
			</div>
		</div>
		<div id="removegroup_modal" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">Remove Group</h4>
					</div>
					<div class="modal-body">
						<div class="form-horizontal">
							<div align="center">Are you sure you want to remove this group?</div>
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
	}
}else{
	login(href('groups'));
}
?>