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
	<label for="<?php echo $widget->get_field_id('depth'); ?>"><?php _e('Categories nesting level:', 'W2DC'); ?></label>
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
	<label for="<?php echo $widget->get_field_id('subcats'); ?>"><?php _e('Show subcategories items number:'); ?></label> 
	<input id="<?php echo $widget->get_field_id('subcats'); ?>" size="2" name="<?php echo $widget->get_field_name('subcats'); ?>" type="text" value="<?php echo esc_attr($instance['subcats']); ?>" />
	<p class="description"><?php _e('Leave 0 to show all subcategories', 'W2DC'); ?></p>
</p>
<p>
	<input id="<?php echo $widget->get_field_id('related_subcats'); ?>" name="<?php echo $widget->get_field_name('related_subcats'); ?>" type="checkbox" value="1" <?php checked($instance['related_subcats'], 1, true); ?> />
	<label for="<?php echo $widget->get_field_id('related_subcats'); ?>"><?php _e('Show related subcategories on categories pages', 'W2DC'); ?></label>
	<p class="description"><?php _e('On categories pages users will see subcategories of current category', 'W2DC'); ?></p> 
</p>
<p>
	<label for="<?php echo $widget->get_field_id('parent'); ?>"><?php _e('Parent category:'); ?></label> 
	<input id="<?php echo $widget->get_field_id('parent'); ?>" size="2" name="<?php echo $widget->get_field_name('parent'); ?>" type="text" value="<?php echo esc_attr($instance['parent']); ?>" />
	<p class="description"><?php _e('Leave 0 to show all root categories', 'W2DC'); ?></p>
</p>
<p>
	<input id="<?php echo $widget->get_field_name('visibility'); ?>" name="<?php echo $widget->get_field_name('visibility'); ?>" type="checkbox" value="1" <?php checked($instance['visibility'], 1, true); ?> />
	<label for="<?php echo $widget->get_field_id('visibility'); ?>"><?php _e('Show only on directory pages', 'W2DC'); ?></label> 
</p>