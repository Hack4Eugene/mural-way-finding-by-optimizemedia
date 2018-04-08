<?php 
global $w2dc_instance;

$packages = $w2dc_instance->listings_package_product->get_all_packages();
?>

<div class="w2dc-content">
	<div class="w2dc-submit-section-adv">
		<?php if ($packages): ?>
			<?php $max_columns_in_row = 3; ?>
			<?php $levels_counter = count($packages); ?>
			<?php if ($levels_counter > $max_columns_in_row) $levels_counter = $max_columns_in_row; ?>
			<?php $cols_width = floor(12/$levels_counter); ?>
			<?php $cols_width_percents = (100-1)/$levels_counter; ?>
	
			<?php $counter = 0; ?>
			<?php $tcounter = 0; ?>
			<?php foreach ($packages AS $package): ?>
			<?php $tcounter++; ?>
			<?php if ($counter == 0): ?>
			<div class="w2dc-row" style="text-align: center;">
			<?php endif; ?>
	
				<div class="w2dc-col-sm-<?php echo $cols_width; ?> w2dc-plan-column" style="width: <?php echo $cols_width_percents; ?>%; max-width: 800px;">
					<div class="w2dc-panel w2dc-panel-default w2dc-text-center w2dc-choose-plan">
						<div class="w2dc-panel-heading">
							<h3>
								<?php echo $package->get_title(); ?>
							</h3>
							<?php if ($package->get_description()): ?><a class="w2dc-hint-icon" href="javascript:void(0);" data-content="<?php echo esc_attr(nl2br($package->get_description())); ?>" data-html="true" rel="popover" data-placement="bottom" data-trigger="hover"></a><?php endif; ?>
						</div>
						<ul class="w2dc-list-group">
							<li class="w2dc-list-group-item">
								<?php
								if ($package->get_price() == 0)
									$cost_text = '<span class="w2dc-price w2dc-payments-free">' . __('FREE', 'W2DC') . '</span>';
								else
									$cost_text = '<span class="w2dc-price">' . $package->get_price_html() . '</span>';
		 
								echo $cost_text;
								?>
							</li>
							<?php foreach ($w2dc_instance->levels->levels_array AS $w2dc_level): ?>
							<li class="w2dc-list-group-item">
								<?php echo $w2dc_level->name; ?> <?php echo $w2dc_instance->current_directory->plural; ?>:
								<strong><?php echo $package->get_listings_number_by_level($w2dc_level->id); ?></strong>
								<a class="w2dc-hint-icon" href="javascript:void(0);" data-content="<?php echo esc_attr('
									<div class="w2dc-panel w2dc-panel-default w2dc-text-center w2dc-choose-plan">
										<div class="w2dc-panel-heading ' . (($w2dc_level->featured) ? 'w2dc-featured' : '') . '">
											<h3>' . $w2dc_level->name . '</h3>
										</div>
										<ul class="w2dc-list-group">
										<li class="w2dc-list-group-item">
											'. __('Active period', 'W2DC') .':
											'. $w2dc_level->getActivePeriodString() . '
										</li>
										' . w2dc_renderTemplate(array(W2DC_FSUBMIT_TEMPLATES_PATH, 'level_details.tpl.php'), array('args' => array('show_period' => 0,'show_sticky' => 1,'show_featured' => 1,'show_categories' => 1,'show_locations' => 1,'show_maps' => 1,'show_images' => 1,'show_videos' => 1,'columns_same_height' => 1,), 'level' => $w2dc_level), true) . '
										</ul>
									</div>'); ?>" data-html="true" rel="popover" data-placement="auto right" data-trigger="hover"></a>
							</li>
							<?php endforeach; ?>
							<li class="w2dc-list-group-item">
								<?php
								echo apply_filters('woocommerce_loop_add_to_cart_link',
										sprintf('<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="w2dc-btn w2dc-btn-primary ajax_add_to_cart %s product_type_%s">%s</a>',
												esc_url($package->add_to_cart_url()),
												esc_attr($package->get_id()),
												esc_attr($package->get_sku()),
												$package->is_purchasable() ? 'add_to_cart_button' : '',
												esc_attr($package->product_type),
												esc_html($package->add_to_cart_text())
										),
										$package);
								?>
							</li>
						</ul>
					</div>          
				</div>
	
			<?php $counter++; ?>
			<?php if ($counter == $max_columns_in_row || $tcounter == $levels_counter): ?>
			</div>
			<?php endif; ?>
			<?php if ($counter == $max_columns_in_row) $counter = 0; ?>
			<?php endforeach; ?>
		<?php else: ?>
			<p><?php _e("There aren't any packagess", "W2DC"); ?></p>
		<?php endif; ?>
	</div>

	<?php if (!isset($hide_navigation) || !$hide_navigation): ?>
	<div class="w2dc-submit-section-adv">
		<?php if (count($w2dc_instance->levels->levels_array) > 1): ?>
		<a href="<?php echo w2dc_submitUrl(); ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('&larr; Return to Levels Table', 'W2DC');?></a>
		<br />
		<br />
		<?php endif; ?>
		<a href="<?php echo w2dc_directoryUrl(); ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('&larr; Return to Home', 'W2DC');?></a>
	</div>
	<?php endif; ?>
</div>
