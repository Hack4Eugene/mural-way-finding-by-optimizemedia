<p>
	<label for="<?php echo $widget->get_field_id('title'); ?>"><?php _e('Title:', 'W2DC'); ?></label> 
	<input class="widefat" id="<?php echo $widget->get_field_id('title'); ?>" name="<?php echo $widget->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
</p>
<?php if ($w2dc_instance->directories->isMultiDirectory()): ?>
<p>
	<label for="<?php echo $widget->get_field_id('directory'); ?>"><?php _e('Search by directory:', 'W2DC'); ?></label>
	<select id="<?php echo $widget->get_field_id('directory'); ?>" name="<?php echo $widget->get_field_name('directory'); ?>">
		<option value="0" <?php selected($instance['directory'], 0); ?>><?php _e('- auto -', 'W2DC'); ?></option>
		<?php foreach ($w2dc_instance->directories->directories_array AS $directory): ?>
		<option value="<?php echo $directory->id; ?>" <?php selected($instance['directory'], $directory->id); ?>><?php echo $directory->name; ?></option>
		<?php endforeach; ?>
	</select>
</p>
<?php endif; ?>
<p>
	<label for="<?php echo $widget->get_field_id('uid'); ?>"><?php _e('uID:', 'W2DC'); ?></label> 
	<input class="widefat" id="<?php echo $widget->get_field_id('uid'); ?>" name="<?php echo $widget->get_field_name('uid'); ?>" type="text" value="<?php echo esc_attr($instance['uid']); ?>" /><?php _e('Enter unique string to connect search form with another elements on the page.', 'W2DC'); ?>
</p>
<p>
	<input id="<?php echo $widget->get_field_name('search_visibility'); ?>" name="<?php echo $widget->get_field_name('search_visibility'); ?>" type="checkbox" value="1" <?php checked($instance['search_visibility'], 1, true); ?> />
	<label for="<?php echo $widget->get_field_id('search_visibility'); ?>"><?php _e('Show only when there is no any other search form on page', 'W2DC'); ?></label> 
</p>
<p>
	<input id="<?php echo $widget->get_field_name('visibility'); ?>" name="<?php echo $widget->get_field_name('visibility'); ?>" type="checkbox" value="1" <?php checked($instance['visibility'], 1, true); ?> />
	<label for="<?php echo $widget->get_field_id('visibility'); ?>"><?php _e('Show only on directory pages', 'W2DC'); ?></label> 
</p>