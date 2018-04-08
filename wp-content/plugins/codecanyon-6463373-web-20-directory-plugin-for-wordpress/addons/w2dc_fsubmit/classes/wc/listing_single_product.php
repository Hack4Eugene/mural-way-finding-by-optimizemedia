<?php

add_action('init', 'w2dc_register_listing_single_product_type');
function w2dc_register_listing_single_product_type() {
	class WC_Product_Listing_Single extends WC_Product {

		public $level_id = null;
		public $raiseup_price = 0;

		public function __construct($product) {
			
			$this->product_type = 'listing_single';

			parent::__construct($product);

			if (get_post_meta($this->id, '_listings_level', true))
				$this->level_id = get_post_meta($this->id, '_listings_level', true);

			if (get_post_meta($this->id, '_raiseup_price', true))
				$this->raiseup_price = get_post_meta($this->id, '_raiseup_price', true);
		}
		
		public function get_virtual($context = 'view') {
			return true;
		}
		
		public function get_downloadable($context = 'view') {
			return true;
		}
	}
}

class w2dc_listing_single_product {
	
	public function __construct() {
		add_filter('product_type_selector', array($this, 'add_listing_single_product'));
		add_action('admin_footer', array($this, 'listing_single_custom_js'));
		add_filter('woocommerce_product_data_tabs', array($this, 'hide_attributes_data_panel'));
		add_action('woocommerce_product_options_pricing', array($this, 'add_raiseup_price'));
		add_filter('woocommerce_product_data_panels', array($this, 'new_product_tab_content'));
		add_action('woocommerce_process_product_meta_listing_single', array($this, 'save_listing_single_tab_content'));
		
		add_filter('w2dc_create_option', array($this, 'create_price'), 10, 2);
		add_filter('w2dc_raiseup_option', array($this, 'raiseup_price'), 10, 2);
		add_filter('w2dc_renew_option', array($this, 'renew_price'), 10, 2);
		add_filter('w2dc_level_upgrade_option', array($this, 'upgrade_price'), 10, 3);

		add_action('w2dc_submitlisting_levels_th', array($this, 'levels_price_front_table_header'), 10, 2);
		add_action('w2dc_submitlisting_levels_rows', array($this, 'levels_price_front_table_row'), 10, 3);
		
		add_filter('w2dc_level_table_header', array($this, 'levels_price_table_header'));
		add_filter('w2dc_level_table_row', array($this, 'levels_price_table_row'), 10, 2);
		
		// Woocommerce Dashboard - Order Details
		add_action('woocommerce_order_item_meta_start', array($this, 'listing_in_order_table'), 10, 3);
		add_action('woocommerce_after_order_itemmeta', array($this, 'listing_in_order_table'), 10, 3);
		// Woocommerce Checkout
		add_filter('woocommerce_get_item_data', array($this, 'listing_in_checkout'), 10, 2);
		add_action('woocommerce_before_calculate_totals', array($this, 'checkout_listing_raiseup_price'));
		// Woocommerce add order item meta
		add_action('woocommerce_new_order_item', array($this, 'add_order_item_meta'), 10, 3);
		// when guest user creates new profile after he created a listing
		add_filter('woocommerce_new_customer_data', array($this, 'update_user_info'));
		// when guest user logs in after he created a listing
		add_filter('woocommerce_checkout_customer_id', array($this, 'reassign_user'));
		
		add_filter('w2dc_listing_creation_front', array($this, 'create_activation_order'));
		add_filter('w2dc_listing_renew', array($this, 'renew_listing_order'), 10, 3);
		add_filter('w2dc_listing_raiseup', array($this, 'listing_raiseup_order'), 10, 3);
		add_filter('w2dc_listing_upgrade', array($this, 'listing_upgrade_order'), 10, 3);
		
		add_filter('woocommerce_payment_complete_order_status', array($this, 'complete_payment'), 10, 2);
		add_action('woocommerce_order_status_completed', array($this, 'complete_status'), 10);
	}

	public function add_listing_single_product($types){
		$types['listing_single'] = __('Listing Single', 'W2DC');
	
		return $types;
	}
	
