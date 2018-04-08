<?php

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	add_filter('w2dc_build_settings', 'woo_plugin_settings');
	function woo_plugin_settings($options) {
		$options['template']['menus']['advanced']['controls']['woocommerce'] = array(
			'type' => 'section',
			'title' => __('Woocommerce', 'W2DC'),
			'fields' => array(
				array(
					'type' => 'toggle',
					'name' => 'w2dc_woocommerce_functionality',
					'label' => __('WooCommerce payments for the directory', 'W2DC'),
					'default' => get_option('w2dc_woocommerce_functionality'),
				),
				array(
					'type' => 'radiobutton',
					'name' => 'w2dc_woocommerce_mode',
					'label' => __('Products to sell', 'W2DC'),
					'items' => array(
						array(
							'value' => 'single',
							'label' =>__('only single listings', 'W2DC'),
						),
						array(
							'value' => 'packages',
							'label' =>__('only packages of listings', 'W2DC'),
						),
						array(
							'value' => 'both',
							'label' =>__('both products, packages and single listings', 'W2DC'),
						),
					),
					'default' => array(
						get_option('w2dc_woocommerce_mode')
					),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_payments_free_for_admins',
					'label' => __('Any services are Free for administrators', 'W2DC'),
					'default' => get_option('w2dc_payments_free_for_admins'),
				),
			)
		);
	
		return $options;
	}
	
	add_action('vp_w2dc_option_after_ajax_save', 'woo_save_option', 11, 3);
	function woo_save_option($opts, $old_opts, $status) {
		global $w2dc_instance;
	
		if ($status) {
			if (get_option('w2dc_woocommerce_functionality') && !get_option('w2dc_woocommerce_produts_created')) {
				foreach ($w2dc_instance->levels->levels_array as $level) {
					$post_id = wp_insert_post( array(
					    'post_title' => 'Listing ' . $level->name,
					    'post_status' => 'publish',
					    'post_type' => "product",
					), true);
					if (!is_wp_error($post_id)) {
						wp_set_object_terms($post_id, 'listing_single', 'product_type');
						update_post_meta($post_id, '_visibility', 'visible');
						update_post_meta($post_id, '_stock_status', 'instock');
						update_post_meta($post_id, 'total_sales', '0');
						update_post_meta($post_id, '_downloadable', 'no');
						update_post_meta($post_id, '_virtual', 'yes');
						update_post_meta($post_id, '_regular_price', '');
						update_post_meta($post_id, '_sale_price', '');
						update_post_meta($post_id, '_purchase_note', '');
						update_post_meta($post_id, '_featured', 'no');
						update_post_meta($post_id, '_weight', '');
						update_post_meta($post_id, '_length', '');
						update_post_meta($post_id, '_width', '');
						update_post_meta($post_id, '_height', '');
						update_post_meta($post_id, '_sku', '');
						update_post_meta($post_id, '_product_attributes', array());
						update_post_meta($post_id, '_sale_price_dates_from', '');
						update_post_meta($post_id, '_sale_price_dates_to', '');
						update_post_meta($post_id, '_price', '');
						update_post_meta($post_id, '_sold_individually', '');
						update_post_meta($post_id, '_manage_stock', 'no');
						update_post_meta($post_id, '_backorders', 'no');
						update_post_meta($post_id, '_stock', '');
	
						update_post_meta($post_id, '_listings_level', $level->id);
					}
				}
				add_option('w2dc_woocommerce_produts_created', true);
			}
		}
	}
}

