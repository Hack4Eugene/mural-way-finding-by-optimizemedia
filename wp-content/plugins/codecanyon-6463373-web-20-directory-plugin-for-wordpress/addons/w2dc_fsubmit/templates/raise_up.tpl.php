<h3>
	<?php echo apply_filters('w2dc_raiseup_option', sprintf(__('Raise up listing "%s"', 'W2DC'), $w2dc_instance->current_listing->title()), $w2dc_instance->current_listing); ?>
</h3>

<p><?php _e('Listing will be raised up to the top of all lists, those ordered by date.', 'W2DC'); ?></p>
<p><?php _e('Note, that listing will not stick on top, so new listings and other listings, those were raised up later, will place higher.', 'W2DC'); ?></p>

<?php do_action('w2dc_raise_up_html', $w2dc_instance->current_listing); ?>

<?php if ($frontend_controller->action == 'show'): ?>
<a href="<?php echo w2dc_dashboardUrl(array('w2dc_action' => 'raiseup_listing', 'listing_id' => $w2dc_instance->current_listing->post->ID, 'raiseup_action' => 'raiseup', 'referer' => urlencode($frontend_controller->referer))); ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Raise up', 'W2DC'); ?></a>
&nbsp;&nbsp;&nbsp;
<a href="<?php echo $frontend_controller->referer; ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Cancel', 'W2DC'); ?></a>
<?php elseif ($frontend_controller->action == 'raiseup'): ?>
<a href="<?php echo $frontend_controller->referer; ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Go back ', 'W2DC'); ?></a>
<?php endif; ?>