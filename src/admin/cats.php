<?php
require_once('../includes/view.php');
if(is_admin()) {
	view::$title="Manage categories";
	view::header();
	$categories=get_categories();?>
	<script>
	var cats=<? echo json_encode($categories);?>;
	var cat_id=null;
	$(function() {
		$('.btn_renamecat').click(function(e) {
			e.preventDefault();
			cat_id=$(this).attr('data-catid');
			var cat=null;
			for(var x=0;x<cats.length;x++) {
				if(cats[x].category_id==cat_id) {
					cat=cats[x];
					break;
				}
			}
			if(cat!==null) {
				$('#modal_form_name').val(cat.name);
				$('#renamecat_modal').modal('show');
			}
		});
		$('#renamecat_modal').find('.btn-primary').click(function(e) {
			var name=$('#modal_form_name').val();
			if(name!=null) {
				$.ajax({
					url: "<? echo href('ajax/admin.php');?>",
					dataType:'json',
					type:'POST',
					data: {method:'renamecat', cat_id: cat_id, name:name}
				}).done(function(data) {
					if(data.success!==undefined && data.success) {
						$('#cat_'+cat_id+'_name').html(name);
					}
				});
				$('#renamecat_modal').modal('hide');
			}
		});
		$('.btn_removecat').click(function(e) {
			e.preventDefault();
			cat_id=$(this).attr('data-catid');
			$('#removecat_modal').modal('show');
		});
		$('#removecat_modal').find('.btn-danger').click(function(e) {
			$.ajax({
				url: "<? echo href('ajax/admin.php');?>",
				dataType:'json',
				type:'POST',
				data: {method:'removecat', cat_id: cat_id}
			}).done(function(data) {
				if(data.success!==undefined && data.success) {
					$('#cat_'+cat_id).remove();
				}
			});
			$('#removecat_modal').modal('hide');
		});
	});
	</script>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Categories</h3>
		</div>
		<ul class="list-group">
		<? foreach($categories as $category) {?>
			<li class="list-group-item" id="cat_<? echo $category['category_id'];?>">
				<div class="btn-group btn-group-xs pull-right">
				  <a href="" class="btn btn-primary btn_renamecat" data-catid="<? echo $category['category_id'];?>">Rename</a>
				  <a href="" class="btn btn-danger btn_removecat" data-catid="<? echo $category['category_id'];?>">Delete</a>
				</div>
				<span id="cat_<? echo $category['category_id'];?>_name"><? echo $category['name'];?></span>
			</li>
		<? 	}?>
		</ul>
		<div class="panel-footer">
			<a href="<? echo href('admin/categories/add');?>" class="btn btn-success">Add a Category</a>
		</div>
	</div>
	<div id="renamecat_modal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">Rename Category</h4>
				</div>
				<div class="modal-body">
					<div class="form-horizontal">
						<div class="form-group">
							<label for="form_name" class="col-lg-2 control-label">Name</label>
							<div class="col-lg-10">
								<input type="text" class="form-control" name="name" id="modal_form_name" placeholder="name" />
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary">Save</button>
				</div>
			</div>
		</div>
	</div>
	<div id="removecat_modal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">Remove Category</h4>
				</div>
				<div class="modal-body">
					<div class="form-horizontal">
						<div align="center">Are you sure you want to remove this category? This will also remove all the messages within this category.</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
					<button type="button" class="btn btn-danger">Yes</button>
				</div>
			</div>
		</div>
	</div>
	<? 
	view::footer();
}?>