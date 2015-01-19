<?php
require_once('includes/view.php');
if(isset($_GET['id'])) {
	$user_id=$_GET['id'];
	$users=get_users(array('user'=>$user_id));
	if(isset($users[0])) {
		$user=$users[0];
		view::$title=$user['name'];
		view::header();?>
		<div class="page-header">
		  <h1><? echo $user['name'];?></h1>
		</div>
		<div class="user_bio"><? if(!is_null($user['stage'])) {?>Stage <? echo $user['stage'];?> <? echo $user['course'];?> student<? }else{echo $user['course'];}?></div>
		<div class="user_department"><strong>Department:</strong> <? echo $user['department'];?></div>
	<? view::footer();
	}
}
?>