if (w2dc_is_woo_active()) {
	
	include_once W2DC_FSUBMIT_PATH . 'classes/wc/listing_single_product.php';
	include_once W2DC_FSUBMIT_PATH . 'classes/wc/listings_package_product.php';
	
	global $w2dc_instance;

	if (get_option('w2dc_woocommerce_mode') == 'single' || get_option('w2dc_woocommerce_mode') == 'both')
		$w2dc_instance->listing_single_product = new w2dc_listing_single_product;
	if (get_option('w2dc_woocommerce_mode') == 'packages' || get_option('w2dc_woocommerce_mode') == 'both')
		$w2dc_instance->listings_package_product = new w2dc_listings_package_product;

	// Remove listings products from the Shop
	add_action('pre_get_posts', 'w2dc_exclude_products_from_shop');
	function w2dc_exclude_products_from_shop($q) {
		if (!$q->is_main_query())
			return;
		if (!$q->is_post_type_archive())
			return;
	
		if (!is_admin() && is_shop()) {
		 $q->set('tax_query', array(array(
		 		'taxonomy' => 'product_type',
		 		'field' => 'slug',
		 		'terms' => array('listing_single', 'listings_package'),
		 		'operator' => 'NOT IN'
		 )));
		}
	
		remove_action('pre_get_posts', 'w2dc_exclude_products_from_shop');
	}
	
	function w2dc_format_price($price) {
		if ($price == 0) {
			$out = '<span class="w2dc-payments-free">' . __('FREE', 'W2DC') . '</span>';
		} else {
			$out = wc_price($price);
		}
		return $out;
	}
	
	function w2dc_recalcPrice($price) {
		// if any services are free for admins - show 0 price
		if (get_option('w2dc_payments_free_for_admins') && current_user_can('manage_options')) {
			return 0;
		} else
			return $price;
	}


	// WC Dashboard
	add_filter('woocommerce_account_menu_items', 'woo_account_dashboard_menu');
	function woo_account_dashboard_menu($items) {
		global $w2dc_instance;

		if (isset($w2dc_instance->dashboard_page_url) && $w2dc_instance->dashboard_page_url) {
			$directory_dashboard['directory_dashboard'] = __('Listings dashboard', 'W2DC');
			array_splice($items, 1, 0, $directory_dashboard);
		}
		
		return $items;
	}
	add_filter('woocommerce_get_endpoint_url', 'woo_account_dashboard_menu_url', 10, 2);
	function woo_account_dashboard_menu_url($url, $endpoint) {
		global $w2dc_instance;

		if (isset($w2dc_instance->dashboard_page_url) && $w2dc_instance->dashboard_page_url)
			if ($endpoint == 'directory_dashboard')
				return w2dc_dashboardUrl();
		
		return $url;
	}

	add_action('woocommerce_account_dashboard', 'woo_account_dashboard_content');
	function woo_account_dashboard_content() {
		global $w2dc_instance, $w2dc_fsubmit_instance;

		?>
		<?php if ($w2dc_instance->submit_page_url): ?>
		<p>
			<?php _e("You can submit directory listings.", "W2DC"); ?>
			<br />
			<?php
			if (w2dc_is_woo_packages()) :
				foreach ($w2dc_instance->levels->levels_array as $level):
					if ($out = $w2dc_instance->listings_package_product->available_listings_descr($level->id, __("submit and raise up", "W2DC"))):
						echo $level->name . ' ' . __('listings', 'W2DC') . ': ' . $out;  ?>
					<br />
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php $w2dc_fsubmit_instance->add_submit_button(); ?>
		</p>
		<?php endif; ?>
		<?php 
	}
	
	function w2dc_get_last_order_of_listing($listing_id) {
		global $wpdb;

		$row = $wpdb->get_row($wpdb->prepare("SELECT MAX(woo_meta.order_item_id) AS last_item_id, woo_orders.order_id AS order_id FROM {$wpdb->prefix}woocommerce_order_itemmeta AS woo_meta LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS woo_orders ON woo_meta.order_item_id = woo_orders.order_item_id WHERE woo_meta.meta_key = '_w2dc_listing_id' AND woo_meta.meta_value = %d", $listing_id), ARRAY_A);
		if ($row['last_item_id']) {
			$last_item_id = $row['last_item_id'];
			$order = wc_get_order($row['order_id']);
			return $order;
		}
	}

	function w2dc_set_order_address($order, $user_id) {
		$address = array(
			'first_name' => get_user_meta($user_id, 'billing_first_name', true),
			'last_name'  => get_user_meta($user_id, 'billing_last_name', true),
			'company'    => get_user_meta($user_id, 'billing_company', true),
			'email'      => get_user_meta($user_id, 'billing_email', true),
			'phone'      => get_user_meta($user_id, 'billing_phone', true),
			'address_1'  => get_user_meta($user_id, 'billing_address_1', true),
			'address_2'  => get_user_meta($user_id, 'billing_address_2', true),
			'city'       => get_user_meta($user_id, 'billing_city', true),
			'state'      => get_user_meta($user_id, 'billing_state', true),
			'postcode'   => get_user_meta($user_id, 'billing_postcode', true),
			'country'    => get_user_meta($user_id, 'billing_country', true),
		);
		$order->set_address($address, 'billing');
	}
	
	add_action('w2dc_dashboard_links', 'woo_add_orders_dashboard_link');
	function woo_add_orders_dashboard_link() {
		$orders_page_endpoint = get_option('woocommerce_myaccount_orders_endpoint', 'orders');
		$myaccount_page = get_option('woocommerce_myaccount_page_id');
		if ($orders_page_endpoint && $myaccount_page && ($orders_url = wc_get_endpoint_url($orders_page_endpoint, '', get_permalink($myaccount_page)))) {
			$args = array(
			    	'post_status' => 'any',
			    	'post_type' => 'shop_order',
					'posts_per_page' => -1,
					'meta_key' => '_customer_user',
					'meta_value' => get_current_user_id()
			);
			$orders_query = new WP_Query($args);
			wp_reset_postdata();

			echo '<li><a href="' . $orders_url . '">' . __('My orders', 'W2DC'). ' (' . $orders_query->found_posts . ')</a></li>';
		}
	}

	// Pay order link in listings table
	add_action('w2dc_listing_status_option', 'woo_pay_order_link');
	function woo_pay_order_link($listing) {
		if ($listing->post->post_author == get_current_user_id() && ($listing->status == 'unpaid' || $listing->status == 'expired')) {
			if (($order = w2dc_get_last_order_of_listing($listing->post->ID)) && !$order->is_paid()) {
				$packages_manager = new w2dc_listings_packages_manager;
				if ($packages_manager->can_user_create_listing_in_level($listing->level->id)) {
					echo '<br /><a href="' . add_query_arg('apply_listing_payment', $listing->post->ID) . '">' . __('apply payment', 'W2DC') . '</a>';
				} else {
					$order_url = $order->get_checkout_payment_url();

					echo '<br /><a href="' . $order_url . '">' . __('pay order', 'W2DC') . '</a>';
				}
			} else {
				$packages_manager = new w2dc_listings_packages_manager;
				if ($packages_manager->can_user_create_listing_in_level($listing->level->id)) {
					echo '<br /><a href="' . add_query_arg('apply_listing_payment', $listing->post->ID) . '">' . __('apply payment', 'W2DC') . '</a>';
				}
			}
		}
	}
	
	add_action('init', 'woo_apply_payment');
	function woo_apply_payment() {
		global $w2dc_instance;

		if (isset($_GET['apply_listing_payment']) && is_numeric($_GET['apply_listing_payment'])) {
			$listing_id = w2dc_getValue($_GET, 'apply_listing_payment');
			if ($listing_id && w2dc_current_user_can_edit_listing($listing_id)) {
				$listing = $w2dc_instance->listings_manager->loadListing($listing_id);
				if ($listing->status == 'unpaid' || $listing->status == 'expired') {
					$packages_manager = new w2dc_listings_packages_manager;
					if ($packages_manager->can_user_create_listing_in_level($listing->level->id)) {
						$listing->processActivate(false);
						$packages_manager->process_listing_creation_for_user($listing->level->id);
						if ($listing->status == 'unpaid')
							w2dc_addMessage(__("Listing was successfully activated.", "W2DC"));
						elseif ($listing->status == 'expired')
							w2dc_addMessage(__("Listing was successfully renewed and activated.", "W2DC"));
						
						wp_redirect(remove_query_arg('apply_listing_payment'));
						die();
					}
				}
			}
		}
	}
	
	add_action('w2dc_listing_info_metabox_html', 'w2dc_last_order_listing_link');
	function w2dc_last_order_listing_link($listing) {
		if ($order = w2dc_get_last_order_of_listing($listing->post->ID)) {
			?>
			<div class="misc-pub-section">
				<?php _e('WC order', 'W2DC'); ?>:
				<?php echo "<a href=". get_edit_post_link($order->get_id()) . ">" . sprintf(__("Order #%d details", "W2DC"), $order->get_id()) . "</a>"; ?>
			</div>
			<?php
		}
	}
}

