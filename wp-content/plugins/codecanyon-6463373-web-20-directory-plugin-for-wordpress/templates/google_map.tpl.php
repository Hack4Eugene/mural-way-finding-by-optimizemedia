<?php if ($sticky_scroll || $height == '100%'): ?>
<script>
	(function($) {
		"use strict";
	
		$(function() {
			<?php if ($sticky_scroll): ?>
			window.sticky_scroll_toppadding_<?php echo $unique_map_id; ?> = <?php echo $sticky_scroll_toppadding; ?>;
			$("#w2dc-maps-canvas-wrapper-<?php echo $unique_map_id; ?>").width($("#w2dc-maps-canvas-wrapper-<?php echo $unique_map_id; ?>").parent().width()).css({ 'z-index': 100 });
			
			$("#w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>").position().left = $("#w2dc-maps-canvas-wrapper-<?php echo $unique_map_id; ?>").position().left;
			$("#w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>").position().top = $("#w2dc-maps-canvas-wrapper-<?php echo $unique_map_id; ?>").position().top;
			$("#w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>").width($("#w2dc-maps-canvas-wrapper-<?php echo $unique_map_id; ?>").width());
			$("#w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>").height($("#w2dc-maps-canvas-wrapper-<?php echo $unique_map_id; ?>").height());
	
			window.a = function() {
				var b = $(document).scrollTop();
				var d = $("#scroller_anchor_<?php echo $unique_map_id; ?>").offset().top-<?php echo $sticky_scroll_toppadding; ?>;
				var c = $("#w2dc-maps-canvas-wrapper-<?php echo $unique_map_id; ?>");
				var e = $("#w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>");
	
				// .scroller_bottom - this is special class used to restrict the area of scroll of map canvas
				if ($(".scroller_bottom").length)
					var f = $(".scroller_bottom").offset().top-($("#w2dc-maps-canvas-<?php echo $unique_map_id; ?>").height()+<?php echo $sticky_scroll_toppadding; ?>);
				else
					var f = $(document).height();
	
				if (b>d && b<f) {
					c.css({ position: "fixed", top: "<?php echo $sticky_scroll_toppadding; ?>px" });
					e.css({ position: "relative" });
				} else {
					if (b<=d) {
						c.css({ position: "relative", top: "" });
						e.css({ position: "absolute" });
					}
					if (b>=f) {
						c.css({ position: "absolute" });
						c.offset({ top: f });
						e.css({ position: "absolute" });
					}
				}
			};
			$(window).scroll(a);
			a();
			$("#w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>").css({ position: "absolute" });
			<?php endif; ?>
	
			<?php if ($height == '100%'): ?>
			$('#w2dc-maps-canvas-<?php echo $unique_map_id; ?>').height(function(index, height) {
				return window.innerHeight - $('#scroller_anchor_<?php echo $unique_map_id; ?>').outerHeight(true) - <?php echo $sticky_scroll_toppadding; ?>;
			});
			$(window).resize(function(){
				$('#w2dc-maps-canvas-<?php echo $unique_map_id; ?>').height(function(index, height) {
					return window.innerHeight - $('#scroller_anchor_<?php echo $unique_map_id; ?>').outerHeight(true) - <?php echo $sticky_scroll_toppadding; ?>;
				});
			});
			<?php endif; ?>
		});
	})(jQuery);
