<?php if (count($search_field->content_field->selection_items)): ?>
<?php if ($columns == 1) $col_md = 6; else $col_md = 4; ?>
<div class="w2dc-row w2dc-field-search-block-<?php echo $search_field->content_field->id; ?> w2dc-field-search-block-<?php echo $random_id; ?> w2dc-field-search-block-<?php echo $search_field->content_field->id; ?>_<?php echo $random_id; ?>">
	<div class="w2dc-col-md-12">
		<label><?php echo $search_field->content_field->name; ?></label>
	</div>
	<?php
	if ($search_field->search_input_mode == 'checkboxes' || $search_field->search_input_mode =='radiobutton'):
		$i = 1;
		while ($i <= ($columns+1)): ?>
		<div class="w2dc-col-md-<?php echo $col_md; ?> w2dc-form-group">
			<?php $j = 1; ?>
			<?php foreach ($search_field->content_field->selection_items AS $key=>$item): ?>
			<?php if ($i == $j): ?>
			<div class="<?php if ($search_field->search_input_mode =='checkboxes'): ?>w2dc-checkbox<?php elseif ($search_field->search_input_mode =='radiobutton'): ?>w2dc-radio<?php endif; ?>">
				<label>
					<?php if ($search_field->search_input_mode =='checkboxes'): ?>
					<input type="checkbox" name="field_<?php echo $search_field->content_field->slug; ?>[]" value="<?php echo esc_attr($key); ?>" <?php if (in_array($key, $search_field->value)) echo 'checked'; ?> />
					<?php elseif ($search_field->search_input_mode =='radiobutton'): ?>
					<input type="radio" name="field_<?php echo $search_field->content_field->slug; ?>" value="<?php echo esc_attr($key); ?>" <?php if (in_array($key, $search_field->value)) echo 'checked'; ?> />
					<?php endif; ?>
					<?php echo $item; ?>
				</label>
			</div>
			<?php endif; ?>
			<?php $j++; ?>
			<?php if ($j > ($columns+1)) $j = 1; ?>
			<?php endforeach; ?>
		</div>
		<?php $i++; ?>
		<?php endwhile; ?>
	<?php elseif ($search_field->search_input_mode == 'selectbox'): ?>
	<div class="w2dc-col-md-12 w2dc-form-group">
		<select name="field_<?php echo $search_field->content_field->slug; ?>" class="w2dc-form-control" style="width: 100%;">
			<option value="" <?php if (!$search_field->value) echo 'selected'; ?>><?php printf(__('- Select %s -', 'W2DC'), $search_field->content_field->name); ?></option>
			<?php foreach ($search_field->content_field->selection_items AS $key=>$item): ?>
			<option value="<?php echo esc_attr($key); ?>" <?php if (in_array($key, $search_field->value)) echo 'selected'; ?>><?php echo $item; ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php endif; ?>
</div>
<?php endif; ?>