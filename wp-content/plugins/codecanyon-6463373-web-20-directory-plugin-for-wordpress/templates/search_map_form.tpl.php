<?php if (get_option('w2dc_show_what_search') || get_option('w2dc_show_where_search')): ?>
<form action="<?php echo $search_url; ?>" class="w2dc-content w2dc-search-map-form w2dc-search-form">
	<?php
	global $wp_rewrite;
	if (!$wp_rewrite->using_permalinks() && $w2dc_instance->index_page_id && (get_option('show_on_front') != 'page' || get_option('page_on_front') != $w2dc_instance->index_page_id)): ?>
	<input type="hidden" name="page_id" value="<?php echo $w2dc_instance->index_page_id; ?>" />
	<?php endif; ?>
	<?php if ($w2dc_instance->index_page_id): ?>
	<input type="hidden" name="w2dc_action" value="search" />
	<?php else: ?>
	<input type="hidden" name="s" value="search" />
	<?php endif; ?>
	<?php if ($hash): ?>
	<input type="hidden" name="hash" value="<?php echo $hash; ?>" />
	<?php endif; ?>
	<?php if ($controller): ?>
	<input type="hidden" name="controller" value="<?php echo $controller; ?>" />
	<?php endif; ?>
	<?php
	// adapted for WPML
	global $sitepress;
	if (function_exists('wpml_object_id_filter') && $sitepress):
		if ($sitepress->get_option('language_negotiation_type') == 3): ?>
		<input type="hidden" name="lang" value="<?php echo $sitepress->get_current_language(); ?>" />
		<?php endif; ?>
	<?php endif; ?>
	<input type="hidden" name="include_categories_children" value="1" />

	<div class="w2dc-search-overlay w2dc-container-fluid">
	<?php if (get_option('w2dc_show_what_search') && get_option('w2dc_show_categories_search') && w2dc_is_anyone_in_taxonomy(W2DC_CATEGORIES_TAX)): ?>
		<div class="w2dc-row">
			<?php $col_md = 12; ?>
			<?php
			if (get_query_var('category-w2dc') && ($category_object = w2dc_get_term_by_path(get_query_var('category-w2dc'))))
				$term_id = $category_object->term_id;
			elseif (isset($_GET['categories']) && is_numeric($_GET['categories']))
				$term_id = $_GET['categories'];
			else 
				$term_id = 0; ?>
			<div class="w2dc-col-md-<?php echo $col_md; ?>">
				<?php w2dc_tax_dropdowns_init(W2DC_CATEGORIES_TAX, 'categories', $term_id, get_option('w2dc_show_category_count_in_search'), array(), array(__('Category', 'W2DC'), __('Subcategory', 'W2DC'), __('Subcategory', 'W2DC'), __('Subcategory', 'W2DC')), null, $exact_categories); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if (get_option('w2dc_show_where_search')): ?>
		<div class="w2dc-row">
			<?php $col_md = 12; ?>
			<?php if (get_option('w2dc_show_locations_search') && w2dc_is_anyone_in_taxonomy(W2DC_LOCATIONS_TAX)): ?>
			<?php
			if (get_query_var('location-w2dc') && ($location_object = w2dc_get_term_by_path(get_query_var('location-w2dc'))))
				$term_id = $location_object->term_id;
			elseif (isset($_GET['location_id']) && is_numeric($_GET['location_id']))
				$term_id = $_GET['location_id'];
			else 
				$term_id = 0; ?>
			<div class="w2dc-col-md-<?php echo $col_md; ?>">
				<?php w2dc_tax_dropdowns_init(W2DC_LOCATIONS_TAX, 'location_id', $term_id, get_option('w2dc_show_location_count_in_search'), array(), $w2dc_instance->locations_levels->getSelectionsArray(), null, $exact_locations); ?>
			</div>
			<?php endif; ?>
			<?php if (get_option('w2dc_show_address_search')): ?>
			<?php if (get_option('w2dc_address_geocode')): ?>
			<script>
				(function($) {
					"use strict";

					$(function() {
						$(".w2dc-get-location-<?php echo $random_id; ?>").click(function() { w2dc_geocodeField($("#address_<?php echo $random_id; ?>"), "<?php echo esc_js(__('GeoLocation service does not work on your device!', 'W2DC')); ?>"); });
					});
				})(jQuery);
			</script>
			<div class="w2dc-col-md-<?php echo $col_md; ?> w2dc-has-feedback">
				<input type="text" name="address" id="address_<?php echo $random_id; ?>" class="w2dc-form-control <?php if (get_option('w2dc_address_autocomplete')): ?>w2dc-field-autocomplete<?php endif; ?>" placeholder="<?php esc_attr_e('Enter address or zip code', 'W2DC'); ?>" value="<?php if (isset($_GET['address'])) echo esc_attr(stripslashes($_GET['address'])); ?>" />
				<span class="w2dc-get-location w2dc-get-location-<?php echo $random_id; ?> w2dc-glyphicon w2dc-glyphicon-screenshot w2dc-form-control-feedback" title="<?php esc_attr_e('Get my location', 'W2DC'); ?>"></span>
			</div>
			<?php else: ?>
			<div class="w2dc-col-md-<?php echo $col_md; ?>">
				<input type="text" name="address" id="address_<?php echo $random_id; ?>" class="w2dc-form-control <?php if (get_option('w2dc_address_autocomplete')): ?>w2dc-field-autocomplete<?php endif; ?>" placeholder="<?php esc_attr_e('Enter address or zip code', 'W2DC'); ?>" value="<?php if (isset($_GET['address'])) echo esc_attr(stripslashes($_GET['address'])); ?>" />
			</div>
			<?php endif; ?>
			<?php endif; ?>

			<?php if (get_option('w2dc_show_radius_search')): ?>
			<?php 
			if (isset($_GET['radius']) && is_numeric($_GET['radius']))
				$radius = $_GET['radius'];
			else
				$radius = get_option('w2dc_radius_search_default');
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
		</div>
	<?php endif; ?>

		<div class="w2dc-row">
			<div class="w2dc-col-md-12 w2dc-search-map-button">
				<input type="submit" name="submit" class="w2dc-btn w2dc-btn-primary w2dc-search-map-button" value="<?php esc_attr_e('Search on map', 'W2DC'); ?>" />
			</div>
		</div>
	</div>
</form>
<?php endif; ?>