<?php echo $args['before_widget']; ?>
<?php if (!empty($title))
echo $args['before_title'] . $title . $args['after_title'];
?>
<div class="w2dc-content w2dc-widget w2dc-locations-widget">
	<?php w2dc_renderAllLocations($parent, $depth, 1, $counter, $sublocations, $exact_locations); ?>
</div>
<?php echo $args['after_widget']; ?>