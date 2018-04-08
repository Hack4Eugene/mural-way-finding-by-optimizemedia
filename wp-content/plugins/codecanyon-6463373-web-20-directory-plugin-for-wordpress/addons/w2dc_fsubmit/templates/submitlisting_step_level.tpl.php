<div class="w2dc-content">
	<?php w2dc_renderMessages(); ?>
	<?php if (isset($frontend_controller->args['show_steps']) && $frontend_controller->args['show_steps']): ?>
	<?php if ((count($w2dc_instance->levels->levels_array) > 1) || (get_option('w2dc_fsubmit_login_mode') == 1 && !is_user_logged_in())): ?>
	<div class="w2dc-submit-section-adv">
		<?php $step = 1; ?>

		<?php if (count($frontend_controller->levels) > 1): ?>
		<div class="w2dc-adv-step w2dc-adv-step-active">
			<div class="w2dc-adv-circle w2dc-adv-circle-active"><?php _e('Step', 'W2DC'); ?> <?php echo $step++; ?></div>
			<?php _e('Choose level', 'W2DC'); ?>
		</div>
		<div class="w2dc-adv-line w2dc-adv-line-active"></div>
		<?php endif; ?>

		<?php if (get_option('w2dc_fsubmit_login_mode') == 1 && !is_user_logged_in()): ?>
		<div class="w2dc-adv-step">
			<div class="w2dc-adv-circle"><?php _e('Step', 'W2DC'); ?> <?php echo $step++; ?></div>
			<?php _e('Login', 'W2DC'); ?>
		</div>
		<div class="w2dc-adv-line"></div>
		<?php endif; ?>
		
		<div class="w2dc-adv-step">
			<div class="w2dc-adv-circle"><?php _e('Step', 'W2DC'); ?> <?php echo $step++; ?></div>
			<?php _e('Create listing', 'W2DC'); ?>
		</div>
		
		<?php $step = apply_filters('w2dc_create_listings_steps_html', $step); ?>
		
		<div class="clear_float"></div>
	</div>
	<?php endif; ?>
	<?php endif; ?>
	
	<?php if (w2dc_is_woo_packages()): ?>
	<div class="w2dc-pull-left">
		<h3><?php printf(__("Submit one single %s", "W2DC"), $w2dc_instance->current_directory->single); ?></h3>
	</div>
	<div class="w2dc-submit-section-adv w2dc-pull-right">
		<a href="<?php echo w2dc_submitUrl(array('listings_packages' => 1)); ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Or select a Package &rarr;', 'W2DC'); ?></a>
	</div>
	<div class="clear_float"></div>
	<?php endif; ?>
	
	<div class="w2dc-submit-section-adv">
		<?php $max_columns_in_row = $frontend_controller->args['columns']; ?>
		<?php $levels_counter = count($frontend_controller->levels); ?>
		<?php if ($levels_counter > $max_columns_in_row) $levels_counter = $max_columns_in_row; ?>
		<?php $cols_width = floor(12/$levels_counter); ?>
		<?php $cols_width_percents = (100-1)/$levels_counter; ?>

		<?php $counter = 0; ?>
		<?php $tcounter = 0; ?>
		<?php foreach ($frontend_controller->levels AS $level): ?>
		<?php $tcounter++; ?>
		<?php if ($counter == 0): ?>
		<div class="w2dc-row" style="text-align: center;">
		<?php endif; ?>

			<div class="w2dc-col-sm-<?php echo $cols_width; ?> w2dc-plan-column" style="width: <?php echo $cols_width_percents; ?>%;">
				<div class="w2dc-panel w2dc-panel-default w2dc-text-center w2dc-choose-plan">
					<div class="w2dc-panel-heading <?php if ($level->featured): ?>w2dc-featured<?php endif; ?>">
						<h3>
							<?php echo $level->name; ?>
						</h3>
						<?php if ($level->description): ?><a class="w2dc-hint-icon" href="javascript:void(0);" data-content="<?php echo esc_attr(nl2br($level->description)); ?>" data-html="true" rel="popover" data-placement="bottom" data-trigger="hover"></a><?php endif; ?>
					</div>
					<ul class="w2dc-list-group">
						<?php do_action('w2dc_submitlisting_levels_rows', $level, '<li class="w2dc-list-group-item">', '</li>'); ?>
						<?php w2dc_renderTemplate(array(W2DC_FSUBMIT_TEMPLATES_PATH, 'level_details.tpl.php'), array('args' => $frontend_controller->args, 'level' => $level)); ?>
						<?php if ($w2dc_instance->submit_page_url): ?>
						<li class="w2dc-list-group-item">
							<a href="<?php echo w2dc_submitUrl(array('level' => $level->id)); ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Submit', 'W2DC'); ?></a>
						</li>
						<?php endif; ?>
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