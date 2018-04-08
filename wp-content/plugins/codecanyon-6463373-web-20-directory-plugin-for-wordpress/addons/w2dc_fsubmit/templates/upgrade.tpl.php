<h3>
	<?php echo sprintf(__('Change level of listing "%s"', 'W2DC'), $w2dc_instance->current_listing->title()); ?>
</h3>

<p><?php _e('The level of listing will be changed. You may upgrade or downgrade the level. If new level has an option of limited active period - expiration date of listing will be recalculated automatically.', 'W2DC'); ?></p>

<form action="<?php echo w2dc_dashboardUrl(array('w2dc_action' => 'upgrade_listing', 'listing_id' => $w2dc_instance->current_listing->post->ID, 'upgrade_action' => 'upgrade', 'referer' => urlencode($frontend_controller->referer))); ?>" method="POST">
	<?php if ($frontend_controller->action == 'show'): ?>
	<h3><?php _e('Choose new level', 'W2DC'); ?></h3>
	<?php foreach ($w2dc_instance->levels->levels_array AS $level): ?>
	<?php if ($w2dc_instance->current_listing->level->id != $level->id && (!isset($w2dc_instance->current_listing->level->upgrade_meta[$level->id]) || !$w2dc_instance->current_listing->level->upgrade_meta[$level->id]['disabled'])): ?>
	<p>
		<label><input type="radio" name="new_level_id" value="<?php echo $level->id; ?>" /> <?php echo apply_filters('w2dc_level_upgrade_option', $level->name, $w2dc_instance->current_listing->level, $level); ?></label>
	</p>
	<?php endif; ?>
	<?php endforeach; ?>
	
	<br />
	<br />
	<input type="submit" value="<?php esc_attr_e('Change level', 'W2DC'); ?>" class="w2dc-btn w2dc-btn-primary" id="submit" name="submit">
	&nbsp;&nbsp;&nbsp;
	<a href="<?php echo $frontend_controller->referer; ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Cancel', 'W2DC'); ?></a>
	<?php elseif ($frontend_controller->action == 'upgrade'): ?>
	<a href="<?php echo $frontend_controller->referer; ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Go back ', 'W2DC'); ?></a>
	<?php endif; ?>
</form>