	public function listing_single_custom_js() {
		if ('product' != get_post_type())
			return;
	
		?><script type='text/javascript'>
				jQuery(document).ready( function($) {
					$('.options_group.pricing').addClass('show_if_listing_single').show();
					$('.options_group.show_if_downloadable').addClass('hide_if_listing_single').hide();
					$('.general_tab').addClass('active').show();
					$('.listings_tab').removeClass('active');
					$('#general_product_data').show();
					$('#listing_single_product_data').hide();
					$('._tax_status_field').parent().addClass('show_if_listing_single');
					if ($('#product-type option:selected').val() == 'listing_single') {
						$('.show_if_listing_single').show();
						$('.hide_if_listing_single').hide();
					}
				});
			</script><?php
	}
	
	public function hide_attributes_data_panel($tabs) {
		// Other default values for 'attribute' are; general, inventory, shipping, linked_product, variations, advanced
		$tabs['inventory']['class'][] = 'hide_if_listing_single';
		$tabs['shipping']['class'][] = 'hide_if_listing_single';
		$tabs['linked_product']['class'][] = 'hide_if_listing_single';
		$tabs['variations']['class'][] = 'hide_if_listing_single';
		$tabs['attribute']['class'][] = 'hide_if_listing_single';
		$tabs['advanced']['class'][] = 'hide_if_listing_single';
	
		$tabs['listings'] = array(
				'label'	=> __('Listings level', 'W2DC'),
				'target' => 'listing_single_product_data',
				'class'	=> array('show_if_listing_single', 'show_if_listing_single', 'advanced_options'),
		);
		return $tabs;
	}

