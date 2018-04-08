<?php

class w2dc_item_listing_raiseup extends w2dc_item_listing {
	
	public function getItemOptions() {
		return false;
	}

	public function complete() {
		if ($listing = $this->getItem()) {
			return $listing->processRaiseUp(false);
		}
	}
}

function w2dc_create_raiseup_listing_invoice($continue, $listing) {
	if (recalcPrice($listing->level->raiseup_price) > 0) {
		if (!($invoice_id = get_post_meta($listing->post->ID, '_listing_raiseup_invoice', true))) {
			$invoice_args = array(
					'item' => 'listing_raiseup',
					'title' => sprintf(__('Invoice for raise up of listing: %s', 'W2DC'), $listing->title()),
					'is_subscription' => false,
					'price' => $listing->level->raiseup_price,
					'item_id' => $listing->post->ID,
					'author_id' => $listing->post->post_author
			);
			if (call_user_func_array('w2dc_create_invoice', $invoice_args)) {
				w2dc_addMessage(__('New invoice was created successfully, listing will be raised up after payment', 'W2DC'));
				update_post_meta($listing->post->ID, '_listing_raiseup_invoice', $invoice_id);
				return false;
			}
		}
	} else 
		return $continue;
}
add_filter('w2dc_listing_raiseup', 'w2dc_create_raiseup_listing_invoice', 10, 2);

?>