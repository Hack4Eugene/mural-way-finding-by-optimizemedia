<?php echo $args['before_widget']; ?>
<?php if (!empty($title))
echo $args['before_title'] . $title . $args['after_title'];
?>
<div class="w2dc-content w2dc-widget w2dc-categories-widget">
	<?php w2dc_renderAllCategories($parent, $depth, 1, $counter, $subcats, array(), $exact_categories); ?>
</div>
<?php echo $args['after_widget']; ?>