	public function add_raiseup_price() {
		woocommerce_wp_text_input(array('id' => '_raiseup_price', 'label' => __('Listings raise up price', 'W2DC') . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price', 'wrapper_class' => 'show_if_listing_single'));
	}
	
	public function new_product_tab_content() {
		global $w2dc_instance;
		?>
			<div id="listing_single_product_data" class="panel woocommerce_options_panel">
					<div class="options_group">
						<?php
						$options = array();
						foreach ($w2dc_instance->levels->levels_array as $level)
							$options[$level->id] = __('Single listing of level "'.esc_attr($level->name).'"', 'W2DC');
	
						woocommerce_wp_radio(array('id' => '_listings_level', 'options' => $options, 'label' => __('Choose the level of listing for this product type', 'W2DC')));
						?>
					</div>
			</div>
			<?php 
	}

	public function save_listing_single_tab_content($post_id) {
		update_post_meta($post_id, '_listings_level', (isset($_POST['_listings_level']) ? wc_clean($_POST['_listings_level']) : ''));

		update_post_meta($post_id, '_raiseup_price', (isset($_POST['_raiseup_price']) ? wc_clean($_POST['_raiseup_price']) : ''));
	}
	
	public function create_price($link_text, $listing) {
		if ($product = $this->get_product_by_level_id($listing->level->id))
			return  $link_text .' - ' . w2dc_format_price(w2dc_recalcPrice($product->get_price()));
	}
	
	public function raiseup_price($link_text, $listing) {
		if ($product = $this->get_product_by_level_id($listing->level->id))
			return  $link_text .' - ' . w2dc_format_price(w2dc_recalcPrice($product->raiseup_price));
	}
	
	public function renew_price($link_text, $listing) {
		if ($product = $this->get_product_by_level_id($listing->level->id))
			return  $link_text .' - ' . w2dc_format_price(w2dc_recalcPrice($product->get_price()));
	}
	
	public function upgrade_price($link_text, $old_level, $new_level) {
		if ($product = $this->get_product_by_level_id($new_level->id))
			return  $link_text .' - ' . w2dc_format_price(w2dc_recalcPrice($product->get_price()));
	}
	
	public function levels_price_front_table_header($pre, $post) {
		echo $pre . __('Price', 'W2DC') . $post;
	}

	public function levels_price_front_table_row($level, $pre, $post) {
		if (!($product = $this->get_product_by_level_id($level->id)) || w2dc_recalcPrice($product->get_price()) == 0)
			$out = '<span class="w2dc-price w2dc-payments-free">' . __('FREE', 'W2DC') . '</span>';
		else {
			$out = '<span class="w2dc-price">' . $product->get_price_html() . '</span>';
	
			if (!$level->eternal_active_period) {
				$string_arr = array();
				if ($level->active_days == 1 && $level->active_months == 0 && $level->active_years == 0)
					$string_arr[] = __('daily', 'W2DC');
				elseif ($level->active_days > 0)
				$string_arr[] = $level->active_days . ' ' . _n('day', 'days', $level->active_days, 'W2DC');
				if ($level->active_days == 0 && $level->active_months == 1 && $level->active_years == 0)
					$string_arr[] = __('monthly', 'W2DC');
				elseif ($level->active_months > 0)
				$string_arr[] = $level->active_months . ' ' . _n('month', 'months', $level->active_months, 'W2DC');
				if ($level->active_days == 0 && $level->active_months == 0 && $level->active_years == 1)
					$string_arr[] = __('annually', 'W2DC');
				elseif ($level->active_years > 0)
				$string_arr[] = $level->active_years . ' ' . _n('year', 'years', $level->active_years, 'W2DC');
				$out .= '/ ' . implode(', ', $string_arr);
			}
		}
	
		echo $pre . $out . $post;
	}
	
	public function levels_price_table_header($columns) {
		$w2dc_columns['price'] = __('Price', 'W2DC');
	
		return array_slice($columns, 0, 2, true) + $w2dc_columns + array_slice($columns, 2, count($columns)-2, true);
	}
	
	public function levels_price_table_row($items_array, $level) {
		if (!($product = $this->get_product_by_level_id($level->id)) || (get_option('w2dc_payments_free_for_admins') && current_user_can('manage_options')))
			$w2dc_columns['price'] = '<span class="w2dc-payments-free">' . __('FREE', 'W2DC') . '</span>';
		else
			$w2dc_columns['price'] = $product->get_price_html();
	
		return array_slice($items_array, 0, 1, true) + $w2dc_columns + array_slice($items_array, 1, count($items_array)-1, true);;
	}

	// Woocommerce Dashboard
	public function listing_in_order_table($item_id, $item, $order) {
		if (method_exists($item, 'get_product_id') && ($product = wc_get_product($item->get_product_id())) && $product->product_type == 'listing_single') {
			if ($listing = $this->get_listing_by_item_id($item_id)) {
				$action = wc_get_order_item_meta($item_id, '_w2dc_action');

				if (is_user_logged_in() && w2dc_current_user_can_edit_listing($listing->post->ID))
					$listing_link = '<a href="' . w2dc_get_edit_listing_link($listing->post->ID) . '" title="' . esc_attr('edit listing', 'W2DC') . '">' . $listing->title() . '</a>';
				else
					$listing_link = $listing->title();
				?>
					<p>
						<?php echo __('Directory listing:', 'W2DC') . ' ' . $listing_link; ?>
						<br />
						<?php if ($action == 'raiseup'):
						_e('Order for listing raise up', 'W2DC'); ?>
						<br />
						<?php endif; ?>
						<?php if ($action == 'upgrade'):
						_e('Order for listing upgrade', 'W2DC'); ?>
						<br />
						<?php endif; ?>
						<?php _e('Status: ', 'W2DC');
						if ($listing->status == 'active')
							echo '<span class="w2dc-badge w2dc-listing-status-active">' . __('active', 'W2DC') . '</span>';
						elseif ($listing->status == 'expired')
							echo '<span class="w2dc-badge w2dc-listing-status-expired">' . __('expired', 'W2DC') . '</span>';
						elseif ($listing->status == 'unpaid')
							echo '<span class="w2dc-badge w2dc-listing-status-unpaid">' . __('unpaid', 'W2DC') . '</span>';
						elseif ($listing->status == 'stopped')
							echo '<span class="w2dc-badge w2dc-listing-status-stopped">' . __('stopped', 'W2DC') . '</span>';
						?>
						<br />
						<?php _e('Expiration Date:', 'W2DC'); ?>
						<?php if ($listing->level->eternal_active_period) _e('Eternal active period', 'W2DC'); else echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), intval($listing->expiration_date)); ?>
					</p>
					<?php 
			}
		}
	}
	
