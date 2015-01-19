<?php
require_once('../includes/db.php');
$output=array('success'=>false);
if(is_logged_in() and isset($_POST['id'])) {
	$id=trim(stripslashes($_POST['id']));
	$messages=get_messages(array('message_id'=>$id));
	if(isset($messages[0])) {
		$message=$messages[0];
		if(is_admin() or $message['sender_id']==get_user_id()) {
			mysql_query("DELETE FROM messages WHERE message_id='".mysql_real_escape_string($id)."'");
			if(mysql_affected_rows()==1) {
				$output['success']=true;
			}
		}
	}
}
echo json_encode($output);
?>