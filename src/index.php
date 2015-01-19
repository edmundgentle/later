<?php
require_once('includes/view.php');
view::header();
$page=1;
if(isset($_GET['page'])) {
	$page=(int)$_GET['page'];
}
if(!$page) {
	$page=1;
}
$perpage=RESULTS_PER_PAGE;
$start=($page-1)*$perpage;
$params=array('for_me'=>true,'limit'=>$perpage,'start'=>$start);
$messages=get_messages($params);
$total=count_messages($params);
$total_pages=ceil($total/$perpage);
?>
<div class="row">
	<div class="col-sm-3">
		<ul class="nav nav-pills nav-stacked">
			<li class="active"><a href="<? echo href('');?>">All Messages</a></li>
			<?
			$categories=get_categories();
			foreach($categories as $category) {?>
				<li><a href="<? echo $category['link'];?>"><? echo $category['name'];?></a></li>
			<? }
			?>
		</ul>
	</div>
	<div class="col-sm-9">
		<? if(count($messages)) {?>
		<ul class="list-group">
		<? foreach($messages as $message) {?>
			<li id="message_<? echo $message['message_id'];?>" class="list-group-item">
				<h4 class="list-group-item-heading"><a href="<? echo $message['link'];?>"><? echo $message['subject'];?></a></h4>
				<div class="list-group-item-text">
					<div class="message_info"><? echo format_date($message['date']);?> · By <a href="<? echo $message['sender_link'];?>" data-userid="<? echo $message['sender_id'];?>"><? echo $message['sender_name'];?></a> in <a href="<? echo $message['category_link'];?>"><? echo $message['category_name'];?></a></div>
					<? echo $message['excerpt'];?>
				</div>
			</li>
		<? }
		?>
		</ul>
		<? paginate($total_pages,$page);?>
		<? }else{?>
			<div class="jumbotron">
			  <div class="container">
			    <p>There aren't any messages here yet. Once people start posting messages, they will appear here.</p>
			  </div>
			</div>
		<? }?>
	</div>
</div>
<? view::footer();?>