	public function update_user_info($customer_data) {
		global $w2dc_instance;

		foreach (WC()->cart->cart_contents as $value) {
			$product = $value['data'];
			if (isset($value['_w2dc_anonymous_user']) && isset($value['_w2dc_listing_id']) && isset($value['_w2dc_action']) && $value['_w2dc_action'] == 'activation' && $product->product_type == 'listing_single') {
				$listing = $w2dc_instance->listings_manager->loadListing($value['_w2dc_listing_id']);
				if ($listing) {
					$customer_data['ID'] = $listing->post->post_author;
					return $customer_data;
				}
			}
		}

		return $customer_data;
	}
	
	public function reassign_user($user_id) {
		global $w2dc_instance;

		foreach (WC()->cart->cart_contents as $value) {
			$product = $value['data'];
			if (isset($value['_w2dc_anonymous_user']) && isset($value['_w2dc_listing_id']) && isset($value['_w2dc_action']) && $value['_w2dc_action'] == 'activation' && $product->product_type == 'listing_single') {
				$listing = $w2dc_instance->listings_manager->loadListing($value['_w2dc_listing_id']);
				if ($listing && $listing->post->post_author != $user_id) {
					$arg = array(
							'ID' => $listing->post->ID,
							'post_author' => $user_id,
					);
					wp_update_post($arg);
				}
			}
		}
		
		return $user_id;
	}
	
	public function listing_in_checkout($item_data, $cart_item) {
		global $w2dc_instance;

		$product = $cart_item['data'];
		if (isset($cart_item['_w2dc_listing_id']) && $product->product_type == 'listing_single') {
			$listing = $w2dc_instance->listings_manager->loadListing($cart_item['_w2dc_listing_id']);
			if ($listing) {
				$item_data[] = array(
						'name' => __('Listing name', 'W2DC'),
						'value' => $listing->title()
				);
				if (isset($cart_item['_w2dc_action'])) {
					$item_data[] = array(
							'name' => __('Listing action', 'W2DC'),
							'value' => $cart_item['_w2dc_action']
					);
				}
			}
		}
		
		return $item_data;
	}
	
	public function checkout_listing_raiseup_price($cart_object) {
		foreach ($cart_object->cart_contents as $value) {
			$product = $value['data'];
			if (isset($value['_w2dc_action']) && $value['_w2dc_action'] == 'raiseup' && $product->product_type == 'listing_single') {
				$value['data']->set_price($value['data']->raiseup_price);
			}
		}
	}
	
	public function add_order_item_meta($item_id, $item, $order_id) {
		if (isset($item->legacy_values['_w2dc_listing_id'])) {
			wc_add_order_item_meta($item_id, '_w2dc_listing_id', $item->legacy_values['_w2dc_listing_id']);
		}
		if (isset($item->legacy_values['_w2dc_action'])) {
			wc_add_order_item_meta($item_id, '_w2dc_action', $item->legacy_values['_w2dc_action']);
		}
	}
	
	public function create_listing_single_order($listing_id, $level_id, $action = 'activation', $redirect = true) {
		if ($product = $this->get_product_by_level_id($level_id)) {
			$options = array(
					'_w2dc_listing_id' => $listing_id,
					'_w2dc_action' => $action
			);

			if ($action == 'activation' && !is_user_logged_in()) {
				$options['_w2dc_anonymous_user'] = true;
			}

			if (!is_admin()) {
				WC()->cart->add_to_cart($product->get_id(), 1, 0, array(), $options);
				if ($redirect && ($checkout_url = wc_get_checkout_url())) {
					wp_redirect($checkout_url);
					die();
				}
			} else {
				// on admin dashboard we create new order directly without cart and checkout
				if ($action == 'raiseup') {
					$product->set_price($product->raiseup_price);
				}

				$user_id = get_current_user_id();
				$order = wc_create_order(array('customer_id' => $user_id));

				$order_item_id = $order->add_product($product);
				wc_add_order_item_meta($order_item_id, '_w2dc_listing_id', $listing_id);
				
				w2dc_set_order_address($order, $user_id);
				
				$order->calculate_totals();
				
				wc_add_order_item_meta($order_item_id, '_w2dc_action', $action);
				
				w2dc_addMessage(__('Complete the order on WooCommerce Orders page.', 'W2DC'));
			}
		}
	}

