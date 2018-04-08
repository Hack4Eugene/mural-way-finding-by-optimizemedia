<?php if ((!get_option('w2dc_payments_addon') && !w2dc_is_woo_active()) && $args['show_period']): ?>
	<li class="w2dc-list-group-item">
		<?php echo $level->getActivePeriodString(); ?>
	</li>
<?php endif; ?>
<?php if ($args['show_sticky'] && ($args['columns_same_height'] || (!$args['columns_same_height'] && $level->sticky))): ?>
	<li class="w2dc-list-group-item">
		<?php _e('Sticky', 'W2DC'); ?>&nbsp;
		<?php if ($level->sticky): ?>
		<img src="<?php echo W2DC_RESOURCES_URL; ?>images/accept.png" />
		<?php else: ?>
		<img src="<?php echo W2DC_RESOURCES_URL; ?>images/delete.png" />
		<?php endif; ?>
	</li>
<?php endif; ?>
<?php if ($args['show_featured'] && ($args['columns_same_height'] || (!$args['columns_same_height'] && $level->featured))): ?>
	<li class="w2dc-list-group-item">
		<?php _e('Featured', 'W2DC'); ?>&nbsp;
		<?php if ($level->featured): ?>
		<img src="<?php echo W2DC_RESOURCES_URL; ?>images/accept.png" />
		<?php else: ?>
		<img src="<?php echo W2DC_RESOURCES_URL; ?>images/delete.png" />
		<?php endif; ?>
	</li>
<?php endif; ?>
<?php if ($args['show_categories'] && ($args['columns_same_height'] || (!$args['columns_same_height'] && ($level->categories_number || $level->unlimited_categories)))): ?>
	<li class="w2dc-list-group-item">
		<?php
		if (!$level->unlimited_categories)
			if ($level->categories_number == 1)
				_e('1 category', 'W2DC');
			elseif ($level->categories_number != 0)
				printf(__('Up to <strong>%d</strong> categories', 'W2DC'), $level->categories_number);
			else 
				_e('No categories', 'W2DC');
		else _e('Unlimited categories', 'W2DC'); ?>
	</li>
<?php endif; ?>
<?php if ($args['show_locations'] && ($args['columns_same_height'] || (!$args['columns_same_height'] && $level->locations_number))): ?>
	<li class="w2dc-list-group-item">
		<?php
		if ($level->locations_number == 1)
			_e('1 location', 'W2DC');
		elseif ($level->locations_number != 0)
			printf(__('Up to <strong>%d</strong> locations', 'W2DC'), $level->locations_number);
		else
			_e('No locations', 'W2DC'); ?>
	</li>
<?php endif; ?>
<?php if ($args['show_maps'] && ($args['columns_same_height'] || (!$args['columns_same_height'] && $level->google_map))): ?>
	<li class="w2dc-list-group-item">
		<?php _e('Google map', 'W2DC'); ?>&nbsp;
		<?php if ($level->google_map): ?>
		<img src="<?php echo W2DC_RESOURCES_URL; ?>images/accept.png" />
		<?php else: ?>
		<img src="<?php echo W2DC_RESOURCES_URL; ?>images/delete.png" />
		<?php endif; ?>
	</li>
<?php endif; ?>
<?php if ($args['show_images'] && ($args['columns_same_height'] || (!$args['columns_same_height'] && $level->images_number))): ?>
	<li class="w2dc-list-group-item">
		<?php
		if ($level->images_number == 1)
			_e('1 image', 'W2DC');
		elseif ($level->images_number != 0)
			printf(__('Up to <strong>%d</strong> images', 'W2DC'), $level->images_number);
		else
			_e('No images', 'W2DC'); ?>
	</li>
<?php endif; ?>
<?php if ($args['show_videos'] && ($args['columns_same_height'] || (!$args['columns_same_height'] && $level->videos_number))): ?>
	<li class="w2dc-list-group-item">
		<?php
		if ($level->videos_number == 1)
			_e('1 video', 'W2DC');
		elseif ($level->videos_number != 0)
			printf(__('Up to <strong>%d</strong> videos', 'W2DC'), $level->videos_number);
		else
			_e('No videos', 'W2DC'); ?>
	</li>
<?php endif; ?>