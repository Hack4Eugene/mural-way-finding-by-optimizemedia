<?php

class w2dc_item_listing {
	public $item_id;
	
	public function __construct($item_id) {
		$this->item_id = $item_id;
	}
	
	public function getItem() {
		$listing_id = $this->item_id;

		// adapted for WPML
		global $sitepress;
		if (function_exists('wpml_object_id_filter') && $sitepress) {
			$listing_id = apply_filters('wpml_object_id', $listing_id, W2DC_POST_TYPE, true);
		}
		
		$listing = new w2dc_listing();
		if ($listing->loadListingFromPost($listing_id)) {
			return $listing;
		} else
			return false;
	}
	
	public function getItemLink() {
		if (($listing = $this->getItem()) && w2dc_current_user_can_edit_listing($listing->post->ID))
			if (current_user_can('edit_published_posts'))
				return '<a href="' . w2dc_get_edit_listing_link($listing->post->ID) . '">' . $listing->title() . '</a>';
			else 
				return $listing->title();
		else
			return __('N/A', 'W2DC');
	}
	
	public function getItemOptions() {
		if ($listing = $this->getItem())
			return array('active_days' => $listing->level->active_days, 'active_months' => $listing->level->active_months, 'active_years' => $listing->level->active_years);
		else
			return __('N/A', 'W2DC');
	}
	
	public function getItemOptionsString() {
		if ($listing = $this->getItem())
			return __('Active period - ', 'W2DC') . $listing->level->getActivePeriodString();
		else
			return __('N/A', 'W2DC');
	}

	public function complete() {
		if ($listing = $this->getItem()) {
			return $listing->processActivate(false);
		}
	}
}

function getInvoicesByListingId($listing_id) {
	$listing = new w2dc_listing();
	if (!$listing->loadListingFromPost($listing_id))
		return false;

	$children = get_children(array('post_parent' => $listing_id, 'post_type' => W2DC_INVOICE_TYPE, 'post_status' => 'publish'));

	$invoices = array();
	if (is_array($children) && count($children) > 0)
		foreach ($children as $child) {
		$invoice = new w2dc_invoice();
		$invoice->post = $child;
		$invoice->listing = $listing;
		$invoice->init();
		$invoices[] = $invoice;
	}
	return $invoices;
}

function getLastInvoiceByListingId($listing_id) {
	if ($invoices = getInvoicesByListingId($listing_id))
		return array_shift($invoices);
	else
		return false;
}

add_action('w2dc_listing_status_option', 'w2dc_pay_invoice_link');
function w2dc_pay_invoice_link($listing) {
	if ($listing->status == 'unpaid') {
		if (($invoice = getLastInvoiceByListingId($listing->post->ID)) && w2dc_current_user_can_edit_listing($invoice->post->ID) && current_user_can('edit_published_posts'))
			echo '<br /><a href="' . w2dc_get_edit_invoice_link($invoice->post->ID) . '" title="' . esc_attr($invoice->post->post_title) . '"><img src="' . W2DC_PAYMENTS_RESOURCES_URL . 'images/money_add.png' . '" class="w2dc-field-icon" />' . __('pay invoice', 'W2DC') . '</a>';
	}
}

add_filter('w2dc_listing_creation', 'w2dc_create_new_listing_invoice');
add_filter('w2dc_listing_creation_front', 'w2dc_create_new_listing_invoice');
function w2dc_create_new_listing_invoice($listing) {
	if ($listing) {
		if (recalcPrice($listing->level->price) > 0) {
			$invoice_args = array(
					'item' => 'listing',
					'title' => sprintf(__('Invoice for activation of listing: %s', 'W2DC'), $listing->title()),
					'is_subscription' => ($listing->level->eternal_active_period) ? false : true,
					'price' => $listing->level->price,
					'item_id' => $listing->post->ID,
					'author_id' => $listing->post->post_author
			);
			if ($invoice_id = call_user_func_array('w2dc_create_invoice', $invoice_args)) {
				w2dc_addMessage(__('New invoice was created successfully, listing become active after payment', 'W2DC'));
				update_post_meta($listing->post->ID, '_listing_status', 'unpaid');
	
				if (is_user_logged_in() && w2dc_current_user_can_edit_listing($invoice_id) && $edit_invoice_link = w2dc_get_edit_invoice_link($invoice_id, 'url')) {
					wp_redirect(apply_filters('redirect_post_location', $edit_invoice_link, $invoice_id));
					die();
				}
			}
		}
	}
	return $listing;
}

add_filter('w2dc_listing_renew', 'w2dc_renew_listing_invoice', 10, 2);
function w2dc_renew_listing_invoice($continue, $listing) {
	if (($invoice = getLastInvoiceByListingId($listing->post->ID)) && $invoice->status == 'unpaid') {
		if (is_user_logged_in() && w2dc_current_user_can_edit_listing($invoice->post->ID) && !is_admin()) {
			wp_redirect(apply_filters('redirect_post_location', w2dc_get_edit_invoice_link($invoice->post->ID, 'url'), $invoice->post->ID));
			die();
		}
		return false;
	} else {
		if (recalcPrice($listing->level->price) > 0) {
			$invoice_args = array(
					'item' => 'listing',
					'title' => sprintf(__('Invoice for renewal of listing: %s', 'W2DC'), $listing->title()),
					'is_subscription' => ($listing->level->eternal_active_period) ? false : true,
					'price' => $listing->level->price,
					'item_id' => $listing->post->ID,
					'author_id' => $listing->post->post_author
			);
			if (call_user_func_array('w2dc_create_invoice', $invoice_args)) {
				if (is_user_logged_in())
					w2dc_addMessage(__('New invoice was created successfully, listing become active after payment', 'W2DC'));

				if ($listing->status == 'expired')
					update_post_meta($listing->post->ID, '_listing_status', 'unpaid');

				return false;
			}
		} else
			return $continue;
	}
}

?>