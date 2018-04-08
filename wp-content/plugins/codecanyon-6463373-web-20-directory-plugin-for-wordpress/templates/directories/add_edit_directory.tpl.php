<?php w2dc_renderTemplate('admin_header.tpl.php'); ?>

<?php screen_icon('edit-pages'); ?>
<h2>
	<?php
	if ($directory_id)
		_e('Edit directory', 'W2DC');
	else
		_e('Create new directory', 'W2DC');
	?>
</h2>

<form method="POST" action="">
	<?php wp_nonce_field(W2DC_PATH, 'w2dc_directories_nonce');?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label><?php _e('Directory name', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></label>
				</th>
				<td>
					<input
						name="name"
						type="text"
						class="regular-text"
						value="<?php echo esc_attr($directory->name); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Single form', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></label>
				</th>
				<td>
					<input
						name="single"
						type="text"
						class="regular-text"
						value="<?php echo esc_attr($directory->single); ?>" />
					<?php w2dc_wpml_translation_complete_notice(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Plural form', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></label>
				</th>
				<td>
					<input
						name="plural"
						type="text"
						class="regular-text"
						value="<?php echo esc_attr($directory->plural); ?>" />
					<?php w2dc_wpml_translation_complete_notice(); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<?php _e('Notice about slugs:', 'W2DC'); ?>
					<br />
					<?php _e('Slugs must contain only alpha-numeric characters, underscores or dashes. All slugs must be unique and different.', 'W2DC'); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Listing slug', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></label>
				</th>
				<td>
					<input
						name="listing_slug"
						type="text"
						class="regular-text"
						value="<?php echo esc_attr($directory->listing_slug); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Category slug', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></label>
				</th>
				<td>
					<input
						name="category_slug"
						type="text"
						class="regular-text"
						value="<?php echo esc_attr($directory->category_slug); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Location slug', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></label>
				</th>
				<td>
					<input
						name="location_slug"
						type="text"
						class="regular-text"
						value="<?php echo esc_attr($directory->location_slug); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Tag slug', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></label>
				</th>
				<td>
					<input
						name="tag_slug"
						type="text"
						class="regular-text"
						value="<?php echo esc_attr($directory->tag_slug); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Assigned categories', 'W2DC'); ?></label>
					<?php echo w2dc_get_wpml_dependent_option_description(); ?>
				</th>
				<td>
					<p class="description"><?php _e('You may define some special categories, those would be available for this directory', 'W2DC'); ?></p>
					<?php w2dc_termsSelectList('categories', W2DC_CATEGORIES_TAX, $directory->categories); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Assigned locations', 'W2DC'); ?></label>
					<?php echo w2dc_get_wpml_dependent_option_description(); ?>
				</th>
				<td>
					<p class="description"><?php _e('You may define some special locations, those would be available for this directory', 'W2DC'); ?></p>
					<?php w2dc_termsSelectList('locations', W2DC_LOCATIONS_TAX, $directory->locations); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Listings levels', 'W2DC'); ?></label>
				</th>
				<td>
					<p class="description"><?php _e('You may define some special levels, those would be available for this directory', 'W2DC'); ?></p>
					<select multiple="multiple" name="levels[]" class="w2dc-form-control w2dc-form-group" style="height: 300px">
						<option value="" <?php if (!$directory->levels) echo 'selected'; ?>><?php _e('- Select All -', 'W2DC'); ?></option>
						<?php
						foreach ($w2dc_instance->levels->levels_array AS $level):
						?>
						<option value="<?php echo $level->id; ?>" <?php if (in_array($level->id, $directory->levels)) echo 'selected'; ?>><?php echo $level->name; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	
	<?php
	if ($directory_id)
		submit_button(__('Save changes', 'W2DC'));
	else
		submit_button(__('Create directory', 'W2DC'));
	?>
</form>

<?php w2dc_renderTemplate('admin_footer.tpl.php'); ?>