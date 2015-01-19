<?php
require_once('../includes/view.php');
if(is_admin()) {
	if(isset($_POST['name'])) {
		$params=array(
			'name'=>trim(stripslashes($_POST['name']))
		);
		$response=insert_category($params);
		header("Location: ".href('admin/categories'));
		exit();
	}
	view::$title='Add a category';
	view::header();
	?>
	<div class="page-header">
	  <h1>Add a category</h1>
	</div>
	<form class="form-horizontal" method="post" role="form">
		<div class="form-group">
			<label for="form_name" class="col-lg-2 control-label">Name</label>
			<div class="col-lg-10">
				<input type="text" class="form-control" name="name" id="form_name" value="<? if(isset($_POST['name'])) echo $_POST['name'];?>" placeholder="Name" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-lg-offset-2 col-lg-10">
				<button type="submit" class="btn btn-primary">Add Category</button>
			</div>
		</div>
	</form>
<?	view::footer();
}?>