class w2dc_listings_packages_manager {
	public $listings_numbers = array();

	public function get_listings_of_user($user_id = false) {
		global $w2dc_instance;
		
		if ($this->listings_numbers)
			return $this->listings_numbers;
		
		if (!$user_id)
			$user_id = get_current_user_id();

		foreach ($w2dc_instance->levels->levels_array as $level) {
			$this->listings_numbers[$level->id]['unlimited'] = false;
			$this->listings_numbers[$level->id]['number'] = 0;
			if (get_user_meta($user_id, '_listings_unlimited_'.$level->id, true))
				$this->listings_numbers[$level->id]['unlimited'] = true;
			elseif ($listings_number = get_user_meta($user_id, '_listings_number_'.$level->id, true))
				$this->listings_numbers[$level->id]['number'] = (int)$listings_number;
		}
		return $this->listings_numbers;
	}
	
	public function can_user_create_any_listing($user_id = false) {
		if (!$user_id)
			$user_id = get_current_user_id();
		
		$numbers = $this->get_listings_of_user($user_id);
		foreach ($numbers AS $number) {
			if ($number['unlimited'] || $numbers['number'] > 0)
				return true;
		}
	}

	public function can_user_create_listing_in_level($level_id, $user_id = false) {
		if (!$user_id)
			$user_id = get_current_user_id();
		
		$numbers = $this->get_listings_of_user($user_id);
		
		if ($numbers[$level_id]['unlimited'] || $numbers[$level_id]['number'] > 0)
			return true;
	}
	
	public function process_listing_creation_for_user($level_id, $user_id = false) {
		if (!$user_id)
			$user_id = get_current_user_id();
		
		$numbers = $this->get_listings_of_user($user_id);
		if (!$numbers[$level_id]['unlimited']) {
			update_user_meta($user_id, '_listings_number_'.$level_id, $numbers[$level_id]['number'] - 1);
			$this->listings_numbers[$level_id]['number'] = $numbers[$level_id]['number'] - 1;
		}
	}
}

?>