<script>
	(function($) {
		"use strict";
	
		$(function() {
			var locations_number = <?php echo $listing->level->locations_number; ?>;
	
			<?php if ($listing->level->google_map && $listing->level->google_map_markers): ?>
			<?php if (get_option('w2dc_map_markers_type') == 'images'): ?>
				var map_icon_file_input;
				$(document).on("click", ".select_map_icon", function() {
					map_icon_file_input = $(this).parents(".w2dc-location-input").find('.w2dc-map-icon-file');
		
					var dialog = $('<div id="select_map_icon_dialog"></div>').dialog({
						width: ($(window).width()*0.5),
						height: ($(window).height()*0.8),
						modal: true,
						resizable: false,
						draggable: false,
						title: '<?php echo esc_js(__('Select map marker icon', 'W2DC')); ?>',
						open: function() {
							w2dc_ajax_loader_show();
							$.ajax({
								type: "POST",
								url: w2dc_js_objects.ajaxurl,
								data: {'action': 'w2dc_select_map_icon'},
								dataType: 'html',
								success: function(response_from_the_action_function){
									if (response_from_the_action_function != 0) {
										$('#select_map_icon_dialog').html(response_from_the_action_function);
										if (map_icon_file_input.val())
											$(".w2dc-icon[icon_file='"+map_icon_file_input.val()+"']").addClass("w2dc-selected-icon");
									}
								},
								complete: function() {
									w2dc_ajax_loader_hide();
								}
							});
							$(document).on("click", ".ui-widget-overlay", function() { $('#select_map_icon_dialog').remove(); });
						},
						close: function() {
							$('#select_map_icon_dialog').remove();
						}
					});
				});
				$(document).on("click", ".w2dc-icon", function() {
					$(".w2dc-selected-icon").removeClass("w2dc-selected-icon");
					if (map_icon_file_input) {
						map_icon_file_input.val($(this).attr('icon_file'));
						map_icon_file_input = false;
						$(this).addClass("w2dc-selected-icon");
						$('#select_map_icon_dialog').remove();
						w2dc_generateMap_backend();
					}
				});
				$(document).on("click", "#reset_icon", function() {
					if (map_icon_file_input) {
						$(".w2dc-selected-icon").removeClass("w2dc-selected-icon");
						map_icon_file_input.val('');
						map_icon_file_input = false;
						$('#select_map_icon_dialog').remove();
						w2dc_generateMap_backend();
					}
				});
			<?php else: ?>
				var map_icon_file_input;
				$(document).on("click", ".select_map_icon", function() {
					map_icon_file_input = $(this).parents(".w2dc-location-input").find('.w2dc-map-icon-file');

					var dialog = $('<div id="select_marker_icon_dialog"></div>').dialog({
						width: ($(window).width()*0.5),
						height: ($(window).height()*0.8),
						modal: true,
						resizable: false,
						draggable: false,
						title: '<?php echo esc_js(__('Select map marker icon', 'W2DC') . ((get_option('w2dc_map_markers_type') == 'icons') ? __(' (icon and color may depend on selected categories)', 'W2DC') : '')); ?>',
						open: function() {
							w2dc_ajax_loader_show();
							$.ajax({
								type: "POST",
								url: w2dc_js_objects.ajaxurl,
								data: {'action': 'w2dc_select_field_icon'},
								dataType: 'html',
								success: function(response_from_the_action_function){
									if (response_from_the_action_function != 0) {
										$('#select_marker_icon_dialog').html(response_from_the_action_function);
										if (map_icon_file_input.val())
											$("#"+map_icon_file_input.val()).addClass("w2dc-selected-icon");
									}
								},
								complete: function() {
									w2dc_ajax_loader_hide();
								}
							});
							$(document).on("click", ".ui-widget-overlay", function() { $('#select_marker_icon_dialog').remove(); });
						},
						close: function() {
							$('#select_marker_icon_dialog').remove();
						}
					});
				});
				$(document).on("click", ".w2dc-fa-icon", function() {
					$(".w2dc-selected-icon").removeClass("w2dc-selected-icon");
					if (map_icon_file_input) {
						map_icon_file_input.val($(this).attr('id'));
						map_icon_file_input = false;
						$(this).addClass("w2dc-selected-icon");
						$('#select_marker_icon_dialog').remove();
						w2dc_generateMap_backend();
					}
				});
				$(document).on("click", "#reset_fa_icon", function() {
					if (map_icon_file_input) {
						$(".w2dc-selected-icon").removeClass("w2dc-selected-icon");
						map_icon_file_input.val('');
						map_icon_file_input = false;
						$('#select_marker_icon_dialog').remove();
						w2dc_generateMap_backend();
					}
				});
			<?php endif; ?>
			<?php endif; ?>
			
			$(".add_address").click(function() {
				w2dc_ajax_loader_show();
				$.ajax({
					type: "POST",
					url: w2dc_js_objects.ajaxurl,
					data: {'action': 'w2dc_add_location_in_metabox', 'post_id': <?php echo $listing->post->ID; ?>},
					success: function(response_from_the_action_function){
						if (response_from_the_action_function != 0) {
							$("#w2dc-locations-wrapper").append(response_from_the_action_function);
							$(".delete_location").show();
							if (locations_number == $(".w2dc-location-in-metabox").length)
								$(".add_address").hide();
							w2dc_setupAutocomplete();
						}
					},
					complete: function() {
						w2dc_ajax_loader_hide();
					}
				});
			});
			$(document).on("click", ".delete_location", function() {
				$(this).parents(".w2dc-location-in-metabox").remove();
				if ($(".w2dc-location-in-metabox").length == 1)
					$(".delete_location").hide();
	
				<?php if ($listing->level->google_map): ?>
				w2dc_generateMap_backend();
				<?php endif; ?>
	
				if (locations_number > $(".w2dc-location-in-metabox").length)
					$(".add_address").show();
			});
	
			$(document).on("click", ".w2dc-manual-coords", function() {
	        	if ($(this).is(":checked"))
	        		$(this).parents(".w2dc-manual-coords-wrapper").find(".w2dc-manual-coords-block").show(200);
	        	else
	        		$(this).parents(".w2dc-manual-coords-wrapper").find(".w2dc-manual-coords-block").hide(200);
	        });
	
	        if (locations_number > $(".w2dc-location-in-metabox").length)
				$(".add_address").show();
		});
	})(jQuery);
