<?php

class w2dc_item_listing_upgrade extends w2dc_item_listing {
	
	public function getItemOptions() {
		global $w2dc_instance;

		if ($listing = $this->getItem()) {
			$old_level_id = get_post_meta($listing->post->ID, '_old_level_id', true);
			$old_level = $w2dc_instance->levels->levels_array[$old_level_id];

			$new_level_id = get_post_meta($listing->post->ID, '_new_level_id', true);
			$new_level = $w2dc_instance->levels->levels_array[$new_level_id];
			
			return sprintf(__("From '%s' to '%s' level", 'W2DC'), $old_level->name, $new_level->name);
		} else
			return __("N/A", 'W2DC');
	}
	
	public function getItemOptionsString() {
		global $w2dc_instance;

		if ($listing = $this->getItem()) {
			$old_level_id = get_post_meta($listing->post->ID, '_old_level_id', true);
			$old_level = $w2dc_instance->levels->levels_array[$old_level_id];

			$new_level_id = get_post_meta($listing->post->ID, '_new_level_id', true);
			$new_level = $w2dc_instance->levels->levels_array[$new_level_id];
			
			return sprintf(__("From '%s' to '%s' level", 'W2DC'), $old_level->name, $new_level->name);
		} else
			return __("N/A", 'W2DC');
	}

	public function complete() {
		if ($listing = $this->getItem()) {
			delete_post_meta($listing->post->ID, '_listing_upgrade_invoice');
			$new_level_id = get_post_meta($listing->post->ID, '_new_level_id', true);
			return $listing->changeLevel($new_level_id, false);
		}
	}
}

function w2dc_delete_invoice_meta($post_id) {
	if (get_post_type($post_id) == W2DC_INVOICE_TYPE) {
		delete_post_meta($post_id, '_listing_upgrade_invoice');
	}
}
add_action('delete_post', 'w2dc_delete_invoice_meta');

function w2dc_create_upgrade_listing_invoice($continue, $listing) {
	$new_level_id = get_post_meta($listing->post->ID, '_new_level_id', true);
	if (recalcPrice($listing->level->upgrade_meta[$new_level_id]['price']) > 0) {
		if (!($invoice_id = get_post_meta($listing->post->ID, '_listing_upgrade_invoice', true)) || !($invoice = getInvoiceByID($invoice_id))) {
			$invoice_args = array(
					'item' => 'listing_upgrade',
					'title' => sprintf(__('Invoice for upgrade of listing: %s', 'W2DC'), $listing->title()),
					'is_subscription' => false,
					'price' => $listing->level->upgrade_meta[$new_level_id]['price'],
					'item_id' => $listing->post->ID,
					'author_id' => $listing->post->post_author
			);
			if ($invoice_id = call_user_func_array('w2dc_create_invoice', $invoice_args)) {
				w2dc_addMessage(sprintf(__('New invoice was created successfully, listing "%s" will be upgraded after payment', 'W2DC'), $listing->title()));
				update_post_meta($listing->post->ID, '_listing_upgrade_invoice', $invoice_id);
				return false;
			}
		} else {
			$invoice->setPrice($listing->level->upgrade_meta[$new_level_id]['price']);
		}
	} else {
		// When there is already existed invoice, but new level will be free - just remove this invoice
		if ($invoice_id = get_post_meta($listing->post->ID, '_listing_upgrade_invoice', true))
			wp_delete_post($invoice_id);

		return $continue;
	}
}
add_filter('w2dc_listing_upgrade', 'w2dc_create_upgrade_listing_invoice', 10, 2);

?>