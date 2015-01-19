<?php
require_once('../includes/view.php');
if(is_admin()) {
	view::$title='Admin Panel';
	view::header();?>
	<div class="page-header">
		<h1>Admin Panel</h1>
	</div>
	<div class="row">
	  <div class="col-sm-3">
	  	<div class="panel panel-default">
		  <div class="panel-heading">
		    <h3 class="panel-title">Actions</h3>
		  </div>
		  <div class="list-group">
			<a href="<? echo href('admin/categories');?>" class="list-group-item">Manage categories</a>
			<a href="<? echo href('admin/messages');?>" class="list-group-item">Manage messages</a>
			<a href="<? echo href('groups');?>" class="list-group-item">Manage groups</a>
		  </div>
	    </div>
	  </div>
	  <div class="col-sm-9">
	  	<div class="panel panel-default">
		  <div class="panel-heading">
		    <h3 class="panel-title">Statistics</h3>
		  </div>
		  <div class="panel-body">
		    <div class="row">
			<?php
			$result=mysql_query("SELECT (SELECT COUNT(user_id) FROM users) AS users, (SELECT COUNT(message_id) FROM messages) AS messages, (SELECT COUNT(group_id) FROM groups) AS groups, (SELECT COUNT(category_id) FROM categories) AS categories");
			list($users, $messages, $groups, $categories)=mysql_fetch_array($result, MYSQL_NUM);
			?>
			  <div class="col-sm-3" align="center"><h2><? echo number_format($users);?></h2> <strong>Users</strong></div>
			  <div class="col-sm-3" align="center"><h2><? echo number_format($messages);?></h2> <strong>Messages</strong></div>
			  <div class="col-sm-3" align="center"><h2><? echo number_format($groups);?></h2> <strong>Groups</strong></div>
			  <div class="col-sm-3" align="center"><h2><? echo number_format($categories);?></h2> <strong>Categories</strong></div>
		  </div>
		</div>
	  </div>
	</div>
	<?
	view::footer();
}else{
	login();
}
?>