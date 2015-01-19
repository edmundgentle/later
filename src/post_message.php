<?php
require_once('includes/view.php');
if(is_logged_in()) {
	$user_id=get_user_id();
	if(isset($_POST['subject']) and isset($_POST['body']) and isset($_POST['category']) and isset($_POST['recipients'])) {
		$private=false;
		if(isset($_POST['private']) and $_POST['private']) {
			$private=true;
		}
		if(!is_array($_POST['recipients'])) {
			$_POST['recipients']=array($_POST['recipients']);
		}
		$params=array(
			'sender_id'=>$user_id,
			'subject'=>trim(stripslashes($_POST['subject'])),
			'body'=>trim(stripslashes($_POST['body'])),
			'category_id'=>trim(stripslashes($_POST['category'])),
			'private'=>$private,
			'recipients'=>$_POST['recipients']
		);
		$response=insert_message($params);
		if(isset($response['slug'])) {
			$categories=get_categories(array('category'=>trim(stripslashes($_POST['category']))));
			if(isset($categories[0])) {
				$category=$categories[0];
				$link=href($category['slug'].'/'.$response['slug']);
				redirect($link);
			}
		}
	}
	view::$title="Send a Message";
	view::header();
	$categories=get_categories();
	$groups=get_groups(array('manager'=>get_user_id()));
	?>
	<script src="<? echo href("js/tinymce.min.js");?>"></script>
	<script>
	$(function() {
		$("#form_recipients").chosen({
			disable_search_threshold: 10,
	    	no_results_text: "No results for",
			placeholder_text_multiple:"Select message recipients..."
		});
		tinymce.init({
		    selector: "#form_body",
		    plugins: [
		        "advlist autolink lists link charmap",
		        "table contextmenu textcolor"
		    ],
			menubar : false,
		    toolbar: "bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist | link forecolor",
			content_css : "<? echo href("css/editor.css");?>",
			height:250,
			target_list:!1
		});
	});
	</script>
	<? if(count($groups)) {?>
	<div class="page-header">
	  <h1>Send a message</h1>
	</div>
	<form class="form-horizontal" method="post" role="form">
		<div class="form-group">
			<label for="form_subject" class="col-lg-2 control-label">Subject</label>
			<div class="col-lg-10">
				<input type="text" class="form-control" name="subject" id="form_subject" value="<? if(isset($_POST['subject'])) echo $_POST['subject'];?>" placeholder="Subject" />
			</div>
		</div>
		<div class="form-group">
			<label for="form_body" class="col-lg-2 control-label">Message</label>
			<div class="col-lg-10">
				<textarea name="body" class="form-control" id="form_body" placeholder="Type your message here..." rows="8"><? if(isset($_POST['body'])) echo $_POST['body'];?></textarea>
			</div>
		</div>
		<div class="form-group">
			<label for="form_category" class="col-lg-2 control-label">Category</label>
			<div class="col-lg-10">
				<select name="category" id="form_category"  class="form-control"><? foreach($categories as $category) {?><option value="<? echo $category['category_id'];?>"><? echo $category['name'];?></option><? }?></select>
			</div>
		</div>
		<div class="form-group">
			<label for="form_recipients" class="col-lg-2 control-label">Recipients</label>
			<div class="col-lg-10">
				<select name="recipients[]" multiple="true" class="form-control" id="form_recipients"><? foreach($groups as $group) {?><option value="<? echo $group['group_id'];?>"><? echo $group['name'];?></option><? }?></select>
			</div>
		</div>
		<div class="form-group">
			<div class="col-lg-offset-2 col-lg-10">
				<div class="checkbox">
					<label>
						<input type="checkbox" name="private" /> Only show to selected recipients
					</label>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-lg-offset-2 col-lg-10">
				<button type="submit" class="btn btn-primary">Send</button>
			</div>
		</div>
	</form>
	<? }else{?>
		<div class="jumbotron">
		  <div class="container">
		    <h1>Send a message</h1>
		    <p>Oh no! You need to manage a group in order to send out messages.</p>
		    <p>
				<a class="btn btn-primary btn-lg" href="<? echo href('groups/create');?>">Create a Group</a>
				<a class="btn btn-default btn-lg" href="<? echo href('groups');?>">My Groups</a>
			</p>
		  </div>
		</div>
	<? }?>
	<?
	view::footer();
}else{
	login(href('post'));
}
?>