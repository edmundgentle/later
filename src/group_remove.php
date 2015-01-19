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
		if(isset($_POST['submitted'])) {
			if($_POST['submitted']!="Yes") {
				redirect($group['link']);
			}
			$response=remove_group($_GET['id']);
			if($response) {
				redirect(href('groups'));
			}
		}
		view::$title="Remove Group";
		view::header();
		?>
		<form method="post">
		<div class="panel panel-danger">
			<div class="panel-heading">
				<h3 class="panel-title">Remove Group</h3>
			</div>
			<div class="panel-body">
				<div align="center">Are you sure you want to remove the group <strong><? echo $group['name'];?></strong>?</div>
			</div>
			<div class="panel-footer" align="center"><input type="submit" value="Yes" name="submitted" class="btn btn-danger" /> <input type="submit" value="No" name="submitted" class="btn btn-default" /></div>
		</div>
		</form>
		<?
		view::footer();
	}
}else{
	login(href('groups'));
}
?>