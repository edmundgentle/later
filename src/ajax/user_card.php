<?php
require_once('../includes/db.php');
if(isset($_GET['id'])) {
	$user_id=$_GET['id'];
	$users=get_users(array('user'=>$user_id));
	if(isset($users[0])) {
		$user=$users[0];
		$bio='';
		if(!is_null($user['stage'])) {
			$bio.='Stage '.$user['stage'].' '.$user['course'].' student';
		}else{
			$bio.=$user['course'];
		}
		echo json_encode(array(
			'name'=>$user['name'],
			'bio'=>$bio,
			'department'=>$user['department']
		));
	}
}
?>