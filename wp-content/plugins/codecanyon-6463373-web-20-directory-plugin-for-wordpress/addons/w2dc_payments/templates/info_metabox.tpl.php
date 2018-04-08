<div id="misc-publishing-actions">
	<?php if (get_option('w2dc_enable_taxes')): ?>
	<div class="misc-pub-section">
		<span>
			<?php echo nl2br(get_option('w2dc_taxes_info')); ?>
		</span>
	</div>
	<?php endif; ?>
	<div class="misc-pub-section">
		<span>
			<b><?php echo $invoice->post->post_title; ?></b>
		</span>
	</div>
	<div class="misc-pub-section">
		<label><?php _e('Invoice ID', 'W2DC'); ?>:</label>
		<span>
			<b><?php echo $invoice->post->ID; ?></b>
		</span>
	</div>
	<?php if ($invoice->post->post_author != get_current_user_id()): ?>
	<div class="misc-pub-section">
		<label><?php _e('Author', 'W2DC'); ?>:</label>
		<span>
			<a href="<?php echo get_edit_user_link($invoice->post->post_author); ?>"><?php echo get_userdata($invoice->post->post_author)->user_login; ?></a>
		</span>
	</div>
	<?php endif; ?>
	<?php if ($billing_info = $invoice->billingInfo()): ?>
	<div class="misc-pub-section">
		<label><?php _e('Bill To', 'W2DC'); ?>:</label>
		<span>
			<?php echo $billing_info; ?>
		</span>
	</div>
	<?php endif; ?>
	<div class="misc-pub-section">
		<label><?php _e('Item', 'W2DC'); ?>:</label>
		<span>
			<?php if (is_object($invoice->item_object)) echo $invoice->item_object->getItemLink(); ?>
		</span>
	</div>
	<?php if ($invoice->item_object->getItemOptions()): ?>
	<div class="misc-pub-section">
		<label><?php _e('Item options', 'W2DC'); ?>:</label>
		<span>
			<b><?php echo $invoice->item_object->getItemOptionsString(); ?></b>
		</span>
	</div>
	<?php endif; ?>
	<div class="misc-pub-section">
		<label><?php _e('Price', 'W2DC'); ?>:</label>
		<span>
			<b><?php echo $invoice->price(); ?></b> <?php echo $invoice->taxesString(); ?>
		</span>
	</div>
	<?php if (get_option('w2dc_enable_taxes') && get_option('w2dc_tax_name')): ?>
	<div class="misc-pub-section">
		<label><?php echo get_option('w2dc_tax_name'); ?>:</label>
		<span>
			<b><?php echo $invoice->taxesAmount(); ?></b>
		</span>
	</div>
	<div class="misc-pub-section">
		<label><?php _e('Total', 'W2DC'); ?>:</label>
		<span>
			<b><?php echo $invoice->taxesPrice(); ?></b>
		</span>
	</div>
	<?php endif; ?>
	<div class="misc-pub-section">
		<label><?php _e('Status', 'W2DC'); ?>:</label>
		<span>
			<?php if ($invoice->status == 'unpaid')
				echo '<span class="w2dc-badge w2dc-invoice-status-unpaid">' . __('unpaid', 'W2DC') . '</span>';
			elseif ($invoice->status == 'paid')
				echo '<span class="w2dc-badge w2dc-invoice-status-paid">' . __('paid', 'W2DC') . '</span>';
			elseif ($invoice->status == 'pending')
				echo '<span class="w2dc-badge w2dc-invoice-status-pending">' . __('pending', 'W2DC') . '</span>';
			?>
		</span>
	</div>
	<?php if ($invoice->gateway): ?>
	<div class="misc-pub-section">
		<label><?php _e('Gateway', 'W2DC'); ?>:</label>
		<span>
			<b><?php echo gatewayName($invoice->gateway); ?></b>
		</span>
	</div>
	<?php endif; ?>
</div>