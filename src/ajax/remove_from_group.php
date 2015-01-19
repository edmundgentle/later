<?php
require_once('../includes/db.php');
if(is_logged_in() and isset($_POST['id'])) {
	if(!isset($_POST['user'])) {
		$params=array(
			'member_id'=>get_user_id(),
			'group_id'=>$_POST['id']
		);
		$response=remove_from_group($params);
		if($response) {
			echo json_encode(array('success'=>true));
			exit();
		}
	}else{
		$groups=get_groups(array('group_id'=>$_POST['id'],'manager'=>get_user_id()));
		$group=null;
		if(isset($groups[0])) {
			$group=$groups[0];
			if(!$group['admin_id']) {
				$group=null;
			}
		}
		if(isset($_POST['manager'])) {
			if(($group['admin_id']==get_user_id() or $_POST['id']==get_user_id()) and $group['admin_id']!=$_POST['id']) {
				
			}else{
				$group=null;
			}
		}
		if(!is_null($group)) {
			$params=array(
				'group_id'=>$_POST['id']
			);
			if(isset($_POST['manager'])) {
				$params['manager_id']=$_POST['user'];
			}else{
				$params['member_id']=$_POST['user'];
			}
			$response=remove_from_group($params);
			if($response) {
				echo json_encode(array('success'=>true));
				exit();
			}
		}
	}
}
echo json_encode(array('success'=>false));
?>