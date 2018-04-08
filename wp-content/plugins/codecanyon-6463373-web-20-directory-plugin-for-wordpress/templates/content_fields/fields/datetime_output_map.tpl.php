<?php if ($formatted_date): ?>
	<?php echo $formatted_date; ?> <?php if($content_field->is_time) echo $content_field->value['hour'] . ':' . $content_field->value['minute']; ?>
<?php endif; ?>