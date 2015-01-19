<?php
require_once('db.php');
if(isset($_GET['key']) and strlen($_GET['key'])==32) {
	$key=$_GET['key'];
	$result=mysql_query("SELECT user_id FROM user_keys WHERE key_id='".mysql_real_escape_string($key)."' AND expires>=NOW()");
	if(mysql_num_rows($result)==1) {
		list($user_id)=mysql_fetch_array($result, MYSQL_NUM);
		$_SESSION['user_id']=$user_id;
		mysql_query("DELETE FROM user_keys WHERE key_id='".mysql_real_escape_string($key)."'");
	}
}
class view {
	public static $title='';
	private static $product_name='Later';
	
	public static function error($message='There appears to be a problem with <em>Later</em> at the moment. Please try again "later".') {
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><? echo 'Error | '.self::$product_name;?></title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<link href="<? echo href("css/bootstrap.min.css");?>" rel="stylesheet" media="screen">
			<script src="<? echo href("js/jquery.min.js");?>"></script>
		    <script src="<? echo href("js/bootstrap.min.js");?>"></script>
			<script src="<? echo href("js/respond.min.js");?>"></script>
		</head>
		<body>
			<div class="container">
				<div class="jumbotron">
					<div class="container">
						<h1>Oh no!</h1>
						<p><? echo $message;?></p>
					</div>
				</div>
			</div>
		</body>
	</html>
		<?
		exit();
	}
	
	public static function header() {
		$file=end(explode('/',$_SERVER['SCRIPT_NAME']));
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><? if(self::$title) {
				echo self::$title.' | '.self::$product_name;
			}else{
				echo self::$product_name;
			}?></title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<link href="<? echo href("css/bootstrap.min.css");?>" rel="stylesheet" media="screen" />
			<link href="<? echo href("css/chosen.min.css");?>" rel="stylesheet" media="screen" />
			<style>
			.message_info {
			    font-size: 12px;
			    padding-bottom: 3px;
			}
			.later_main {
				padding-top:10px;
				padding-bottom:10px;
			}
			</style>
			<script src="<? echo href("js/jquery.min.js");?>"></script>
		    <script src="<? echo href("js/bootstrap.min.js");?>"></script>
			<script src="<? echo href("js/respond.min.js");?>"></script>
			<script src="<? echo href("js/chosen.min.js");?>"></script>
			<script>
			$(function() {
				$('a').click(function(e) {
					var t=$(this);
					if(t.attr('data-userid')!==undefined) {
						e.preventDefault();
						var user=t.attr('data-userid');
						$.ajax({
							url: "<? echo href('ajax');?>user_card.php?id="+user,
							dataType:'json'
						}).done(function(data) {
							if(data.name && data.bio && data.department) {
								$('#modalProfile').find('.modal-title').html(data.name);
								$('#modalProfile').find('.profile_bio').html(data.bio);
								$('#modalProfile').find('.profile_department').html(data.department);
								$('#modalProfile').modal('show');
							}
						});
					}
				});
			});
			</script>
		</head>
		<body>
			<div class="container later_main">
				<div class="navbar navbar-default">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				        	<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="<? echo href('');?>"><? echo self::$product_name;?></a>
					</div>
					<div class="navbar-collapse collapse">
					<? if(is_logged_in()) {
						$users=get_users(array('user'=>get_user_id()));
						if(isset($users[0])) {
							$user=$users[0];?>
						<ul class="nav navbar-nav">
							<li<? if($file=='index.php' or $file=='cat.php'){echo ' class="active"';}?>><a href="<? echo href('');?>">Home</a></li>
							<li<? if($file=='post_message.php'){echo ' class="active"';}?>><a href="<? echo href('post');?>">Send Message</a></li>
							<li<? if($file=='my_groups.php'){echo ' class="active"';}?>><a href="<? echo href('groups');?>">My Groups</a></li>
							<li<? if($file=='subscriptions.php'){echo ' class="active"';}?>><a href="<? echo href('settings');?>">Settings</a></li>
							<? if(is_admin()) {?>
								<li><a href="<? echo href('admin');?>">Admin</a></li>
							<? }?>
						</ul>
						<p class="navbar-text navbar-right">Signed in as <a href="<? echo href('user/'.get_user_id());?>" data-userid="<? echo get_user_id();?>" class="navbar-link"><? echo $user['name'];?></a></p>
					<? 	}}else{?>
						<ul class="nav navbar-nav navbar-right">
							<li><a href="<? echo LOGIN_URL;?>">Login</a></li>
						</ul>
					<? }
					?>
					</div>
				</div>
				<div id="content_container">
		<?
	}
	public static function footer() {
		?>
				</div>
			</div>
			<div class="modal fade" id="modalProfile" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">...</h4></div><div class="modal-body"><div class="profile_bio"></div><div><strong>Department:</strong> <span class="profile_department"></div></div></div></div>
		</body>
	</html>
		<? 
	}
}
?>