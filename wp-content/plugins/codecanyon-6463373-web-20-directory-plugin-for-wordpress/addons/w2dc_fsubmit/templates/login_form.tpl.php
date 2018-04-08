<div class="w2dc-content">
	<?php w2dc_renderMessages(); ?>

	<?php if (isset($_GET['level']) && ($level = $w2dc_instance->levels->getLevelById($_GET['level']))): ?>
	<?php if (count($w2dc_instance->levels->levels_array) > 1): ?>
	<h2><?php echo sprintf(__('Create new %s in level "%s"', 'W2DC'), $w2dc_instance->current_directory->single, $level->name); ?></h2>
	<?php endif; ?>

	<?php if ($frontend_controller->args['show_steps']): ?>
	<?php if ((count($w2dc_instance->levels->levels_array) > 1) || (get_option('w2dc_fsubmit_login_mode') == 1 && !is_user_logged_in())): ?>
	<div class="w2dc-submit-section-adv">
		<?php $step = 1; ?>

		<?php if (count($w2dc_instance->levels->levels_array) > 1): ?>
		<div class="w2dc-adv-step">
			<div class="w2dc-adv-circle w2dc-adv-circle-passed"><?php _e('Step', 'W2DC'); ?> <?php echo $step++; ?></div>
			<?php _e('Choose level', 'W2DC'); ?>
		</div>
		<div class="w2dc-adv-line w2dc-adv-line-passed"></div>
		<?php endif; ?>

		<?php if (get_option('w2dc_fsubmit_login_mode') == 1 && !is_user_logged_in()): ?>
		<div class="w2dc-adv-step w2dc-adv-step-active">
			<div class="w2dc-adv-circle w2dc-adv-circle-active"><?php _e('Step', 'W2DC'); ?> <?php echo $step++; ?></div>
			<?php _e('Login', 'W2DC'); ?>
		</div>
		<div class="w2dc-adv-line"></div>
		<?php endif; ?>

		<div class="w2dc-adv-step">
			<div class="w2dc-adv-circle"><?php _e('Step', 'W2DC'); ?> <?php echo $step++; ?></div>
			<?php _e('Create listing', 'W2DC'); ?>
		</div>
		
		<?php $step = apply_filters('w2dc_create_listings_steps_html', $step, $level); ?>

		<div class="clear_float"></div>
	</div>
	<?php endif; ?>
	<?php endif; ?>
	<?php endif; ?>

	<div class="w2dc-submit-section-adv">
		<?php w2dc_login_form(); ?>
	</div>
</div>