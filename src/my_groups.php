<?php
require_once('includes/view.php');
if(is_logged_in()) {
	view::$title="My Groups";
	view::header();
	$params=array('member'=>get_user_id());
	$groups=get_groups($params);?>
	<script>
	var my_groups=<? echo json_encode($groups);?>;
	var group_id=null;
	$(function() {
		$('a.remove_from_group').click(function(e) {
			var t=$(this);
			if(t.attr('data-groupid')!==undefined) {
				e.preventDefault();
				group_id=t.attr('data-groupid');
				var group=null;
				for(var x=0;x<my_groups.length;x++) {
					if(my_groups[x].group_id==group_id) {
						group=my_groups[x];
						break;
					}
				}
				if(group!==null) {
					$('#removefromgroup_modal').find('.removegroupname').html(group.name);
					$('#removefromgroup_modal').modal('show');
				}
			}
		});
		$('#removefromgroup_modal').find('.btn-danger').click(function(e) {
			if(group_id!=null) {
				$.ajax({
					url: "<? echo href('ajax');?>remove_from_group.php",
					dataType:'json',
					type:'POST',
					data: {id: group_id}
				}).done(function(data) {
					if(data.success!==undefined && data.success) {
						$('#mg_'+group_id).remove();
					}
				});
				$('#removefromgroup_modal').modal('hide');
			}
		});
	});
	</script>
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Your groups</h3>
		</div>
		<? if(count($groups)) {?>
		<ul class="list-group">
			<? foreach($groups as $group) {?>
			<li id="mg_<? echo $group['group_id'];?>" class="list-group-item">
				<? echo $group['name'];?> <span class="label label-default"><? echo pluralise($group['num_members'],'Member');?></span>
				<? if($group['admin_id']) {?>
					<a href="<? echo $group['link'];?>remove/" class="btn btn-danger btn-xs pull-right remove_from_group" data-groupid="<? echo $group['group_id'];?>">Remove me</a>
				<? }?>
			</li>
			<? }?>
		</ul>
		<? }else{?>
			<div class="panel-body"><div align="center">You aren't in any groups yet.</div></div>
		<? }?>
	</div>
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Groups you manage</h3>
		</div>
			<?php
			$params=array('manager'=>get_user_id());
			$groups=get_groups($params);
			if(count($groups)) {?>
				<ul class="list-group">
			<? foreach($groups as $group) {?>
				<li class="list-group-item">
					<? echo $group['name'];?> <span class="label label-default"><? echo pluralise($group['num_members'],'Member');?></span><? if($group['admin_id']==0) {?> <span class="label label-info" data-toggle="tooltip" title="This is an automatically generated group">Auto</span><? }?> <a href="<? echo $group['link'];?>" class="btn btn-primary btn-xs pull-right">Manage</a>
				</li>
			<? }?>
			</ul>
			<? }else{?>
				<div class="panel-body"><div align="center">You don't manage any groups yet.</div></div>
			<? }?>
		<div class="panel-footer"><a href="<? echo href('groups/create');?>" class="btn btn-success">Create a Group</a></div>
	</div>
	<div id="removefromgroup_modal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">Remove from Group</h4>
				</div>
				<div class="modal-body">
					<p align="center">Are you sure you want to be removed from the group <strong class="removegroupname"></strong>?</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
					<button type="button" class="btn btn-danger">Yes</button>
				</div>
			</div>
		</div>
	</div>
<? 	view::footer();
}else{
	login(href('groups'));
}
?>