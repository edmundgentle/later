<?php
require_once('includes/view.php');
if(is_logged_in() and isset($_GET['id'])) {
	if(isset($_GET['user'])) {
		$groups=get_groups(array('group_id'=>$_GET['id'],'manager'=>get_user_id()));
		$group=null;
		if(isset($groups[0])) {
			$group=$groups[0];
			if(!$group['admin_id']) {
				$group=null;
			}
		}
		if(isset($_GET['manager'])) {
			//cancel if the current user is not the admin or trying to delete themselves - and the admin trying 
			if(($group['admin_id']==get_user_id() or $_GET['id']==get_user_id()) and $group['admin_id']!=$_GET['id']) {
				
			}else{
				$group=null;
			}
		}
		if(!is_null($group)) {
			$params=array(
				'group_id'=>$_GET['id']
			);
			if(isset($_GET['manager'])) {
				$params['manager_id']=$_GET['user'];
			}else{
				$params['member_id']=$_GET['user'];
			}
			$response=remove_from_group($params);
		}
		redirect($group['link']);
	}
	$groups=get_groups(array('group_id'=>$_GET['id']));
	$group=null;
	if(isset($groups[0])) {
		$group=$groups[0];
		if(!$group['admin_id']) {
			$group=null;
		}
	}
	if(!is_null($group)) {
		$user_id=get_user_id();
		if(isset($_POST['submitted'])) {
			if($_POST['submitted']!="Yes") {
				redirect(href('groups'));
			}
			$params=array(
				'member_id'=>$user_id,
				'group_id'=>$_GET['id'],
			);
			$response=remove_from_group($params);
			if($response) {
				redirect(href('groups'));
			}
		}
		view::$title="Remove from Group";
		view::header();
		?>
		<form method="post">
		<div class="panel panel-danger">
			<div class="panel-heading">
				<h3 class="panel-title">Remove from Group</h3>
			</div>
			<div class="panel-body">
				<div align="center">Are you sure you want to be removed from the group <strong><? echo $group['name'];?></strong>?</div>
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