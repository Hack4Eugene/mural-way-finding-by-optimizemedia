<div class="w2dc-row w2dc-field-search-block-<?php echo $search_field->content_field->id; ?> w2dc-field-search-block-<?php echo $random_id; ?> w2dc-field-search-block-<?php echo $search_field->content_field->id; ?>_<?php echo $random_id; ?>">
	<div class="w2dc-col-md-12">
		<label><?php echo $search_field->content_field->name; ?></label>
	</div>
	<div class="w2dc-col-md-12 w2dc-form-group">
		<input type="text" class="w2dc-form-control" name="field_<?php echo $search_field->content_field->slug; ?>" value="<?php echo esc_attr($search_field->value); ?>" />
	</div>
</div>