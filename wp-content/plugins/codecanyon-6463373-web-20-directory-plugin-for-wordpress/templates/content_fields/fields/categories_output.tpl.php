<?php if (has_term('', W2DC_CATEGORIES_TAX, $listing->post->ID)): ?>
<div class="w2dc-field-output-block w2dc-field-output-block-<?php echo $content_field->type; ?> w2dc-field-output-block-<?php echo $content_field->id; ?>">
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
	<?php //echo get_the_term_list($listing->post->ID, W2DC_CATEGORIES_TAX, '', ', ', ''); ?>
		<?php
		$terms = get_the_terms($listing->post->ID, W2DC_CATEGORIES_TAX);
		foreach ($terms as $term):?>
			<span class="w2dc-label w2dc-label-primary"><a href="<?php echo get_term_link($term, W2DC_CATEGORIES_TAX); ?>" rel="tag"><?php echo $term->name; ?></a>&nbsp;&nbsp;<span class="w2dc-glyphicon w2dc-glyphicon-tag"></span></span>
		<?php endforeach; ?>
	</span>
</div>
<?php endif; ?>