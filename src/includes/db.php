<?php
include('config.php');
require_once('functions.php');
$dbc = @mysql_connect(MYSQL_SERVER, MYSQL_USERNAME, MYSQL_PASSWORD);
@mysql_select_db(MYSQL_DB);
if(!$dbc) {
	view::error('We\'ve got a problem trying to connect to our database. This should be sorted very soon! Please check back "later".');
}
date_default_timezone_set(TIMEZONE);
function insert_message($params=array()) {
	if(isset($params['sender_id']) and isset($params['subject']) and isset($params['body']) and isset($params['category_id']) and isset($params['private'])) {
		if(!isset($params['slug'])) {
			$params['slug']=trim(preg_replace('/\W/','_',strtolower($params['subject'])),'_');
			do {
				$params['slug']=str_replace('__','_',$params['slug'],$c);
			}
			while($c);
			$params['slug']=substr($params['slug'],0,50);
			$c=0;
			$slug=$params['slug'];
			do {
				if($c>0) {
					$params['slug']=substr($slug,0,49-strlen($c)).'_'.$c;
				}
				$c++;
			}
			while(mysql_num_rows(mysql_query("SELECT message_id FROM messages WHERE slug='".mysql_real_escape_string($params['slug'])."'")));
		}
		if(!isset($params['date'])) {
			$params['date']=time();
		}
		$result=mysql_query("INSERT INTO messages (slug, sender_id, subject, body, category_id, private, date) VALUES ('".mysql_real_escape_string($params['slug'])."', '".mysql_real_escape_string($params['sender_id'])."', '".mysql_real_escape_string($params['subject'])."', '".mysql_real_escape_string($params['body'])."', '".mysql_real_escape_string($params['category_id'])."', '".mysql_real_escape_string($params['private'])."', '".mysql_real_escape_string(date("Y-m-d H:i:s", $params['date']))."')");
		if($params['message_id']=mysql_insert_id()) {
			if(isset($params['recipients']) and is_array($params['recipients'])) {
				$params['recipients']=array_unique($params['recipients']);
				$inserts=array();
				foreach($params['recipients'] as $recipient) {
					$inserts[]=" ('".mysql_real_escape_string($params['message_id'])."', '".mysql_real_escape_string($recipient)."') ";
				}
				if(count($inserts)) {
					mysql_query("INSERT INTO message_recipients (message_id, group_id) VALUES ".implode(',',$inserts));
				}
			}
			$senders=get_users(array('user'=>$params['sender_id']));
			if(isset($senders[0])) {
				$sender=$senders[0];
				$result=mysql_query("SELECT users.user_id, first_name, last_name, email FROM group_users, users WHERE group_users.user_id=users.user_id AND group_users.group_id IN ('".implode("','",$params['recipients'])."') AND (SELECT COUNT(preference) FROM user_preferences WHERE user_preferences.user_id=users.user_id AND user_preferences.category_id='".mysql_real_escape_string($params['category_id'])."')=0");
				$body = '<style>body, .body {font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;font-size: 14px;line-height: 1.428571429;color: #333;}a {color: #428bca;text-decoration: none;}.container {background-color:#fff;margin-left:auto;margin-right:auto;max-width:500px;border-radius:3px;overflow:hidden;box-shadow:0 0 4px rgba(0,0,0,0.2);}.container .header {background-color:#428bca;color:#FFF;padding:3px;text-align:center;}.container h1 {margin:0;padding:0;font-size:20px;font-weight:400;}.message_list {list-style:none;margin:0;padding:1px;}.message_list li {padding:4px;border-bottom:1px solid #ddd;}.message_list li:last-child {border:0;}.message_list li .message_name {font-size:18px;}.message_list li .message_info {font-size:12px;}.message_list li .message_excerpt {padding:2px;padding-left:3px;border-left:3px solid #eee;}.container .email_footer {border-top:3px solid #eee;margin:4px;padding:4px;padding-top:7px;text-align:center;font-size:12px;}</style><div class="body">'.$params['body'].'</div><div class="email_footer"><a href="'.href('settings').'">Manage email settings</a></div>';
				while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					send_email(array(
						'to'=>$row['email'],
						'to_name'=>$row['first_name'].' '.$row['last_name'],
						'body'=>$body,
						'subject'=>$params['subject'],
						'from_name'=>$sender['name'],
						'from'=>$sender['email']
					));
				}
			}
			return $params;
		}
	}
	return null;
}
function get_messages($params=array()) {
	$output=array();
	$where=array();
	$start=0;
	$limit=0;
	foreach($params as $k=>$v) {
		if($k=='category' || $k=='category_id') {
			if(is_array($v)) {
				$where[]=" messages.category_id IN ('".implode("','",$v)."')";
			}else{
				$where[]=" messages.category_id='$v'";
			}
		}
		if($k=='message' || $k=='message_id') {
			if(is_array($v)) {
				$where[]=" messages.message_id IN ('".implode("','",$v)."')";
			}else{
				$where[]=" messages.message_id='$v'";
			}
		}
		if($k=='slug') {
			if(is_array($v)) {
				$where[]=" messages.slug IN ('".implode("','",$v)."')";
			}else{
				$where[]=" messages.slug='$v'";
			}
		}
		if($k=='sender' || $k=='sender_id') {
			if(is_array($v)) {
				$where[]=" messages.sender_id IN ('".implode("','",$v)."')";
			}else{
				$where[]=" messages.sender_id='$v'";
			}
		}
		if($k=='private') {
			if($v) {
				$where[]=" messages.private=1";
			}else{
				$where[]=" messages.private=0";
			}
		}
		if($k=='for_me') {
			if($v) {
				$where[]=" (messages.private=0 OR '".get_user_id()."' IN (SELECT group_users.user_id FROM group_users, groups, message_recipients WHERE group_users.group_id=groups.group_id AND groups.group_id=message_recipients.group_id AND message_recipients.message_id=messages.message_id)) ";
			}
		}
		if($k=='since') {
			$where[]=" messages.date>$v ";
		}
		if($k=='start') {
			$start=$v;
		}
		if($k=='limit') {
			$limit=$v;
		}
	}
	$whereq='';
	if(count($where)) {
		$whereq="AND ".implode(" AND ", $where);
	}
	$query="SELECT messages.message_id, messages.slug, subject, body, messages.category_id, messages.sender_id, date, private, categories.slug AS category_slug, categories.name AS category_name, users.first_name AS sender_firstname, users.last_name AS sender_lastname FROM messages, categories, users WHERE messages.category_id=categories.category_id AND messages.sender_id=users.user_id $whereq ORDER BY date DESC";
	if($limit) {
		$query.=" LIMIT $start, $limit";
	}
	$result=mysql_query($query);
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$row['excerpt']=substr_words($row['body'],200);
		$row['link']=href($row['category_slug'].'/'.$row['slug']);
		$row['category_link']=href($row['category_slug']);
		$row['sender_name']=trim($row['sender_firstname'].' '.$row['sender_lastname']);
		$row['sender_link']=href('user/'.$row['sender_id']);
		$output[]=$row;
	}
	return $output;
}
function count_messages($params=array()) {
	$output=array();
	$where=array();
	foreach($params as $k=>$v) {
		if($k=='category' || $k=='category_id') {
			if(is_array($v)) {
				$where[]=" messages.category_id IN ('".implode("','",$v)."')";
			}else{
				$where[]=" messages.category_id='$v'";
			}
		}
		if($k=='message' || $k=='message_id') {
			if(is_array($v)) {
				$where[]=" messages.message_id IN ('".implode("','",$v)."')";
			}else{
				$where[]=" messages.message_id='$v'";
			}
		}
		if($k=='slug') {
			if(is_array($v)) {
				$where[]=" messages.slug IN ('".implode("','",$v)."')";
			}else{
				$where[]=" messages.slug='$v'";
			}
		}
		if($k=='sender' || $k=='sender_id') {
			if(is_array($v)) {
				$where[]=" messages.sender_id IN ('".implode("','",$v)."')";
			}else{
				$where[]=" messages.sender_id='$v'";
			}
		}
		if($k=='private') {
			if($v) {
				$where[]=" messages.private=1";
			}else{
				$where[]=" messages.private=0";
			}
		}
		if($k=='for_me') {
			if($v) {
				$where[]=" (messages.private=0 OR '".get_user_id()."' IN (SELECT group_users.user_id FROM group_users, groups, message_recipients WHERE group_users.group_id=groups.group_id AND groups.group_id=message_recipients.group_id AND message_recipients.message_id=messages.message_id)) ";
			}
		}
		if($k=='since') {
			$where[]=" messages.date>$v ";
		}
	}
	$whereq='';
	if(count($where)) {
		$whereq="WHERE ".implode(" AND ", $where);
	}
	$query="SELECT COUNT(messages.message_id) FROM messages $whereq";
	$result=mysql_query($query);
	list($num)=mysql_fetch_array($result, MYSQL_NUM);
	return $num;
}
function get_categories($params=array()) {
	$output=array();
	$where=array();
	foreach($params as $k=>$v) {
		if($k=='category' || $k=='category_id') {
			if(is_array($v)) {
				$where[]=" category_id IN ('".implode("','",$v)."')";
			}else{
				$where[]=" category_id='$v'";
			}
		}
		if($k=='slug') {
			if(is_array($v)) {
				$where[]=" slug IN ('".implode("','",$v)."')";
			}else{
				$where[]=" slug='$v'";
			}
		}
	}
	$whereq='';
	if(count($where)) {
		$whereq="WHERE ".implode(" AND ", $where);
	}
	$result=mysql_query("SELECT category_id, slug, name FROM categories $whereq ORDER BY name ASC");
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$row['link']=href($row['slug']);
		$output[]=$row;
	}
	return $output;
}
function insert_category($params) {
	if(isset($params['name'])) {
		if(!isset($params['slug'])) {
			$params['slug']=trim(preg_replace('/\W/','_',strtolower($params['name'])),'_');
			do {
				$params['slug']=str_replace('__','_',$params['slug'],$c);
			}
			while($c);
			$params['slug']=substr($params['slug'],0,30);
			$c=0;
			$slug=$params['slug'];
			do {
				if($c>0) {
					$params['slug']=substr($slug,0,29-strlen($c)).'_'.$c;
				}
				$c++;
			}
			while(mysql_num_rows(mysql_query("SELECT category_id FROM categories WHERE slug='".mysql_real_escape_string($params['slug'])."'")));
		}
		$result=mysql_query("INSERT INTO categories (slug, name) VALUES ('".mysql_real_escape_string($params['slug'])."','".mysql_real_escape_string($params['name'])."')");
		if($params['category_id']=mysql_insert_id()) {
			return $params;
		}
	}
	return null;
}
function insert_group($params) {
	if(isset($params['name']) and isset($params['admin_id'])) {
		$params['managers'][]=$params['admin_id'];
		$result=mysql_query("INSERT INTO groups (name, admin_id) VALUES ('".mysql_real_escape_string($params['name'])."','".mysql_real_escape_string($params['admin_id'])."')");
		if($params['group_id']=mysql_insert_id()) {
			if(isset($params['members'])) {
				$params['members']=array_unique($params['members']);
				$inserts=array();
				foreach($params['members'] as $member) {
					$inserts[]=" ('".mysql_real_escape_string($params['group_id'])."', '".mysql_real_escape_string($member)."') ";
				}
				if(count($inserts)) {
					mysql_query("INSERT INTO group_users (group_id, user_id) VALUES ".implode(',',$inserts));
				}
			}
			if(isset($params['managers'])) {
				$params['managers']=array_unique($params['managers']);
				$inserts=array();
				foreach($params['managers'] as $member) {
					$inserts[]=" ('".mysql_real_escape_string($params['group_id'])."', '".mysql_real_escape_string($member)."') ";
				}
				if(count($inserts)) {
					mysql_query("INSERT INTO group_managers (group_id, user_id) VALUES ".implode(',',$inserts));
				}
			}
			return $params;
		}
	}
	return null;
}
function edit_group($params) {
	if(isset($params['group_id']) and isset($params['name'])) {
		$result=mysql_query("UPDATE groups SET name='".mysql_real_escape_string($params['name'])."' WHERE group_id='".mysql_real_escape_string($params['group_id'])."'");
		if(mysql_affected_rows()) {
			return true;
		}
	}
	return false;
}
function update_message($params) {
	if(isset($params['message_id']) and isset($params['subject']) and isset($params['body']) and isset($params['category_id'])) {
		$result=mysql_query("UPDATE messages SET subject='".mysql_real_escape_string($params['subject'])."', body='".mysql_real_escape_string($params['body'])."', category_id='".mysql_real_escape_string($params['category_id'])."' WHERE message_id='".mysql_real_escape_string($params['message_id'])."'");
		if(mysql_affected_rows()) {
			return true;
		}
	}
	return false;
}
function remove_group($group_id) {
	mysql_query("DELETE FROM group_users WHERE group_id='".mysql_real_escape_string($group_id)."'");
	mysql_query("DELETE FROM group_managers WHERE group_id='".mysql_real_escape_string($group_id)."'");
	mysql_query("DELETE FROM groups WHERE group_id='".mysql_real_escape_string($group_id)."'");
	return true;
}
function remove_from_group($params) {
	if(isset($params['member_id']) and isset($params['group_id'])) {
		$result=mysql_query("DELETE FROM group_users WHERE user_id='".mysql_real_escape_string($params['member_id'])."' AND group_id='".mysql_real_escape_string($params['group_id'])."'");
		if(mysql_affected_rows()) {
			return true;
		}
	}
	if(isset($params['manager_id']) and isset($params['group_id'])) {
		$result=mysql_query("DELETE FROM group_managers WHERE user_id='".mysql_real_escape_string($params['manager_id'])."' AND group_id='".mysql_real_escape_string($params['group_id'])."'");
		if(mysql_affected_rows()) {
			return true;
		}
	}
	return false;
}
function get_groups($params=array()) {
	$output=array();
	$where=array();
	foreach($params as $k=>$v) {
		if($k=='group' || $k=='group_id') {
			if(is_array($v)) {
				$where[]=" groups.group_id IN ('".implode("','",$v)."')";
			}else{
				$where[]=" groups.group_id='$v'";
			}
		}
		if($k=='admin' || $k=='admin_id') {
			if(is_array($v)) {
				$where[]=" groups.admin_id IN ('".implode("','",$v)."')";
			}else{
				$where[]=" groups.admin_id='$v'";
			}
		}
		if($k=='member' || $k=='member_id') {
			$where[]=" '$v' IN (SELECT user_id FROM group_users WHERE group_users.group_id=groups.group_id) ";
		}
		if($k=='manager' || $k=='manager_id') {
			if($v==get_user_id() and is_admin()) {
				$where[]=" ('$v' IN (SELECT user_id FROM group_managers WHERE group_managers.group_id=groups.group_id) OR groups.admin_id=0) ";
			}else{
				$where[]=" '$v' IN (SELECT user_id FROM group_managers WHERE group_managers.group_id=groups.group_id) ";
			}
		}
	}
	$whereq='';
	if(count($where)) {
		$whereq="WHERE ".implode(" AND ", $where);
	}
	$result=mysql_query("SELECT group_id, name, admin_id, (SELECT COUNT(id) FROM group_users WHERE group_users.group_id=groups.group_id) AS num_members FROM groups $whereq ORDER BY name ASC");
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$row['link']=href('groups/'.$row['group_id']);
		$output[]=$row;
	}
	return $output;
}
function add_group_users($params=array()) {
	if(isset($params['group_id'])) {
		if(isset($params['members'])) {
			$current=get_group_members(array('group'=>$params['group_id']));
			$existing=array();
			foreach($current as $member) {
				$existing[]=$member['user_id'];
			}
			$params['members']=array_unique($params['members']);
			$inserts=array();
			foreach($params['members'] as $member) {
				if(!in_array($member,$existing)) {
					$inserts[]=" ('".mysql_real_escape_string($params['group_id'])."', '".mysql_real_escape_string($member)."') ";
				}
			}
			if(count($inserts)) {
				mysql_query("INSERT INTO group_users (group_id, user_id) VALUES ".implode(',',$inserts));
				return true;
			}
		}
		if(isset($params['managers'])) {
			$current=get_group_members(array('group'=>$params['group_id'],'managers'=>true));
			$existing=array();
			foreach($current as $member) {
				$existing[]=$member['user_id'];
			}
			$params['managers']=array_unique($params['managers']);
			$inserts=array();
			foreach($params['managers'] as $member) {
				if(!in_array($member,$existing)) {
					$inserts[]=" ('".mysql_real_escape_string($params['group_id'])."', '".mysql_real_escape_string($member)."') ";
				}
			}
			if(count($inserts)) {
				mysql_query("INSERT INTO group_managers (group_id, user_id) VALUES ".implode(',',$inserts));
				return true;
			}
		}
	}
}
function get_group_members($params=array()) {
	$output=array();
	$where=array();
	$what='group_users';
	foreach($params as $k=>$v) {
		if($k=='group' || $k=='group_id') {
			if(is_array($v)) {
				$where[]=" [[]].group_id IN ('".implode("','",$v)."')";
			}else{
				$where[]=" [[]].group_id='$v'";
			}
		}
		if($k=='managers' and $v) {
			$what='group_managers';
		}
	}
	$whereq='';
	if(count($where)) {
		$whereq=str_replace('[[]]',$what," AND ".implode(" AND ", $where));
	}
	$result=mysql_query("SELECT users.user_id, first_name, last_name, email FROM $what, users WHERE $what.user_id=users.user_id $whereq ORDER BY last_name ASC");
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$row['name']=trim($row['first_name'].' '.$row['last_name']);
		$row['link']=href('user/'.$row['user_id']);
		$output[]=$row;
	}
	return $output;
}
function get_users($params=array()) {
	$output=array();
	$where=array();
	foreach($params as $k=>$v) {
		if($k=='user' || $k=='user_id') {
			if(is_array($v)) {
				$where[]=" users.user_id IN ('".implode("','",$v)."')";
			}else{
				$where[]=" users.user_id='$v'";
			}
		}
	}
	$whereq='';
	if(count($where)) {
		$whereq="WHERE ".implode(" AND ", $where);
	}
	$result=mysql_query("SELECT user_id, first_name, last_name, email, stage, course, department FROM users $whereq ORDER BY last_name ASC");
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$row['name']=trim($row['first_name'].' '.$row['last_name']);
		$row['link']=href('user/'.$row['user_id']);
		$output[]=$row;
	}
	return $output;
}
function get_preferences($params=array()) {
	$output=array();
	$where=array();
	foreach($params as $k=>$v) {
		if($k=='user' || $k=='user_id') {
			if(is_array($v)) {
				$where[]=" user_id IN ('".implode("','",$v)."')";
			}else{
				$where[]=" user_id='$v'";
			}
		}
	}
	$whereq='';
	if(count($where)) {
		$whereq="WHERE ".implode(" AND ", $where);
	}
	$result=mysql_query("SELECT user_id, category_id, preference FROM user_preferences $whereq");
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$output[]=$row;
	}
	return $output;
}
function set_preferences($user_id, $preferences=array()) {
	foreach($preferences as $cat=>$val) {
		mysql_query("DELETE FROM user_preferences WHERE user_id='".mysql_real_escape_string($user_id)."' AND category_id='".mysql_real_escape_string($cat)."'");
		if($val!='all_emails') {
			mysql_query("INSERT INTO user_preferences (user_id, category_id, preference) VALUES ('".mysql_real_escape_string($user_id)."', '".mysql_real_escape_string($cat)."', '".mysql_real_escape_string($val)."')");
		}
	}
	return true;
}
?>