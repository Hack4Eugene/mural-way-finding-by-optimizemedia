<?php w2dc_renderTemplate('admin_header.tpl.php'); ?>

<?php screen_icon('edit-pages'); ?>
<h2>
	<?php _e('Configure number/price search field', 'W2DC'); ?>
</h2>

<script>
	(function($) {
		"use strict";
	
		$(function() {
			$("#add_selection_item").click(function() {
				$("#selection_items_wrapper").append('<div class="selection_item"><input name="min_max_options[]" type="text" size="9" value="" /><img class="w2dc-delete-selection-item" src="<?php echo W2DC_RESOURCES_URL . 'images/delete.png'?>" title="<?php esc_attr_e('Remove option', 'W2DC')?>" /></div>');
			});
			$(document).on("click", ".w2dc-delete-selection-item", function() {
				$(this).parent().remove();
			});
		});
	})(jQuery);
</script>

<form method="POST" action="">
	<?php wp_nonce_field(W2DC_PATH, 'w2dc_configure_content_fields_nonce');?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label><?php _e('Search mode', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></label>
				</th>
				<td>
					<label>
						<input
							name="mode"
							type="radio"
							value="exact_number"
							<?php checked($search_field->mode, 'exact_number'); ?> />
						<?php _e('Enter exact number for search', 'W2DC'); ?>
					</label>
					<br />
					<label>
						<input
							name="mode"
							type="radio"
							value="min_max"
							<?php checked($search_field->mode, 'min_max'); ?> />
						<?php _e('Select Min-Max options for search', 'W2DC'); ?>
					</label>
					<br />
					<label>
						<input
							name="mode"
							type="radio"
							value="min_max_slider"
							<?php checked($search_field->mode, 'min_max_slider'); ?> />
						<?php _e('Search range slider with steps from Min-Max options', 'W2DC'); ?>
					</label>
					<br />
					<label>
						<input
							name="mode"
							type="radio"
							value="range_slider"
							<?php checked($search_field->mode, 'range_slider'); ?> />
						<?php _e('Search range slider with step 1.', 'W2DC'); ?>
					</label>
					 <?php _e('From:', 'W2DC'); ?><input type="text" name="slider_step_1_min" size=4 value="<?php echo $search_field->slider_step_1_min; ?>" /> <?php _e('To:', 'W2DC'); ?><input type="text" name="slider_step_1_max" size=4 value="<?php echo $search_field->slider_step_1_max; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Min-Max options:', 'W2DC'); ?>
				</th>
				<td>
					<div id="selection_items_wrapper">
						<?php if (count($search_field->min_max_options)): ?>
						<?php foreach ($search_field->min_max_options AS $item): ?>
						<div class="selection_item">
							<input
								name="min_max_options[]"
								type="text"
								size="9"
								value="<?php echo $item; ?>" />
							<img class="w2dc-delete-selection-item" src="<?php echo W2DC_RESOURCES_URL . 'images/delete.png'?>" title="<?php esc_attr_e('Remove min-max option', 'W2DC')?>" />
						</div>
						<?php endforeach; ?>
						<?php else: ?>
						<div class="selection_item">
							<input
								name="min_max_options[]"
								type="text"
								size="9"
								value="" />
							<img class="w2dc-delete-selection-item" src="<?php echo W2DC_RESOURCES_URL . 'images/delete.png'?>" title="<?php esc_attr_e('Remove min-max option', 'W2DC')?>" />
						</div>
						<?php endif; ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="button" id="add_selection_item" class="button button-primary" value="<?php esc_attr_e('Add min-max option', 'W2DC'); ?>" />
	
	<?php submit_button(__('Save changes', 'W2DC')); ?>
</form>

<?php w2dc_renderTemplate('admin_footer.tpl.php'); ?>