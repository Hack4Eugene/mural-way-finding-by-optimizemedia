<?php w2dc_renderTemplate('admin_header.tpl.php'); ?>

<?php screen_icon('edit-pages'); ?>
<h2><?php _e('CSV Import'); ?></h2>

<p class="description"><?php _e('On this second step collate CSV headers of columns with existing listings fields', 'W2DC'); ?></p>

<form method="POST" action="">
	<input type="hidden" name="action" value="import_collate">
	<input type="hidden" name="import_type" value="<?php echo esc_attr($import_type); ?>">
	<input type="hidden" name="csv_file_name" value="<?php echo esc_attr($csv_file_name); ?>">
	<input type="hidden" name="images_dir" value="<?php echo esc_attr($images_dir); ?>">
	<input type="hidden" name="columns_separator" value="<?php echo esc_attr($columns_separator); ?>">
	<input type="hidden" name="values_separator" value="<?php echo esc_attr($values_separator); ?>">
	<?php wp_nonce_field(W2DC_PATH, 'w2dc_csv_import_nonce');?>
	
	<h3><?php _e('Collate CSV columns', 'W2DC'); ?></h3>
	<table class="form-table">
		<tbody>
			<?php foreach ($headers AS $i=>$column): ?>
			<tr>
				<th scope="row">
					<label><?php echo $column; ?></label>
				</th>
				<td>
					<select name="fields[]">
						<option value=""><?php _e('- Select listings field -', 'W2DC'); ?></option>
						<?php foreach ($collation_fields AS $key=>$field): ?>
						<option value="<?php echo $key; ?>" <?php if ($collated_fields) selected($collated_fields[$i], $key, true); ?>><?php echo $field; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<h3><?php _e('Import settings', 'W2DC'); ?></h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label><?php _e('What to do when category/location/tag was not found', 'W2DC'); ?></label>
				</th>
				<td>
					<input
						name="if_term_not_found"
						type="radio"
						value="create"
						<?php isset($if_term_not_found) ? checked($if_term_not_found, 'create') : checked(true); ?> />
					<?php _e('Create new category/location/tag', 'W2DC'); ?>

					<br />

					<input
						name="if_term_not_found"
						type="radio"
						value="skip"
						<?php isset($if_term_not_found) ? checked($if_term_not_found, 'skip') : ''; ?> />
					<?php _e('Do not do anything', 'W2DC'); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Listings author', 'W2DC'); ?></label>
				</th>
				<td>
					<select name="listings_author">
						<option value="0" <?php isset($listing_author) ? selected($listings_author, 0) : selected(true); ?>><?php _e('As defined in CSV column'); ?></option>
						<?php foreach ($users AS $user): ?>
						<option value="<?php echo $user->ID; ?>" <?php isset($listings_author) ? selected($listings_author, $user->ID) : ''; ?>><?php echo $user->user_login; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Geocode imported listings by address parts', 'W2DC'); ?></label>
				</th>
				<td>
					<input
						name="do_geocode"
						type="checkbox"
						value="1"
						<?php checked($do_geocode, 1, true); ?> />
				</td>
			</tr>
			<?php if (get_option('w2dc_fsubmit_addon') && get_option('w2dc_claim_functionality')): ?>
			<tr>
				<th scope="row">
					<label><?php _e('Configure imported listings as claimable', 'W2DC'); ?></label>
				</th>
				<td>
					<input
						name="is_claimable"
						type="checkbox"
						value="1"
						<?php checked($is_claimable, 1, true); ?> />
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
	
	<?php w2dc_renderTemplate('csv_manager/import_instructions.tpl.php'); ?>
	
	<?php submit_button(__('Import', 'W2DC'), 'primary', 'submit', false); ?>
	&nbsp;&nbsp;&nbsp;
	<?php submit_button(__('Test import', 'W2DC'), 'secondary', 'tsubmit', false); ?>
</form>

<?php w2dc_renderTemplate('admin_footer.tpl.php'); ?>