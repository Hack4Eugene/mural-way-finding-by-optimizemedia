<h3>
	<?php echo apply_filters('w2dc_renew_option', sprintf(__('Renew listing "%s"', 'W2DC'), $w2dc_instance->current_listing->title()), $w2dc_instance->current_listing); ?>
</h3>

<p><?php _e('Listing will be renewed and raised up to the top of all lists, those ordered by date.', 'W2DC'); ?></p>

<?php do_action('w2dc_renew_html', $w2dc_instance->current_listing); ?>

<?php if ($frontend_controller->action == 'show'): ?>
<a href="<?php echo w2dc_dashboardUrl(array('w2dc_action' => 'renew_listing', 'listing_id' => $w2dc_instance->current_listing->post->ID, 'renew_action' => 'renew', 'referer' => urlencode($frontend_controller->referer))); ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Renew listing', 'W2DC'); ?></a>
&nbsp;&nbsp;&nbsp;
<a href="<?php echo $frontend_controller->referer; ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Cancel', 'W2DC'); ?></a>
<?php elseif ($frontend_controller->action == 'renew'): ?>
<a href="<?php echo $frontend_controller->referer; ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Go back ', 'W2DC'); ?></a>
<?php endif; ?>