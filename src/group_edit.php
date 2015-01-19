<?php
require_once('includes/view.php');
if(is_logged_in() and isset($_GET['id'])) {
	$user_id=get_user_id();
	$groups=get_groups(array('group_id'=>$_GET['id'],'admin_id'=>$user_id));
	$group=null;
	if(isset($groups[0])) {
		$group=$groups[0];
	}
	if(!is_null($group)) {
		if(isset($_POST['name'])) {
			$params=array(
				'group_id'=>$_GET['id'],
				'name'=>trim(stripslashes($_POST['name']))
			);
			$response=edit_group($params);
			if($response) {
				redirect($group['link']);
			}
		}
		view::$title="Edit Group";
		view::header();
		?>
		<div class="page-header">
		  <h1><? echo $group['name'];?> <small>Edit Group</small></h1>
		</div>
		<form class="form-horizontal" method="post" role="form">
			<div class="form-group">
				<label for="form_name" class="col-lg-2 control-label">Name</label>
				<div class="col-lg-10">
					<input type="text" class="form-control" name="name" id="form_name" value="<? echo $group['name'];?>" placeholder="name" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-lg-offset-2 col-lg-10">
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</div>
		</form>
		<?
		view::footer();
	}
}else{
	login(href('groups'));
}
?>