</script>

<div class="w2dc-locations-metabox w2dc-content">
	<div id="w2dc-locations-wrapper" class="w2dc-form-horizontal">
		<?php
		if ($listing->locations)
			foreach ($listing->locations AS $location)
				w2dc_renderTemplate('locations/locations_in_metabox.tpl.php', array('listing' => $listing, 'location' => $location, 'locations_levels' => $locations_levels, 'delete_location_link' => (count($listing->locations) > 1) ? true : false));
		else
			w2dc_renderTemplate('locations/locations_in_metabox.tpl.php', array('listing' => $listing, 'location' => new w2dc_location, 'locations_levels' => $locations_levels, 'delete_location_link' => false));
		?>
	</div>
	
	<?php if ($listing->level->locations_number > 1): ?>
	<div class="w2dc-row w2dc-form-group w2dc-location-input">
		<div class="w2dc-col-md-12">	
			<a class="add_address" style="display: none;" href="javascript: void(0);"><img src="<?php echo W2DC_RESOURCES_URL; ?>images/map_add.png" /></a>&nbsp;<?php echo sprintf('<a class="add_address" style="display: none;" href="javascript:void(0);">%s</a>', __('Add address', 'W2DC')); ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ($listing->level->google_map): ?>
	<div class="w2dc-row w2dc-form-group w2dc-location-input">
		<div class="w2dc-col-md-12">
			<input type="hidden" name="map_zoom" class="w2dc-map-zoom" value="<?php echo $listing->map_zoom; ?>" />
			<input type="button" class="w2dc-btn w2dc-btn-primary" onClick="w2dc_generateMap_backend(); return false;" value="<?php esc_attr_e('Generate on google map', 'W2DC'); ?>" />
		</div>
	</div>
	<div class="w2dc-maps-canvas" id="w2dc-maps-canvas" style="width: auto; height: 450px;"></div>
	<?php endif;?>
</div>