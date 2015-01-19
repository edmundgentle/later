<?php
require_once('../includes/db.php');
if(is_logged_in() and isset($_POST['id'])) {
	$user_id=get_user_id();
	$groups=get_groups(array('group_id'=>$_POST['id'],'admin_id'=>$user_id));
	$group=null;
	if(isset($groups[0])) {
		$group=$groups[0];
	}
	if(!is_null($group)) {
		if(isset($_POST['name'])) {
			$params=array(
				'group_id'=>$_POST['id'],
				'name'=>trim(stripslashes($_POST['name']))
			);
			$response=edit_group($params);
			if($response) {
				echo json_encode(array('success'=>true));
				exit();
			}
		}
	}
}
echo json_encode(array('success'=>false));
?>