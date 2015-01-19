<?php
require_once('includes/view.php');
if(isset($_GET['slug'])) {
	$slug=$_GET['slug'];
	$messages=get_messages(array('slug'=>$slug));
	if(isset($messages[0])) {
		$message=$messages[0];
		if(is_admin() or get_user_id()==$message['sender_id']) {
			if(isset($_POST['subject']) and isset($_POST['body']) and isset($_POST['category'])) {
				$params=array(
					'message_id'=>$message['message_id'],
					'subject'=>trim(stripslashes($_POST['subject'])),
					'body'=>trim(stripslashes($_POST['body'])),
					'category_id'=>trim(stripslashes($_POST['category']))
				);
				$response=update_message($params);
				if($response) {
					redirect($message['link']);
				}
			}
			view::$title='Edit Message';
			view::header();
			?>
			<script src="<? echo href("js/tinymce.min.js");?>"></script>
			<script>
			$(function() {
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
			<div class="page-header">
			  <h1>Edit message</h1>
			</div>
			<form class="form-horizontal" method="post" role="form">
				<div class="form-group">
					<label for="form_subject" class="col-lg-2 control-label">Subject</label>
					<div class="col-lg-10">
						<input type="text" class="form-control" name="subject" id="form_subject" value="<? echo $message['subject'];?>" placeholder="Subject" />
					</div>
				</div>
				<div class="form-group">
					<label for="form_body" class="col-lg-2 control-label">Message</label>
					<div class="col-lg-10">
						<textarea name="body" class="form-control" id="form_body" placeholder="Type your message here..." rows="8"><? echo $message['body'];?></textarea>
					</div>
				</div>
				<div class="form-group">
					<label for="form_category" class="col-lg-2 control-label">Category</label>
					<div class="col-lg-10">
						<select name="category" id="form_category"  class="form-control"><? $categories=get_categories();foreach($categories as $category) {?><option value="<? echo $category['category_id'];?>"<? if($message['category_id']==$category['category_id']) echo' selected="selected"';?>><? echo $category['name'];?></option><? }?></select>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-offset-2 col-lg-10">
						<button type="submit" class="btn btn-primary">Update</button>
					</div>
				</div>
			</form>
			<?
		 	view::footer();
		}else{
			login(href($_GET['category'].'/'.$_GET['slug']));
		}
	}else{
		login(href($_GET['category'].'/'.$_GET['slug']));
	}
}
?>