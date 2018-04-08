<h3>
	<?php echo sprintf(__('Delete listing "%s"', 'W2DC'), $w2dc_instance->current_listing->title()); ?>
</h3>

<p><?php _e('Listing will be completely deleted with all metadata, comments and attachments.', 'W2DC'); ?></p>

<?php do_action('w2dc_renew_html', $w2dc_instance->current_listing); ?>

<a href="<?php echo w2dc_dashboardUrl(array('w2dc_action' => 'delete_listing', 'listing_id' => $w2dc_instance->current_listing->post->ID, 'delete_action' => 'delete', 'referer' => urlencode($frontend_controller->referer))); ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Delete listing', 'W2DC'); ?></a>
&nbsp;&nbsp;&nbsp;
<a href="<?php echo $frontend_controller->referer; ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Cancel', 'W2DC'); ?></a>