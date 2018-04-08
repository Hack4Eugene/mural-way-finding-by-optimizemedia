<?php global $wp_rewrite; ?>
<?php if (get_option('w2dc_paypal_email') && $wp_rewrite->using_permalinks()): ?>
<?php if (get_option('w2dc_paypal_single')): ?>
<div class="w2dc-payment-method">
	<div class="w2dc-payment-gateway-icon">
		<a href="<?php echo w2dc_get_edit_invoice_link($invoice->post->ID); ?>&w2dc_gateway=paypal"><?php echo $paypal->buy_button(); ?></a>
	</div>
	<a href="<?php echo w2dc_get_edit_invoice_link($invoice->post->ID); ?>&w2dc_gateway=paypal"><?php echo $paypal->name(); ?></a>
	<p class="description"><?php echo $paypal->description(); ?></p>
</div>
<?php endif; ?>
<?php if (get_option('w2dc_paypal_subscriptions') && $invoice->is_subscription): ?>
<div class="w2dc-payment-method">
	<div class="w2dc-payment-gateway-icon">
		<a href="<?php echo w2dc_get_edit_invoice_link($invoice->post->ID); ?>&w2dc_gateway=paypal_subscription"><?php echo $paypal_subscription->buy_button(); ?></a>
	</div>
	<a href="<?php echo w2dc_get_edit_invoice_link($invoice->post->ID); ?>&w2dc_gateway=paypal_subscription"><?php echo $paypal_subscription->name(); ?></a>
	<p class="description"><?php echo $paypal_subscription->description(); ?></p>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if ((get_option('w2dc_stripe_test') && get_option('w2dc_stripe_test_secret') && get_option('w2dc_stripe_test_public')) || (get_option('w2dc_stripe_live_secret') && get_option('w2dc_stripe_live_public'))): ?>
<?php w2dc_renderTemplate(array(W2DC_PAYMENTS_PATH, 'templates/stripe_button.tpl.php'), array('stripe' => $stripe, 'invoice' => $invoice)); ?>
<?php endif; ?>

<?php if (get_option('w2dc_allow_bank')): ?>
<div class="w2dc-payment-method">
	<div class="w2dc-payment-gateway-icon">
		<a href="<?php echo w2dc_get_edit_invoice_link($invoice->post->ID); ?>&w2dc_gateway=bank_transfer"><?php echo $bank_transfer->buy_button(); ?></a>
	</div>
	<a href="<?php echo w2dc_get_edit_invoice_link($invoice->post->ID); ?>&w2dc_gateway=bank_transfer"><?php echo $bank_transfer->name(); ?></a>
	<p class="description"><?php echo $bank_transfer->description(); ?></p>
</div>
<?php endif; ?>
<div class="clear_float"></div>