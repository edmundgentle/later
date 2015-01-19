<?php
require_once('includes/view.php');
if(is_logged_in()) {
	$user_id=get_user_id();
	if(isset($_POST['name']) and isset($_POST['users'])) {
		if(!isset($_POST['managers'])) {
			$_POST['managers']=array();
		}
		if(!is_array($_POST['managers'])) {
			$_POST['managers']=array($_POST['managers']);
		}
		if(!is_array($_POST['users'])) {
			$_POST['users']=array($_POST['users']);
		}
		$params=array(
			'admin_id'=>$user_id,
			'name'=>trim(stripslashes($_POST['name'])),
			'managers'=>$_POST['managers'],
			'members'=>$_POST['users']
		);
		$response=insert_group($params);
		if(isset($response['group_id'])) {
			redirect(href('groups'));
		}
	}
	view::$title="Create Group";
	view::header();
	$users=get_users();
	?>
	<script>
	$(function() {
		$("#form_managers").chosen({
			disable_search_threshold: 10,
	    	no_results_text: "No results for",
			placeholder_text_multiple:"Select message recipients..."
		});
		$("#form_users").chosen({
			disable_search_threshold: 10,
	    	no_results_text: "No results for",
			placeholder_text_multiple:"Select message recipients..."
		});
	});
	</script>
	<div class="page-header">
	  <h1>Create a group</h1>
	</div>
	<form method="post" role="form" class="form-horizontal">
		<div class="form-group">
			<label for="form_name" class="col-lg-2 control-label">Group Name</label>
			<div class="col-lg-10">
				<input type="text" class="form-control" name="name" id="form_name" value="<? if(isset($_POST['name'])) echo $_POST['name'];?>" placeholder="Group Name" />
			</div>
		</div>
		<div class="form-group">
			<label for="form_managers" class="col-lg-2 control-label">Group Managers</label>
			<div class="col-lg-10">
				<select name="managers[]" class="form-control" id="form_managers" multiple="multiple"><? foreach($users as $user) {?><option value="<? echo $user['user_id'];?>"><? echo $user['name'];?></option><? }?></select>
			</div>
		</div>
		<div class="form-group">
			<label for="form_users" class="col-lg-2 control-label">Group Members</label>
			<div class="col-lg-10">
				<select name="users[]" class="form-control" id="form_users" multiple="multiple"><? foreach($users as $user) {?><option value="<? echo $user['user_id'];?>"><? echo $user['name'];?></option><? }?></select>
			</div>
		</div>
		<div class="form-group">
			<div class="col-lg-offset-2 col-lg-10">
				<button type="submit" class="btn btn-primary">Add Group</button>
			</div>
		</div>
	</form>
	<?
	view::footer();
}else{
	login(href('groups'));
}
?>