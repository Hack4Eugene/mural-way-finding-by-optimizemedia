<?php if ($args['show_what_search'] || $args['show_where_search']): ?>
<form action="<?php echo $search_url; ?>" class="w2dc-content w2dc-search-form">
	<?php $search_form->outputHiddenFields(); ?>

	<div class="w2dc-search-overlay w2dc-container-fluid">
	<?php if ($args['show_what_search']): ?>
		<div class="w2dc-row">
			<?php if (($args['show_categories_search'] && w2dc_is_anyone_in_taxonomy(W2DC_CATEGORIES_TAX)) && $args['show_keywords_search']) $col_md = 6; else $col_md = 12; ?>
			<?php if ($columns == 1) $col_md = 12; ?>
			<div class="w2dc-search-section-label w2dc-col-md-12"><?php _e('Search for:', 'W2DC'); ?></div>
			<?php do_action('pre_search_what_form_html', $random_id); ?>
			<?php if ($args['show_categories_search'] && w2dc_is_anyone_in_taxonomy(W2DC_CATEGORIES_TAX)): ?>
			<?php
			if (get_query_var('category-w2dc') && ($category_object = w2dc_get_term_by_path(get_query_var('category-w2dc'))))
				$term_id = $category_object->term_id;
			elseif (isset($_GET['categories']) && is_numeric($_GET['categories']))
				$term_id = $_GET['categories'];
			else 
				$term_id = w2dc_getValue($args, 'category'); ?>
			<div class="w2dc-col-md-<?php echo $col_md; ?>">
				<?php w2dc_tax_dropdowns_init(W2DC_CATEGORIES_TAX, 'categories', $term_id, get_option('w2dc_show_category_count_in_search'), array(), array(__('Category', 'W2DC'), __('Subcategory', 'W2DC'), __('Subcategory', 'W2DC'), __('Subcategory', 'W2DC')), null, $args['exact_categories']); ?>
			</div>
			<?php endif; ?>
			<?php if ($args['show_keywords_search']): ?>
			<div class="w2dc-col-md-<?php echo $col_md; ?> w2dc-form-group">
				<input type="text" name="what_search" class="w2dc-form-control" size="38" placeholder="<?php esc_attr_e('Enter keywords', 'W2DC'); ?>" value="<?php echo esc_attr(stripslashes(w2dc_getValue($_GET, 'what_search', w2dc_getValue($args, 'what_search')))); ?>" />
			</div>
			<?php endif; ?>
		</div>

		<?php $w2dc_instance->search_fields->render_content_fields($random_id, $columns, $search_form); ?>

		<?php do_action('post_search_what_form_html', $random_id); ?>
	<?php endif; ?>

	<?php if ($args['show_where_search']): ?>
		<div class="w2dc-row">
			<?php if (($args['show_locations_search'] && w2dc_is_anyone_in_taxonomy(W2DC_LOCATIONS_TAX)) && $args['show_address_search']) $col_md = 6; else $col_md = 12; ?>
			<?php if ($columns == 1) $col_md = 12; ?>
			<div class="w2dc-search-section-label w2dc-col-md-12"><?php _e('Search near:', 'W2DC'); ?></div>
			<?php if ($args['show_locations_search'] && w2dc_is_anyone_in_taxonomy(W2DC_LOCATIONS_TAX)): ?>
			<?php
			if (get_query_var('location-w2dc') && ($location_object = w2dc_get_term_by_path(get_query_var('location-w2dc'))))
				$term_id = $location_object->term_id;
			elseif (isset($_GET['location_id']) && is_numeric($_GET['location_id']))
				$term_id = $_GET['location_id'];
			else 
				$term_id = w2dc_getValue($args, 'location');; ?>
			<div class="w2dc-col-md-<?php echo $col_md; ?>">
				<?php w2dc_tax_dropdowns_init(W2DC_LOCATIONS_TAX, 'location_id', $term_id, get_option('w2dc_show_location_count_in_search'), array(), $w2dc_instance->locations_levels->getSelectionsArray(), null, $args['exact_locations']); ?>
			</div>
			<?php endif; ?>
			<?php if ($args['show_address_search']): ?>
			<?php if (get_option('w2dc_address_geocode')): ?>
			<script>
				(function($) {
					"use strict";

					$(function() {
						$(".w2dc-get-location-<?php echo $random_id; ?>").click(function() { w2dc_geocodeField($("#address_<?php echo $random_id; ?>"), "<?php echo esc_js(__('GeoLocation service does not work on your device!', 'W2DC')); ?>"); });
					});
				})(jQuery);
			</script>
			<div class="w2dc-col-md-<?php echo $col_md; ?> w2dc-form-group w2dc-has-feedback">
				<input type="text" name="address" id="address_<?php echo $random_id; ?>" class="w2dc-form-control <?php if (get_option('w2dc_address_autocomplete')): ?>w2dc-field-autocomplete<?php endif; ?>" placeholder="<?php esc_attr_e('Enter address or zip code', 'W2DC'); ?>" value="<?php echo esc_attr(stripslashes(w2dc_getValue($_GET, 'address', w2dc_getValue($args, 'address')))); ?>" />
				<span class="w2dc-get-location w2dc-get-location-<?php echo $random_id; ?> w2dc-glyphicon w2dc-glyphicon-screenshot w2dc-form-control-feedback" title="<?php esc_attr_e('Get my location', 'W2DC'); ?>"></span>
			</div>
			<?php else: ?>
			<div class="w2dc-col-md-<?php echo $col_md; ?> w2dc-form-group">
				<input type="text" name="address" id="address_<?php echo $random_id; ?>" class="w2dc-form-control <?php if (get_option('w2dc_address_autocomplete')): ?>w2dc-field-autocomplete<?php endif; ?>" placeholder="<?php esc_attr_e('Enter address or zip code', 'W2DC'); ?>" value="<?php echo esc_attr(stripslashes(w2dc_getValue($_GET, 'address', w2dc_getValue($args, 'address')))); ?>" />
			</div>
			<?php endif; ?>
			<?php endif; ?>

			<?php if ($args['show_radius_search']): ?>
			<?php 
			if (isset($_GET['radius']) && is_numeric($_GET['radius']))
				$radius = $_GET['radius'];
			else
				$radius = w2dc_getValue($args, 'radius');
			?>
			<script>
				(function($) {
					"use strict";

					$(function() {
						$('#radius_slider_<?php echo $random_id; ?>').slider({
							<?php if (function_exists('is_rtl') && is_rtl()): ?>
							isRTL: true,
							<?php endif; ?>
							min: parseInt(slider_params.min),
							max: parseInt(slider_params.max),
							range: "min",
							value: $("#radius_<?php echo $random_id; ?>").val(),
							slide: function(event, ui) {
								$("#radius_label_<?php echo $random_id; ?>").html(ui.value);
								$("#radius_<?php echo $random_id; ?>").val(ui.value);
							}
						});
					});
				})(jQuery);
			</script>
			<div class="w2dc-col-md-12 w2dc-form-group w2dc-jquery-ui-slider">
				<div class="w2dc-search-radius-label">
					<?php _e('Search in radius', 'W2DC'); ?>
					<strong id="radius_label_<?php echo $random_id; ?>"><?php echo $radius; ?></strong>
					<?php if (get_option('w2dc_miles_kilometers_in_search') == 'miles') _e('miles', 'W2DC'); else _e('kilometers', 'W2DC'); ?>
				</div>
				<div id="radius_slider_<?php echo $random_id; ?>"></div>
				<input type="hidden" name="radius" id="radius_<?php echo $random_id; ?>" value="<?php echo $radius; ?>" />
			</div>
			<?php endif; ?>

			<?php do_action('post_search_where_form_html', $random_id); ?>
		</div>
	<?php endif; ?>

		<?php do_action('post_search_form_html', $random_id); ?>

		<div class="w2dc-row clear_float">
			<div class="w2dc-col-md-6 w2dc-form-group w2dc-pull-right w2dc-text-right">
				<input type="submit" name="submit" class="w2dc-btn w2dc-btn-primary" value="<?php esc_attr_e('Search', 'W2DC'); ?>" />
			</div>

			<?php if ($search_form->is_advanced_search_panel): ?>
			<script>
				(function($) {
					"use strict";
	
					$(function() {
						$("#w2dc-advanced-search-label_<?php echo $random_id; ?>").click(function(){
							if ($("#w2dc_advanced_search_fields_<?php echo $random_id; ?>").is(":hidden")) {
								$("#use_advanced_<?php echo $random_id; ?>").val(1);
								$("#w2dc_advanced_search_fields_<?php echo $random_id; ?>").show();
							} else {
								$("#use_advanced_<?php echo $random_id; ?>").val(0);
								$("#w2dc_advanced_search_fields_<?php echo $random_id; ?>").hide();
							}
						});
					});
				})(jQuery);
			</script>
			<div class="w2dc-col-md-6 w2dc-form-group w2dc-pull-left">
				<a id="w2dc-advanced-search-label_<?php echo $random_id; ?>" class="w2dc-advanced-search-label" href="javascript: void(0);"><?php _e('Advanced search', 'W2DC'); ?></a>
			</div>
			<?php endif; ?>

			<?php do_action('buttons_search_form_html', $random_id); ?>
		</div>
	</div>
</form>
<?php endif; ?>