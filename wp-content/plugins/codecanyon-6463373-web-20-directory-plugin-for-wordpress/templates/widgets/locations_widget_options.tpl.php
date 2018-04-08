<p>
	<label for="<?php echo $widget->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
	<input class="widefat" id="<?php echo $widget->get_field_id('title'); ?>" name="<?php echo $widget->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
</p>
<?php if ($w2dc_instance->directories->isMultiDirectory()): ?>
<p>
	<label for="<?php echo $widget->get_field_id('directory'); ?>"><?php _e('Directory:', 'W2DC'); ?></label>
	<select id="<?php echo $widget->get_field_id('directory'); ?>" name="<?php echo $widget->get_field_name('directory'); ?>">
		<option value="0" <?php selected($instance['directory'], 0); ?>><?php _e('- auto -', 'W2DC'); ?></option>
		<?php foreach ($w2dc_instance->directories->directories_array AS $directory): ?>
		<option value="<?php echo $directory->id; ?>" <?php selected($instance['directory'], $directory->id); ?>><?php echo $directory->name; ?></option>
		<?php endforeach; ?>
	</select>
</p>
<?php endif; ?>
<p>
	<label for="<?php echo $widget->get_field_id('depth'); ?>"><?php _e('Locations nesting level:', 'W2DC'); ?></label>
	<select id="<?php echo $widget->get_field_id('depth'); ?>" name="<?php echo $widget->get_field_name('depth'); ?>">
		<option value=1 <?php selected($instance['depth'], 1); ?>>1</option>
		<option value=2 <?php selected($instance['depth'], 2); ?>>2</option>
	</select>
</p>
<p>
	<input id="<?php echo $widget->get_field_id('counter'); ?>" name="<?php echo $widget->get_field_name('counter'); ?>" type="checkbox" value="1" <?php checked($instance['counter'], 1, true); ?> />
	<label for="<?php echo $widget->get_field_id('counter'); ?>"><?php _e('Show listings counts', 'W2DC'); ?></label> 
</p>
<p>
	<label for="<?php echo $widget->get_field_id('sublocations'); ?>"><?php _e('Show sublocations items number:'); ?></label> 
	<input id="<?php echo $widget->get_field_id('sublocations'); ?>" size="2" name="<?php echo $widget->get_field_name('sublocations'); ?>" type="text" value="<?php echo esc_attr($instance['sublocations']); ?>" />
	<p class="description"><?php _e('Leave 0 to show all sublocations', 'W2DC'); ?></p>
</p>
<p>
	<input id="<?php echo $widget->get_field_id('related_sublocations'); ?>" name="<?php echo $widget->get_field_name('related_sublocations'); ?>" type="checkbox" value="1" <?php checked($instance['related_sublocations'], 1, true); ?> />
	<label for="<?php echo $widget->get_field_id('related_sublocations'); ?>"><?php _e('Show related sublocations on locations pages', 'W2DC'); ?></label>
	<p class="description"><?php _e('On locations pages users will see sublocations of current location', 'W2DC'); ?></p> 
</p>
<p>
	<label for="<?php echo $widget->get_field_id('parent'); ?>"><?php _e('Parent location:'); ?></label> 
	<input id="<?php echo $widget->get_field_id('parent'); ?>" size="2" name="<?php echo $widget->get_field_name('parent'); ?>" type="text" value="<?php echo esc_attr($instance['parent']); ?>" />
	<p class="description"><?php _e('Leave 0 to show all root locations', 'W2DC'); ?></p>
</p>
<p>
	<input id="<?php echo $widget->get_field_name('visibility'); ?>" name="<?php echo $widget->get_field_name('visibility'); ?>" type="checkbox" value="1" <?php checked($instance['visibility'], 1, true); ?> />
	<label for="<?php echo $widget->get_field_id('visibility'); ?>"><?php _e('Show only on directory pages', 'W2DC'); ?></label> 
</p>