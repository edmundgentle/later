<?php
require_once('includes/view.php');
if(is_logged_in()) {
	$saved=false;
	$user_id=get_user_id();
	$categories=get_categories();
	if(count($categories) and isset($_POST['cat_'.$categories[0]['category_id']])) {
		$new_prefs=array();
		$possible_prefs=array('no_emails', 'summary', 'all_emails');
		foreach($categories as $category) {
			if(isset($_POST['cat_'.$category['category_id']]) and in_array($_POST['cat_'.$category['category_id']],$possible_prefs)) {
				$new_prefs[$category['category_id']]=$_POST['cat_'.$category['category_id']];
			}
		}
		if(set_preferences($user_id, $new_prefs)) {
			$saved=true;
		}
	}
	view::$title="Settings";
	view::header();
	$prefs=get_preferences(array('user'=>$user_id));
	$preferences=array();
	foreach($prefs as $p) {
		$preferences[$p['category_id']]=$p['preference'];
	}?>
	<div class="page-header">
	  <h1>Settings <small>Manage your email notifications</small></h1>
	</div>
	<? 
	if($saved) {?>
		<div class="message_success">Your settings have been saved successfully.</div>
	<? }?>
	<form method="post">
		<div class="form-group">
			<div class="row hidden-xs">
				<div class="col-sm-offset-3 col-sm-2" align="center"><strong>All Emails</strong></div>
				<div class="col-sm-2" align="center"><strong>Weekly Summary</strong></div>
				<div class="col-sm-2" align="center"><strong>No Emails</strong></div>
			</div>
			<?
			foreach($categories as $category) {
				$pref='all_emails';//no_emails, summary, all_emails
				if(isset($preferences[$category['category_id']])) {
					$pref=$preferences[$category['category_id']];
				}?>
				<div class="row">
					<div class="col-sm-3"><strong><? echo $category['name'];?></strong></div>
					<div class="col-sm-2" align="center"><input type="radio" name="cat_<? echo $category['category_id'];?>" value="all_emails"<? if($pref=='all_emails') {echo "checked=\"checked\"";}?>> <span class="visible-xs">All Emails</span></div>
					<div class="col-sm-2" align="center"><input type="radio" name="cat_<? echo $category['category_id'];?>" value="summary"<? if($pref=='summary') {echo "checked=\"checked\"";}?>> <span class="visible-xs">Weekly Summary</span></div>
					<div class="col-sm-2" align="center"><input type="radio" name="cat_<? echo $category['category_id'];?>" value="no_emails"<? if($pref=='no_emails') {echo "checked=\"checked\"";}?>> <span class="visible-xs">No Emails</span></div>
				</div>
			<? }?>
		</div>
		<div class="form-group">
			<input class="btn btn-primary" type="submit" value="Save" />
		</div>
	</form>
	<?
	view::footer();
}else{
	login(href('settings'));
}
?>