<?php
require_once('../includes/db.php');
//summary emails
$start_time=strtotime("-7 days");
$messages = get_messages(array('since'=>$start_time));
$keyedmessages=array();
$usermessages=array();
foreach($messages as $message) {
	$keyedmessages[$message['message_id']]=$message;
	$result=mysql_query("SELECT users.user_id, first_name, last_name, email FROM group_users, users WHERE group_users.user_id=users.user_id AND group_users.group_id IN (SELECT group_id FROM message_recipients WHERE message_recipients.message_id='".mysql_real_escape_string($message['message_id'])."') AND (SELECT COUNT(preference) FROM user_preferences WHERE user_preferences.user_id=users.user_id AND user_preferences.category_id='".mysql_real_escape_string($message['category_id'])."' AND user_preferences.preference='summary')=1");
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$usermessages[$row['user_id']]['user']=$row;
		$usermessages[$row['user_id']]['messages'][]=$message['message_id'];
	}
}
foreach($usermessages as $user) {
	$body="<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body>
	<style type=\"text/css\">body {margin:0;padding:0;background-color:#eee;}a {color: #428bca;text-decoration: none;}.body {background-color:#eee;padding:20px;font-family: Helvetica Neue,Helvetica,Arial,sans-serif;font-size: 14px;line-height: 1.428571429;color: #333;}.container {background-color:#fff;margin-left:auto;margin-right:auto;max-width:500px;border-radius:3px;overflow:hidden;box-shadow:0 0 4px rgba(0,0,0,0.2);}.container .header {background-color:#428bca;color:#FFF;padding:3px;text-align:center;}.container h1 {margin:0;padding:0;font-size:20px;font-weight:400;}.message_list {list-style:none;margin:0;padding:1px;}.message_list li {padding:4px;border-bottom:1px solid #ddd;}.message_list li:last-child {border:0;}.message_list li .message_name {font-size:18px;}.message_list li .message_info {font-size:12px;}.message_list li .message_excerpt {padding:2px;padding-left:3px;border-left:3px solid #eee;}.container .email_footer {border-top:3px solid #eee;margin:4px;padding:4px;padding-top:7px;text-align:center;font-size:12px;}</style>
<div class=\"body\"><div class=\"container\"><div class=\"header\"><h1>Your email summary for this week</h1></div><ul class=\"message_list\">";
	foreach($user['messages'] as $m) {
		if(isset($keyedmessages[$m])) {
			$message=$keyedmessages[$m];
			$body.='<li>
				<div class="message_name"><a href="'.$message['link'].'">'.$message['subject'].'</a></div>
				<div class="message_info">'.format_date($message['date']).' · By <a href="'.$message['sender_link'].'">'.$message['sender_name'].'</a> in <a href="'.$message['category_link'].'">'.$message['category_name'].'</a></div>
				<div class="message_excerpt">'.$message['excerpt'].'</div>
			</li>';
		}
	}
	$body.='</ul><div class="email_footer"><a href="'.href('settings').'">Manage email settings</a></div></div></div></body></html>';
	send_email(array(
		'to'=>$user['user']['email'],
		'to_name'=>$user['user']['first_name'].' '.$user['user']['last_name'],
		'body'=>$body,
		'subject'=>"Your Email Summary"
	));
}
?>