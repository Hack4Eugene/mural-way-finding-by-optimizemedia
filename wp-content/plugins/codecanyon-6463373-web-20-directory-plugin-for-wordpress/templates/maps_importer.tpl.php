<?php w2gm_renderTemplate('admin_header.tpl.php'); ?>

<h2>
	<?php _e('Import From Web 2.0 Google Maps plugin', 'W2DC'); ?>
</h2>

<?php _e('We have found that Web 2.0 Google Maps plugin was installed on your WordPress site. Do you want to import categories, locations, tags, listings or settings from directory? You will not lose any data, everything will be copied from google maps tables.', 'W2DC'); ?>
<br />
<?php _e('Would be better to import all data at once.', 'W2DC'); ?>
<br />
<strong><?php _e('Recommended to make database backup before import.', 'W2DC'); ?></strong>

<form method="POST" action="">
	<table class="form-table">
		<tbody>
			<tr>
				<td>
					<label>
						<input
							name="import_categories"
							type="checkbox"
							checked
							value="1" />
		
						<?php _e('Import maps categories', 'W2DC'); ?>
					</label>
				</td>
			</tr>
			<tr>
				<td>
					<label>
						<input
							name="import_locations"
							type="checkbox"
							checked
							value="1" />
		
						<?php _e('Import maps locations', 'W2DC'); ?>
					</label>
				</td>
			</tr>
			<tr>
				<td>
					<label>
						<input
							name="import_tags"
							type="checkbox"
							checked
							value="1" />
		
						<?php _e('Import maps tags', 'W2DC'); ?>
					</label>
				</td>
			</tr>
			<tr>
				<td>
					<label>
						<input
							name="import_fields"
							type="checkbox"
							checked
							value="1" />
		
						<?php _e('Import maps content fields with fields groups and listings fields data', 'W2DC'); ?>
					</label>
				</td>
			</tr>
			<tr>
				<td>
					<label>
						<input
							name="import_listings"
							type="checkbox"
							checked
							value="1" />
		
						<?php _e('Import maps listings', 'W2DC'); ?>
					</label>
				</td>
			</tr>
			<tr>
				<td>
					<label>
						<input
							name="import_settings"
							type="checkbox"
							checked
							value="1" />
		
						<?php _e('Import similar maps settings', 'W2DC'); ?>
					</label>
				</td>
			</tr>
			<tr>
				<td>
					<label>
						<?php _e('Choose directory level to assume listings from Google Maps plugin', 'W2DC'); ?>
						<select name="import_level">
							<?php
							global $w2dc_instance;
							foreach ($w2dc_instance->levels->levels_array AS $level)
								echo "<option value=".$level->id.">".$level->name."</option>"; 
							?>
						</select>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
<?php submit_button(__('Import', 'W2DC')); ?>
</form>

<?php w2gm_renderTemplate('admin_footer.tpl.php'); ?>