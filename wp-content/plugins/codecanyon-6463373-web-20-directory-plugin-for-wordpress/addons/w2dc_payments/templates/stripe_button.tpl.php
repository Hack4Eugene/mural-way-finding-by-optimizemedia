<?php if (extension_loaded('openssl')): ?>
<script src="https://checkout.stripe.com/checkout.js"></script>
<script>
	(function($) {
		"use strict";
	
		$(document).ready(function()  {
			var handler = StripeCheckout.configure({
				key: '<?php echo esc_js($stripe->publishable_key); ?>',
				token: function(token) {
					$('<form action="<?php echo w2dc_get_edit_invoice_link($invoice->post->ID); ?>&w2dc_gateway=stripe" method="POST">' + 
						'<input type="hidden" name="stripe_email" value="' + token.email + '">' +
						'<input type="hidden" name="stripe_token" value="' + token.id + '">' +
						'</form>').appendTo($(document.body)).submit();
				}
			});
			
			$('.stripe_button').click( function(e) {
				handler.open({
					name: '<?php echo esc_js(get_option('blogname')); ?>',
					description: '<?php echo esc_js($invoice->post->post_title); ?>',
					<?php $current_user = wp_get_current_user(); ?>
					email: '<?php echo esc_js($current_user->user_email); ?>',
					amount: <?php echo esc_js($invoice->taxesPrice(false)*100); ?>,
					currency: '<?php echo esc_js(get_option('w2dc_payments_currency')); ?>' 
				});
				e.preventDefault();
			});
		});
	})(jQuery);
</script>
<div class="w2dc-payment-method">
	<div class="w2dc-payment-gateway-icon">
		<a class="stripe_button" href="<?php echo w2dc_get_edit_invoice_link($invoice->post->ID); ?>&w2dc_gateway=stripe"><?php echo $stripe->buy_button(); ?></a>
	</div>
	<a class="stripe_button" href="<?php echo w2dc_get_edit_invoice_link($invoice->post->ID); ?>&w2dc_gateway=stripe"><?php echo $stripe->name(); ?></a>
	<p class="description"><?php echo $stripe->description(); ?></p>
</div>
<?php else: ?>
<div class="w2dc-payment-method">
	<div class="w2dc-payment-gateway-icon">
		<?php echo $stripe->buy_button(); ?>
	</div>
	<div><?php echo $stripe->name(); ?></div>
	<p class="description"><?php _e('Payment by Stripe requires installed OpenSSL extension!', 'W2DC'); ?></p>
</div>
<?php endif; ?>