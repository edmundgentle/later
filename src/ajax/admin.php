<?php
require_once('../includes/db.php');
$output=array('success'=>false);
if(is_logged_in() and is_admin() and isset($_POST['method'])) {
	$method=$_POST['method'];
	if($method=='renamecat') {
		if(isset($_POST['cat_id']) and isset($_POST['name'])) {
			$cat_id=trim(stripslashes($_POST['cat_id']));
			$name=trim(stripslashes($_POST['name']));
			mysql_query("UPDATE categories SET name='".mysql_real_escape_string($name)."' WHERE category_id='".mysql_real_escape_string($cat_id)."'");
			if(mysql_affected_rows()==1) {
				$output['success']=true;
			}
		}
	}
	if($method=='removecat') {
		if(isset($_POST['cat_id'])) {
			$cat_id=trim(stripslashes($_POST['cat_id']));
			mysql_query("DELETE FROM categories WHERE category_id='".mysql_real_escape_string($cat_id)."'");
			if(mysql_affected_rows()==1) {
				mysql_query("DELETE FROM messages WHERE category_id='".mysql_real_escape_string($cat_id)."'");
				$output['success']=true;
			}
		}
	}
	if($method=='removemessage') {
		if(isset($_POST['message_id'])) {
			$mess_id=trim(stripslashes($_POST['message_id']));
			mysql_query("DELETE FROM messages WHERE message_id='".mysql_real_escape_string($mess_id)."'");
			if(mysql_affected_rows()==1) {
				$output['success']=true;
			}
		}
	}
	
}
echo json_encode($output);
?>