	public function create_activation_order($listing) {
		if ($listing && ($product = $this->get_product_by_level_id($listing->level->id)) && w2dc_recalcPrice($product->get_price()) > 0) {
			update_post_meta($listing->post->ID, '_listing_status', 'unpaid');
			$this->create_listing_single_order($listing->post->ID, $listing->level->id, 'activation');
		}
		return $listing;
	}
	
	public function renew_listing_order($continue, $listing, $continue_invoke_hooks) {
		if ($continue_invoke_hooks[0]) {
			if ($order = w2dc_get_last_order_of_listing($listing->post->ID)) {
				if (!$order->is_paid()) {
					$order_url = $order->get_checkout_payment_url();
					if ($order_url && is_user_logged_in()) {
						wp_redirect($order_url);
						die();
					}
					return false;
				}
			}
	
			if (($product = $this->get_product_by_level_id($listing->level->id)) && w2dc_recalcPrice($product->get_price()) > 0) {
				$this->create_listing_single_order($listing->post->ID, $listing->level->id, 'renew');
				$continue_invoke_hooks[0] = false;
				return false;
			}
		}

		return $continue;
	}
	
	public function listing_raiseup_order($continue, $listing, $continue_invoke_hooks) {
		if ($continue_invoke_hooks[0]) {
			if (($product = $this->get_product_by_level_id($listing->level->id)) && w2dc_recalcPrice($product->raiseup_price) > 0) {
				$this->create_listing_single_order($listing->post->ID, $listing->level->id, 'raiseup');
				$continue_invoke_hooks[0] = false;
				return false;
			}
		}
		return $continue;
	}

	public function listing_upgrade_order($continue, $listing, $continue_invoke_hooks) {
		if ($continue_invoke_hooks[0]) {
			$new_level_id = get_post_meta($listing->post->ID, '_new_level_id', true);
			if ($new_level_id && ($product = $this->get_product_by_level_id($new_level_id)) && w2dc_recalcPrice($product->get_price()) > 0) {
				$this->create_listing_single_order($listing->post->ID, $new_level_id, 'upgrade');
				$continue_invoke_hooks[0] = false;
				return false;
			}
		}
		
		return $continue;
	}

	public function complete_payment($status, $order_id) {
		$this->activate_listing($status, $order_id);
	
		return $status;
	}
	
	public function complete_status($order_id) {
		$this->activate_listing('completed', $order_id);
	}
	
	public function activate_listing($status, $order_id) {
		if ($status == 'completed') {
			$order = wc_get_order($order_id);
			$items = $order->get_items();
			foreach ($items AS $item_id=>$item) {
				if (($product = wc_get_product($item->get_product_id())) && $product->product_type == 'listing_single') {
					if ($listing = $this->get_listing_by_item_id($item_id)) {
						$action = wc_get_order_item_meta($item_id, '_w2dc_action');
						switch ($action) {
							case "activation":
							case "renew":
								$listing->processActivate(false);
								break;
							case "raiseup":
								$listing->processRaiseUp(false);
								break;
							case "upgrade":
								$new_level_id = get_post_meta($listing->post->ID, '_new_level_id', true);
								$listing->changeLevel($new_level_id, false);
								break;
						}
					}
				}
			}
		}
	}
	
	public function get_product_by_level_id($level_id) {
		$result = get_posts(array(
				'post_type' => 'product',
				'posts_per_page' => 1,
				'tax_query' => array(array(
						'taxonomy' => 'product_type',
						'field' => 'slug',
						'terms' => array('listing_single'),
						'operator' => 'IN'
				)),
				'meta_query' => array(
						array(
								'key' => '_listings_level',
								'value' => $level_id,
								'type' => 'numeric'
						)
				)
		));
		if ($result)
			return wc_get_product($result[0]->ID);
	}
	
	function get_listing_by_item_id($item_id) {
		global $w2dc_instance;
	
		$listing_id = wc_get_order_item_meta($item_id, '_w2dc_listing_id');
		if ($listing_id) {
			$listing = $w2dc_instance->listings_manager->loadListing($listing_id);
			if ($listing) {
				return $listing;
			}
		}
	}
}
?>