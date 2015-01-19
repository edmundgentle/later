<?php
require_once('includes/view.php');
if(is_logged_in() and isset($_GET['id'])) {
	$user_id=get_user_id();
	$groups=get_groups(array('group_id'=>$_GET['id'],'manager'=>$user_id));
	$group=null;
	if(isset($groups[0])) {
		$group=$groups[0];
	}
	if(!is_null($group)) {
		if(isset($_POST['users'])) {
			if(!is_array($_POST['users'])) {
				$_POST['users']=array($_POST['users']);
			}
			$params=array(
				'group_id'=>$_GET['id']
			);
			if(isset($_GET['manager'])) {
				$params['managers']=$_POST['users'];
			}else{
				$params['members']=$_POST['users'];
			}
			
			$response=add_group_users($params);
			if($response) {
				redirect($group['link']);
			}
		}
		if(isset($_GET['manager'])) {
			view::$title="Add Group Manager";
			$addwhat="Managers";
		}else{
			view::$title="Add Group Member";
			$addwhat="Members";
		}
		view::header();
		$users=get_users();
		?>
		<script>
		$(function() {
			$("#form_users").chosen({
				disable_search_threshold: 10,
		    	no_results_text: "No results for",
				placeholder_text_multiple:"Select new <? echo $addwhat;?>..."
			});
		});
		</script>
		<div class="page-header">
		  <h1><? echo $group['name'];?> <small><? echo view::$title;?></small></h1>
		</div>
		<form class="form-horizontal" method="post" role="form">
			<div class="form-group">
				<label for="form_users" class="col-lg-2 control-label">New <? echo $addwhat;?></label>
				<div class="col-lg-10">
					<select name="users[]" multiple="multiple" class="form-control" id="form_users"><? foreach($users as $user) {?><option value="<? echo $user['user_id'];?>"><? echo $user['name'];?></option><? }?></select>
				</div>
			</div>
			<div class="form-group">
				<div class="col-lg-offset-2 col-lg-10">
					<button type="submit" class="btn btn-primary">Add</button>
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