</script>
<?php endif; ?>
<div class="w2dc-content">
<?php if (!$static_image): ?>
	<script>
		w2dc_map_markers_attrs_array.push(new w2dc_map_markers_attrs('<?php echo $unique_map_id; ?>', eval(<?php echo $locations_options; ?>), <?php echo ($enable_radius_circle) ? 1 : 0; ?>, <?php echo ($enable_clusters) ? 1 : 0; ?>, <?php echo ($show_summary_button) ? 1 : 0; ?>, <?php echo ($show_readmore_button) ? 1 : 0; ?>, <?php echo ($draw_panel) ? 1 : 0; ?>, '<?php echo esc_js($map_style_name); ?>', <?php echo ($enable_full_screen) ? 1 : 0; ?>, <?php echo ($enable_wheel_zoom) ? 1 : 0; ?>, <?php echo ($enable_dragging_touchscreens) ? 1 : 0; ?>, <?php echo ($center_map_onclick) ? 1 : 0; ?>, <?php echo $map_args; ?>));

		<?php if ($search_form): ?>
		(function($) {
			"use strict";

			$(function() {
				$("#w2dc-draggable-search-<?php echo $unique_map_id; ?>").show();
			});
		})(jQuery);
		<?php endif; ?>
	</script>

	<?php if ($sticky_scroll || $height == '100%'): ?>
	<div id="scroller_anchor_<?php echo $unique_map_id; ?>"></div> 
	<?php endif; ?>

	<div id="w2dc-maps-canvas-wrapper-<?php echo $unique_map_id; ?>" class="w2dc-maps-canvas-wrapper">
		<?php if ($search_form): ?>
		<div class="w2dc-search-map-block" id="w2dc-draggable-search-<?php echo $unique_map_id; ?>" style="display: none;">
			<?php
			$search_form = new w2dc_search_map_form($unique_map_id, $controller, $directories);
			echo $search_form->display();
			?>
		</div>
		<?php endif; ?>
		<div id="w2dc-maps-canvas-<?php echo $unique_map_id; ?>" class="w2dc-maps-canvas" <?php if ($custom_home): ?>data-custom-home="1"<?php endif; ?> data-shortcode-hash="<?php echo $unique_map_id; ?>" style="<?php if ($width) echo 'max-width:' . $width . 'px'; else echo 'width: auto'; ?>; height: <?php if ($height) echo $height; else echo '300'; ?>px"></div>
	</div>

	<?php if ($sticky_scroll): ?>
	<div id="w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>" style="position: relative"></div>
	<?php endif; ?>
	
	<?php if ($show_directions): ?>
	<div class="w2dc-row w2dc-form-group">
		<?php if (get_option('w2dc_directions_functionality') == 'builtin'): ?>
		<label class="w2dc-col-md-12 w2dc-control-label"><?php _e('Get directions from:', 'W2DC'); ?></label>
		<?php if (get_option('w2dc_address_geocode')): ?>
		<script>
			jQuery(document).ready(function($) {
				jQuery(".w2dc-get-location-<?php echo $unique_map_id; ?>").click(function() { w2dc_geocodeField(jQuery("#from_direction_<?php echo $unique_map_id; ?>"), "<?php echo esc_js(__('GeoLocation service does not work on your device!', 'W2DC')); ?>"); });
			});
		</script>
		<div class="w2dc-col-md-12 w2dc-has-feedback">
			<input type="text" id="from_direction_<?php echo $unique_map_id; ?>" class="w2dc-form-control <?php if (get_option('w2dc_address_autocomplete')): ?>w2dc-field-autocomplete<?php endif; ?>" placeholder="<?php esc_attr_e('Enter address or zip code', 'W2DC'); ?>" />
			<span class="w2dc-get-location w2dc-get-location-<?php echo $unique_map_id; ?> w2dc-glyphicon w2dc-glyphicon-screenshot w2dc-form-control-feedback" title="<?php esc_attr_e('Get my location', 'W2DC'); ?>"></span>
		</div>
		<?php else: ?>
		<div class="w2dc-col-md-12">
			<input type="text" id="from_direction_<?php echo $unique_map_id; ?>" class="w2dc-form-control <?php if (get_option('w2dc_address_autocomplete')): ?>w2dc-field-autocomplete<?php endif; ?>" placeholder="<?php esc_attr_e('Enter address or zip code', 'W2DC'); ?>" />
		</div>
		<?php endif; ?>
		<div class="w2dc-col-md-12">
			<?php $i = 1; ?>
			<?php foreach ($locations_array AS $location): ?>
			<div class="w2dc-radio">
				<label>
					<input type="radio" name="select_direction" class="select_direction_<?php echo $unique_map_id; ?>" <?php checked($i, 1); ?> value="<?php esc_attr_e($location->map_coords_1.' '.$location->map_coords_2); ?>" />
					<?php 
					if ($address = $location->getWholeAddress(false))
						echo $address;
					else 
						echo $location->map_coords_1.' '.$location->map_coords_2;
					?>
				</label>
			</div>
			<?php endforeach; ?>
		</div>
		<div class="w2dc-col-md-12">
			<input type="button" class="direction_button front-btn w2dc-btn w2dc-btn-primary" id="get_direction_button_<?php echo $unique_map_id; ?>" value="<?php esc_attr_e('Get directions', 'W2DC'); ?>">
		</div>
		<div class="w2dc-col-md-12">
			<div id="route_<?php echo $unique_map_id; ?>" class="w2dc-maps-direction-route"></div>
		</div>
		<?php elseif (get_option('w2dc_directions_functionality') == 'google'): ?>
		<label class="w2dc-col-md-12 w2dc-control-label"><?php _e('directions to:', 'W2DC'); ?></label>
		<form action="//maps.google.com" target="_blank">
			<input type="hidden" name="saddr" value="Current Location" />
			<div class="w2dc-col-md-12">
				<?php $i = 1; ?>
				<?php foreach ($locations_array AS $location): ?>
				<div class="w2dc-radio">
					<label>
						<input type="radio" name="daddr" class="select_direction_<?php echo $unique_map_id; ?>" <?php checked($i, 1); ?> value="<?php esc_attr_e($location->map_coords_1.','.$location->map_coords_2); ?>" />
						<?php 
						if ($address = $location->getWholeAddress(false))
							echo $address;
						else 
							echo $location->map_coords_1.' '.$location->map_coords_2;
						?>
					</label>
				</div>
				<?php endforeach; ?>
			</div>
			<div class="w2dc-col-md-12">
				<input class="w2dc-btn w2dc-btn-primary" type="submit" value="<?php esc_attr_e('Get directions', 'W2DC'); ?>" />
			</div>
		</form>
		<?php endif; ?>
	</div>
	<?php endif; ?>
<?php else: ?>
	<img src="//maps.googleapis.com/maps/api/staticmap?size=795x350&<?php foreach ($locations_array  AS $location) { if ($location->map_coords_1 != 0 && $location->map_coords_2 != 0) { ?>markers=<?php if (W2DC_MAP_ICONS_URL && $location->map_icon_file) { ?>icon:<?php echo W2DC_MAP_ICONS_URL . 'icons/' . urlencode($location->map_icon_file) . '%7C'; }?><?php echo $location->map_coords_1 . ',' . $location->map_coords_2 . '&'; }} ?><?php if ($map_zoom) echo 'zoom=' . $map_zoom; ?><?php if (get_option('w2dc_google_api_key')) echo '&key='.get_option('w2dc_google_api_key'); ?>" />
<?php endif; ?>
</div>