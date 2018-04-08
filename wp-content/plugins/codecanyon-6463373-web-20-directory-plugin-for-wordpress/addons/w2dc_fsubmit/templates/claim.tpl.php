<h3>
	<?php echo apply_filters('w2dc_claim_option', sprintf(__('Claim listing "%s"', 'W2DC'), $w2dc_instance->current_listing->title()), $w2dc_instance->current_listing); ?>
</h3>

<?php if ($frontend_controller->action == 'show'): ?>
<?php if (get_option('w2dc_claim_approval')): ?>
<p><?php _e('Notification about claim for this listing will be sent to the current listing owner.', 'W2DC'); ?></p>
<p><?php esc_attr_e("After approval you will become owner of this listing, you'll receive email notification.", 'W2DC'); ?></p>
<?php endif; ?>
<?php if (get_option('w2dc_after_claim') == 'expired'): ?>
<p><?php echo __('After approval listing status become expired.', 'W2DC') . ((get_option('w2dc_payments_addon')) ? apply_filters('w2dc_renew_option', __(' The price for renewal', 'W2DC'), $w2dc_instance->current_listing) : ''); ?></p>
<?php endif; ?>

<?php do_action('w2dc_claim_html', $w2dc_instance->current_listing); ?>

<form method="post" action="<?php echo w2dc_dashboardUrl(array('w2dc_action' => 'claim_listing', 'listing_id' => $w2dc_instance->current_listing->post->ID, 'claim_action' => 'claim')); ?>">
	<input type="hidden" name="referer" value="<?php echo $frontend_controller->referer; ?>" />
	<div class="w2dc-form-group w2dc-row">
		<div class="w2dc-col-md-12">
			<textarea name="claim_message" class="w2dc-form-control" rows="5"></textarea>
			<p class="description"><?php _e('additional information to moderator', 'W2DC'); ?></p>
		</div>
	</div>
	<input type="submit" class="w2dc-btn w2dc-btn-primary" value="<?php esc_attr_e('Send Claim', 'W2DC'); ?>"></input>
	&nbsp;&nbsp;&nbsp;
	<a href="<?php echo $frontend_controller->referer; ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Cancel', 'W2DC'); ?></a>
</form>
<?php elseif ($frontend_controller->action == 'claim'): ?>
<a href="<?php echo $frontend_controller->referer; ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Go back ', 'W2DC'); ?></a>
<?php endif; ?>