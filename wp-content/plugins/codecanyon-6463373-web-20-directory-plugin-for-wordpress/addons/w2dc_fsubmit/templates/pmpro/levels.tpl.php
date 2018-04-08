<?php 
global $wpdb, $pmpro_msg, $pmpro_msgt, $pmpro_levels, $current_user;
if($pmpro_msg)
{
?>
<div class="pmpro_message <?php echo $pmpro_msgt?>"><?php echo $pmpro_msg?></div>
<?php
}
?>
<div class="w2dc-content">
	<div class="w2dc-submit-section-adv">
		<?php $max_columns_in_row = 3; ?>
		<?php $levels_counter = count($pmpro_levels); ?>
		<?php if ($levels_counter > $max_columns_in_row) $levels_counter = $max_columns_in_row; ?>
		<?php $cols_width = floor(12/$levels_counter); ?>
		<?php $cols_width_percents = (100-1)/$levels_counter; ?>

		<?php $counter = 0; ?>
		<?php $tcounter = 0; ?>
		<?php foreach ($pmpro_levels AS $pmpro_level): ?>
		<?php
			if(isset($current_user->membership_level->ID))
				$current_level = ($current_user->membership_level->ID == $pmpro_level->id);
			else
				$current_level = false;
		?>
		<?php $tcounter++; ?>
		<?php if ($counter == 0): ?>
		<div class="w2dc-row" style="text-align: center;">
		<?php endif; ?>

			<div class="w2dc-col-sm-<?php echo $cols_width; ?> w2dc-plan-column" style="width: <?php echo $cols_width_percents; ?>%;">
				<div class="w2dc-panel w2dc-panel-default w2dc-text-center w2dc-choose-plan">
					<div class="w2dc-panel-heading <?php if ($current_level && (pmpro_isLevelRecurring($current_user->membership_level) || empty($current_user->membership_level->enddate))): ?>w2dc-featured<?php endif; ?>">
						<h3>
							<?php echo $pmpro_level->name; ?>
						</h3>
						<?php if ($pmpro_level->description): ?><a class="w2dc-hint-icon" href="javascript:void(0);" data-content="<?php echo esc_attr(nl2br($pmpro_level->description)); ?>" data-html="true" rel="popover" data-placement="bottom" data-trigger="hover"></a><?php endif; ?>
					</div>
					<ul class="w2dc-list-group">
						<li class="w2dc-list-group-item">
							<?php
							if(pmpro_isLevelFree($pmpro_level))
								$cost_text = '<span class="w2dc-price w2dc-payments-free">' . __('FREE', 'W2DC') . '</span>';
							else
								$cost_text = pmpro_getLevelCost($pmpro_level, true, true);
	 
							$expiration_text = pmpro_getLevelExpiration($pmpro_level);
							if(!empty($cost_text) && !empty($expiration_text))
								echo $cost_text . "<br />" . $expiration_text;
							elseif(!empty($cost_text))
								echo $cost_text;
							elseif(!empty($expiration_text))
								echo $expiration_text;
							?>
						</li>
						<?php foreach ($w2dc_instance->levels->levels_array AS $w2dc_level): ?>
						<li class="w2dc-list-group-item">
							<?php echo $w2dc_level->name; ?> <?php _e('listings', 'W2DC'); ?>:
							<strong><?php echo getPMPROlistingsNumberByLevel($pmpro_level->id, $w2dc_level->id); ?></strong>
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
								</div>'); ?>" data-html="true" rel="popover" data-placement="right" data-trigger="hover"></a>
						</li>
						<?php endforeach; ?>
						<li class="w2dc-list-group-item">
							<?php if (empty($current_user->membership_level->ID)): ?>
								<a class="w2dc-btn w2dc-btn-primary" href="<?php echo pmpro_url("checkout", "?level=" . $pmpro_level->id, "https")?>"><?php _e('Select', 'W2DC');?></a>
							<?php elseif (!$current_level): ?>                	
								<a class="w2dc-btn w2dc-btn-primary" href="<?php echo pmpro_url("checkout", "?level=" . $pmpro_level->id, "https")?>"><?php _e('Select', 'W2DC');?></a>
							<?php elseif($current_level): ?>
								<?php
								//if it's a one-time-payment level, offer a link to renew				
								if (!pmpro_isLevelRecurring($current_user->membership_level) && !empty($current_user->membership_level->enddate)): ?>
									<a class="w2dc-btn w2dc-btn-primary" href="<?php echo pmpro_url("checkout", "?level=" . $pmpro_level->id, "https")?>"><?php _e('Renew', 'W2DC');?></a>
								<?php else: ?>
									<a class="w2dc-btn w2dc-btn-primary w2dc-disabled" href="<?php echo pmpro_url("account")?>"><?php _e('Your Level', 'W2DC');?></a>
								<?php endif; ?>
							<?php endif; ?>
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
	</div>
</div>

<nav id="nav-below" class="navigation" role="navigation">
	<div class="nav-previous alignleft">
		<?php if(!empty($current_user->membership_level->ID)) { ?>
			<a href="<?php echo pmpro_url("account")?>"><?php _e('&larr; Return to Your Account', 'pmpro');?></a>
		<?php } else { ?>
			<a href="<?php echo home_url('/')?>"><?php _e('&larr; Return to Home', 'pmpro');?></a>
		<?php } ?>
	</div>
</nav>
