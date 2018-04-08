<?php if (has_excerpt() || (get_option('w2dc_cropped_content_as_excerpt') && get_post()->post_content !== '')): ?>
<div class="w2dc-field w2dc-field-output-block w2dc-field-output-block-<?php echo $content_field->type; ?> w2dc-field-output-block-<?php echo $content_field->id; ?>">
	<?php if ($content_field->icon_image || !$content_field->is_hide_name): ?>
	<span class="w2dc-field-caption">
		<?php if ($content_field->icon_image): ?>
		<span class="w2dc-field-icon w2dc-fa w2dc-fa-lg <?php echo $content_field->icon_image; ?>"></span>
		<?php endif; ?>
		<?php if (!$content_field->is_hide_name): ?>
		<span class="w2dc-field-name"><?php echo $content_field->name?>:</span>
		<?php endif; ?>
	</span>
	<?php endif; ?>
	<span class="w2dc-field-content">
		<?php echo w2dc_crop_content(get_option('w2dc_excerpt_length'), get_option('w2dc_strip_excerpt'), $listing->level->listings_own_page, $listing->level->nofollow); ?>
	</span>
</div>
<?php endif; ?>