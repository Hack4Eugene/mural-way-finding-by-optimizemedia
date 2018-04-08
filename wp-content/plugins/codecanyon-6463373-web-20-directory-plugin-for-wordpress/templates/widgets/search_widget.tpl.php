<?php echo $args['before_widget']; ?>
<?php if (!empty($title))
echo $args['before_title'] . $title . $args['after_title'];
?>
<div class="w2dc-content w2dc-widget w2dc_search_widget">
	<?php
	$search_form = new w2dc_search_form($uid, '', array('directory' => $directory));
	$search_form->display(1);
	?>
</div>
<?php echo $args['after_widget']; ?>