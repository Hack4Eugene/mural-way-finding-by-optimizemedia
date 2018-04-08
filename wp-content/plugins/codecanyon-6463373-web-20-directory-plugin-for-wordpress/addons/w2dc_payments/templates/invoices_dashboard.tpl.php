	<?php if ($frontend_controller->invoices): ?>
		<table class="w2dc-table w2dc-table-striped">
			<tr>
				<th class="td_invoices_title"><?php _e('Invoice', 'W2DC'); ?></th>
				<th class="td_invoices_item"><?php _e('Item', 'W2DC'); ?></th>
				<th class="td_invoices_price"><?php _e('Price', 'W2DC'); ?></th>
				<th class="td_invoices_payment"><?php _e('Payment', 'W2DC'); ?></th>
				<th class="td_invoices_date"><?php _e('Creation date', 'W2DC'); ?></th>
			</tr>
		<?php while ($frontend_controller->invoices_query->have_posts()): ?>
			<?php $frontend_controller->invoices_query->the_post(); ?>
			<?php $invoice = $frontend_controller->invoices[get_the_ID()]; ?>
			<tr>
				<td class="td_invoices_title">
					<?php
					if (w2dc_current_user_can_edit_listing($invoice->post->ID))
						echo '<a href="' . w2dc_get_edit_invoice_link($invoice->post->ID) . '">' . $invoice->post->post_title . '</a>';
					else
						echo $invoice->post->post_title;
					?>
				</td>
				<td class="td_invoices_item"><?php if (is_object($invoice->item_object)) echo $invoice->item_object->getItemLink(); ?></td>
				<td class="td_invoices_price"><?php echo $invoice->price(); ?></td>
				<td class="td_invoices_payment">
					<?php
					if ($invoice->status == 'unpaid') {
						echo '<span class="w2dc-badge w2dc-invoice-status-unpaid">' . __('unpaid', 'W2DC') . '</span>';
						if (w2dc_current_user_can_edit_listing($invoice->post->ID))
							echo '<br /><a href="' . w2dc_get_edit_invoice_link($invoice->post->ID) . '"><img src="' . W2DC_PAYMENTS_RESOURCES_URL . 'images/money_add.png' . '" class="w2dc-field-icon" />' . __('pay invoice', 'W2DC') . '</a>';
					} elseif ($invoice->status == 'paid') {
						echo '<span class="w2dc-badge w2dc-invoice-status-paid">' . __('paid', 'W2DC') . '</span>';
						if ($invoice->gateway)
							echo '<br /><b>' . gatewayName($invoice->gateway) . '</b>';
					} elseif ($invoice->status == 'pending') {
						echo '<span class="w2dc-badge w2dc-invoice-status-pending">' . __('pending', 'W2DC') . '</span>';
						if ($invoice->gateway)
							echo '<br /><b>' . gatewayName($invoice->gateway) . '</b>';
					}
					?>
				</td>
				<td class="td_invoices_date"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($invoice->post->post_date)); ?></td>
			</tr>
		<?php endwhile; ?>
		</table>
		<?php w2dc_renderPaginator($frontend_controller->invoices_query, '', false); ?>
	<?php endif; ?>