		<div class="w2dc-directory-frontpanel">
			<script>
				var window_width = 860;
				var window_height = 800;
				var leftPosition, topPosition;
				(function($) {
					"use strict";

					leftPosition = (window.screen.width / 2) - ((window_width / 2) + 10);
					topPosition = (window.screen.height / 2) - ((window_height / 2) + 50);
				})(jQuery);
			</script>
			<input type="button" class="w2dc-print-listing-link w2dc-btn w2dc-btn-primary" onclick="window.open('<?php echo esc_url(add_query_arg('invoice_id', $frontend_controller->invoice->post->ID, w2dc_directoryUrl(array('w2dc_action' => 'w2dc_print_invoice')))); ?>', 'print_window', 'height='+window_height+',width='+window_width+',left='+leftPosition+',top='+topPosition+',menubar=yes,scrollbars=yes');" value="<?php esc_attr_e('Print invoice', 'W2DC'); ?>" />
			
			<?php if ($frontend_controller->invoice->gateway): ?>
			<input type="button" class="w2dc-reset-link w2dc-btn w2dc-btn-primary" onclick="window.location='<?php echo esc_url(add_query_arg('invoice_action', 'reset_gateway', w2dc_get_edit_invoice_link($frontend_controller->invoice->post->ID))); ?>';" value="<?php esc_attr_e('Reset gateway', 'W2DC'); ?>" />
			<?php endif; ?>
		</div>
		
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Invoice Info', 'W2DC'); ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php w2dc_renderTemplate(array(W2DC_PAYMENTS_TEMPLATES_PATH, 'info_metabox.tpl.php'), array('invoice' => $frontend_controller->invoice)); ?>
			</div>
		</div>

		<?php if ((get_option('w2dc_paypal_email') || get_option('w2dc_allow_bank') || ((get_option('w2dc_stripe_test') && get_option('w2dc_stripe_test_secret') && get_option('w2dc_stripe_test_public')) || (get_option('w2dc_stripe_live_secret') && get_option('w2dc_stripe_live_public')))) && $frontend_controller->invoice->status == 'unpaid' && !$frontend_controller->invoice->gateway): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Invoice Payment - choose payment gateway', 'W2DC'); ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php w2dc_renderTemplate(array(W2DC_PAYMENTS_TEMPLATES_PATH, 'payment_metabox.tpl.php'), array('invoice' => $frontend_controller->invoice, 'paypal' => $frontend_controller->paypal, 'paypal_subscription' => $frontend_controller->paypal_subscription, 'bank_transfer' => $frontend_controller->bank_transfer, 'stripe' => $frontend_controller->stripe)); ?>
			</div>
		</div>
		<?php endif; ?>

		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Invoice Log', 'W2DC'); ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php w2dc_renderTemplate(array(W2DC_PAYMENTS_TEMPLATES_PATH, 'log_metabox.tpl.php'), array('invoice' => $frontend_controller->invoice)); ?>
			</div>
		</div>

		<a href="<?php echo w2dc_dashboardUrl(array('w2dc_action' => 'invoices')); ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('View all invoices', 'W2DC'); ?></a>