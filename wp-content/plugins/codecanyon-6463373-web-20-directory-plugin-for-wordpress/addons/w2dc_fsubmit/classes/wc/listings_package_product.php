<?php

add_action('init', 'w2dc_register_listings_package_product_type');
function w2dc_register_listings_package_product_type() {
	class WC_Product_Listings_package extends WC_Product_Simple {

		public function __construct($product) {

			$this->supports[]   = 'ajax_add_to_cart';

			$this->product_type = 'listings_package';
			
			parent::__construct($product);
		}
		
		public function get_virtual($context = 'view') {
			return true;
		}
		
		public function get_downloadable($context = 'view') {
			return true;
		}
		
		public function get_listings_number_by_level($level_id) {
			if (get_post_meta($this->id, '_listings_unlimited_'.$level_id, true))
				return __('unlimited', 'W2DC');
			else {
				if (!($listings_number = get_post_meta($this->id, '_listings_number_'.$level_id, true)))
					return __('N/A', 'W2DC');
				else 
					return $listings_number;
			}
		}

		public function get_listings_number_all() {
			global $w2dc_instance;

			$result = array();
			foreach ($w2dc_instance->levels->levels_array as $level) {
				$result[$level->id]['unlimited'] = false;
				$result[$level->id]['number'] = 0;
				if (get_post_meta($this->id, '_listings_unlimited_'.$level->id, true))
					$result[$level->id]['unlimited'] = true;
				elseif ($listings_number = get_post_meta($this->id, '_listings_number_'.$level->id, true))
					$result[$level->id]['number'] = $listings_number;
			}
			return $result;
		}
	}
}

class w2dc_listings_package_product {
	
	public function __construct() {
		add_filter('product_type_selector', array($this, 'add_listings_package_product'));
		add_action('admin_footer', array($this, 'listings_package_custom_js'));
		add_action('admin_footer', array($this, 'select_listings_number_custom_js'));
		add_filter('woocommerce_product_data_tabs', array($this, 'hide_attributes_data_panel'));
		add_filter('woocommerce_product_data_panels', array($this, 'new_product_tab_content'));
		add_action('woocommerce_process_product_meta_listings_package', array($this, 'save_listings_package_tab_content'));

		add_filter('woocommerce_order_needs_payment', array($this, 'set_payment_button_for_free_package'), 10, 3);

		// Woocommerce Dashboard - Order Details
		add_action('woocommerce_order_item_meta_start', array($this, 'package_in_order_table'), 10, 3);
		add_action('woocommerce_after_order_itemmeta', array($this, 'package_in_order_table'), 10, 3);
		
		add_filter('woocommerce_payment_complete_order_status', array($this, 'complete_payment'), 10, 2);
		add_action('woocommerce_order_status_completed', array($this, 'complete_status'), 10);

		// Woocommerce Checkout
		add_filter('woocommerce_get_item_data', array($this, 'listing_in_checkout'), 10, 2);
		// Woocommerce add order item meta
		add_action('woocommerce_new_order_item', array($this, 'add_order_item_meta'), 10, 3);
		// when guest user creates new profile after he created a listing
		add_filter('woocommerce_new_customer_data', array($this, 'update_user_info'));
		// when guest user logs in after he created a listing
		add_filter('woocommerce_checkout_customer_id', array($this, 'reassign_user'));
		
		add_action('show_user_profile', array($this, 'add_user_profile_fields'));
		add_action('edit_user_profile', array($this, 'add_user_profile_fields'));
		add_action('personal_options_update', array($this, 'save_user_profile_fields'));
		add_action('edit_user_profile_update', array($this, 'save_user_profile_fields'));
		
		// first of all if user has pre-paid listing(s) - simply activate it after creation
		add_filter('w2dc_listing_creation_front', array($this, 'activate_if_possible'), 1);
		// secondly if package was in cart and listing was already created - activate and process it after payment
		add_filter('w2dc_listing_creation_front', array($this, 'create_activation_order'), 2);

		add_action('w2dc_renew_html', array($this, 'renew_html'));
		add_filter('w2dc_listing_renew', array($this, 'renew_listing_order'), 1, 3);
		
		add_action('w2dc_submitlisting_levels_rows', array($this, 'levels_price_front_table_row'), 11, 3);
		add_filter('w2dc_create_option', array($this, 'create_price'), 10, 2);
		
		add_action('w2dc_raise_up_html', array($this, 'raiseup_html'));
		add_filter('w2dc_listing_raiseup', array($this, 'listing_raiseup_order'), 1, 3);

		add_filter('w2dc_level_upgrade_option', array($this, 'level_upgrade_option'), 10, 3);
		add_filter('w2dc_listing_upgrade', array($this, 'listing_upgrade_order'), 1, 3);
	}

	public function add_listings_package_product($types) {
		$types['listings_package'] = __('Listings Package', 'W2DC');
	
		return $types;
	}
	
	public function listings_package_custom_js() {
		if ('product' != get_post_type())
			return;
	
		?><script type='text/javascript'>
				jQuery(document).ready( function($) {
					$('.options_group.pricing').addClass('show_if_listings_package').show();
					$('.options_group.show_if_downloadable').addClass('hide_if_listings_package').hide();
					$('.general_tab').addClass('active').show();
					$('.listings_package_tab').removeClass('active');
					$('#general_product_data').show();
					$('#listings_package_product_data').hide();
					$('._tax_status_field').parent().addClass('show_if_listings_package');
					if ($('#product-type option:selected').val() == 'listings_package') {
						$('.show_if_listings_package').show();
						$('.hide_if_listings_package').hide();
					}
				});
			</script><?php
	}

	public function select_listings_number_custom_js() {
		global $pagenow;

		if ('product' != get_post_type() && $pagenow != 'profile.php')
			return;
	
		?><script type='text/javascript'>
				jQuery(document).ready( function($) {
					$("input[name*='_listings_unlimited_']").each( function() {
						levelUnlimitedChange($(this));
					});

					$("input[name*='_listings_unlimited_']").change( function() {
						levelUnlimitedChange($(this));
					});

					function levelUnlimitedChange(checkbox) {
						if (checkbox.is(':checked'))
							checkbox.parent().parent().find(".listings_number").attr('disabled', 'true');
						else
							checkbox.parent().parent().find(".listings_number").removeAttr('disabled');
					}
				});
			</script><?php
	}
	
	public function hide_attributes_data_panel($tabs) {
		// Other default values for 'attribute' are; general, inventory, shipping, linked_product, variations, advanced
		$tabs['inventory']['class'][] = 'hide_if_listings_package';
		$tabs['shipping']['class'][] = 'hide_if_listings_package';
		$tabs['linked_product']['class'][] = 'hide_if_listings_package';
		$tabs['variations']['class'][] = 'hide_if_listings_package';
		$tabs['attribute']['class'][] = 'hide_if_listings_package';
		$tabs['advanced']['class'][] = 'hide_if_listings_package';
	
		$tabs['listings_package'] = array(
				'label'	=> __('Listings in Package', 'W2DC'),
				'target' => 'listings_package_product_data',
				'class'	=> array('show_if_listings_package', 'show_if_listings_package', 'advanced_options'),
		);
		return $tabs;
	}

	public function new_product_tab_content() {
		global $w2dc_instance;
		?>
			<div id="listings_package_product_data" class="panel woocommerce_options_panel">
				<?php
				foreach ($w2dc_instance->levels->levels_array as $level):
				?>
				<div class="options_group">
				<?php 
					woocommerce_wp_text_input(array('id' => '_listings_number_'.$level->id, 'label' => __('Number of listings of level "'.esc_attr($level->name).'"', 'W2DC'), 'desc_tip' => 'true', 'description' => __('Enter number of listings in the package or set up as unlimited.', 'W2DC'), 'type' => 'number', 'class' => 'listings_number'));

					woocommerce_wp_checkbox(array('id' => '_listings_unlimited_'.$level->id, 'label' => __('Unlimited', 'W2DC')));
				?>
				</div>
				<?php endforeach; ?>
			</div>
			<?php 
	}

	public function save_listings_package_tab_content($post_id) {
		global $w2dc_instance;

		foreach ($w2dc_instance->levels->levels_array as $level) {
			update_post_meta($post_id, '_listings_number_'.$level->id, (isset($_POST['_listings_number_'.$level->id]) ? wc_clean($_POST['_listings_number_'.$level->id]) : ''));
			update_post_meta($post_id, '_listings_unlimited_'.$level->id, (isset($_POST['_listings_unlimited_'.$level->id]) ? wc_clean($_POST['_listings_unlimited_'.$level->id]) : ''));
		}
	}
	
	public function get_all_packages() {
		$result = get_posts(array(
				'post_type' => 'product',
				'posts_per_page' => -1,
				'tax_query' => array(array(
						'taxonomy' => 'product_type',
						'field' => 'slug',
						'terms' => array('listings_package'),
						'operator' => 'IN'
				))
		));
		$products = array();
		if ($result)
			foreach ($result AS $post) {
				$products[$post->ID] = wc_get_product($post->ID);
			}
		return $products;
	}
	
	public function complete_payment($status, $order_id) {
		$this->set_listings_numbers($status, $order_id);
	
		return $status;
	}
	
	public function complete_status($order_id) {
		$this->set_listings_numbers('completed', $order_id);
	}
	
	public function set_listings_numbers($status, $order_id) {
		global $w2dc_instance;

		if ($status == 'completed') {
			$order = wc_get_order($order_id);
			$user_id = $order->get_user_id();
			$items = $order->get_items();
			foreach ($items AS $item_id=>$item) {
				if (($product = wc_get_product($item->get_product_id())) && $product->product_type == 'listings_package') {
					$listings_numbers = $product->get_listings_number_all();
					foreach ($listings_numbers AS $level_id=>$numbers) {
						if ($numbers['unlimited'])
							update_user_meta($user_id, '_listings_unlimited_'.$level_id, true);
						else
							update_user_meta($user_id, '_listings_number_'.$level_id, $numbers['number'] + (int)get_user_meta($user_id, '_listings_number_'.$level_id, true));
					}

					// if package was in cart and listing was already created - activate and process it after payment
					if ($listing_id = wc_get_order_item_meta($item_id, '_w2dc_listing_id')) {
						if ($listing = $w2dc_instance->listings_manager->loadListing($listing_id)) {
							$packages_manager = new w2dc_listings_packages_manager;
							if ($packages_manager->can_user_create_listing_in_level($listing->level->id)) {
								$listing->processActivate(false);
								$packages_manager->process_listing_creation_for_user($listing->level->id);
							}
						}
					}
				}
			}
		}
	}
	
	public function add_user_profile_fields($user) {
		global $w2dc_instance;
		
		if (!current_user_can('edit_user', $user->ID))
			return false;
	?>
		<h2><?php _e('Directory listings available', 'W2DC'); ?></h3>

		<table class="form-table">
		<?php
		$packages_manager = new w2dc_listings_packages_manager;
		$listings_number = $packages_manager->get_listings_of_user($user->ID);

		foreach ($w2dc_instance->levels->levels_array as $level):
		?>
			<tr>
				<th><label for="listings_number_<?php echo $level->id; ?>"><?php _e('Number of listings of level "'.esc_attr($level->name).'"', 'W2DC'); ?></label></th>
				<td>
					<input type="text" name="_listings_number_<?php echo $level->id; ?>" id="_listings_number_<?php echo $level->id; ?>" value="<?php echo esc_attr($listings_number[$level->id]['number']); ?>" class="regular-text listings_number" />
					<p>
						<input type="checkbox" name="_listings_unlimited_<?php echo $level->id; ?>" id="_listings_unlimited_<?php echo $level->id; ?>" <?php checked($listings_number[$level->id]['unlimited'], 1); ?> value="1" />
						<label for="_listings_unlimited_<?php echo $level->id; ?>"><?php _e('Unlimited', 'W2DC'); ?></label>
					</p>
				</td>
			</tr>
		<?php endforeach; ?>
		</table>
	<?php }
	
	public function save_user_profile_fields($user_id) {
		global $w2dc_instance;

		if (!current_user_can('edit_user', $user_id))
			return false;

		foreach ($w2dc_instance->levels->levels_array as $level) {
			update_user_meta($user_id, '_listings_unlimited_'.$level->id, sanitize_text_field(w2dc_getValue($_POST, '_listings_unlimited_'.$level->id, 0)));
			update_user_meta($user_id, '_listings_number_'.$level->id, sanitize_text_field(w2dc_getValue($_POST, '_listings_number_'.$level->id, 0)));
		}
	}
	
	public function set_payment_button_for_free_package($needs_payment, $order, $valid_order_statuses) {
		$items = $order->get_items();
		foreach ($items AS $item_id=>$item)
			if (($product = wc_get_product($item->get_product_id())) && $product->product_type == 'listings_package')
				if ($order->has_status($valid_order_statuses))
					return true;
				
		return $needs_payment;
	}

	public function package_in_order_table($item_id, $item, $order) {
		global $w2dc_instance;

		if (method_exists($item, 'get_product_id') && ($product = wc_get_product($item->get_product_id())) && $product->product_type == 'listings_package') {
			?>
			<p>
				<?php
				foreach ($w2dc_instance->levels->levels_array AS $level):
					echo $level->name . ' ' . __('listings', 'W2DC') . ': '; ?>
					<strong><?php echo $product->get_listings_number_by_level($level->id); ?></strong>
					<br />
				<?php endforeach; ?>
			</p>
			<?php 
		}
	}
	
	public function available_listings_descr($level_id, $action_string) {
		global $w2dc_instance;
		
		if (is_admin())
			return false;

		$packages_manager = new w2dc_listings_packages_manager;
		$listings_number = $packages_manager->get_listings_of_user();
		
		$out = '';
		if ($listings_number[$level_id]['unlimited'])
			$out = sprintf(__('You can %s any number of %s for <strong>free</strong>', 'W2DC'), $action_string, $w2dc_instance->current_directory->plural);
		elseif ($number = $listings_number[$level_id]['number'])
			$out = sprintf(__('You can %s <strong>%d</strong> %s for <strong>free</strong>', 'W2DC'), $action_string, $number, _n($w2dc_instance->current_directory->single, $w2dc_instance->current_directory->plural, $number, 'W2DC'));
		
		if ($out && count($w2dc_instance->levels->levels_array) > 1)
			$out .= ' ' . __("in this level");
		
		return $out;
	}
	
	public function levels_price_front_table_row($level, $pre, $post) {
		$out = $this->available_listings_descr($level->id, __("submit", "W2DC"));

		if ($out)
			echo $pre . $out . $post;
	}
	
	public function create_price($link_text, $listing) {
		$out = $this->available_listings_descr($listing->level->id, __("submit", "W2DC"));

		if ($out)
			return  $link_text . '<p>' . $out . '</p>';
		else 
			return $link_text;
	}
	
	public function update_user_info($customer_data) {
		global $w2dc_instance;
	
		foreach (WC()->cart->cart_contents as $value) {
			$product = $value['data'];
			if (isset($value['_w2dc_anonymous_user']) && isset($value['_w2dc_listing_id']) && $product->product_type == 'listings_package') {
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
			if (isset($value['_w2dc_anonymous_user']) && isset($value['_w2dc_listing_id']) && $product->product_type == 'listings_package') {
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
		if (isset($cart_item['_w2dc_listing_id']) && $product->product_type == 'listings_package') {
			$listing = $w2dc_instance->listings_manager->loadListing($cart_item['_w2dc_listing_id']);
			if ($listing) {
				$item_data[] = array(
						'name' => __('Listing to activate', 'W2DC'),
						'value' => $listing->title()
				);
			}
		}
	
		return $item_data;
	}
	
	// if user has pre-paid listing(s) - simply activate it after creation
	public function activate_if_possible($listing) {
		if ($listing) {
			$packages_manager = new w2dc_listings_packages_manager;
			if ($packages_manager->can_user_create_listing_in_level($listing->level->id)) {
				$packages_manager->process_listing_creation_for_user($listing->level->id);
				return false;
			}
		}
		return $listing;
	}

	// if package was in cart and listing was already created - activate and process it after payment
	public function create_activation_order($listing) {
		if ($listing) {
			if ($cart_item_key = $this->is_package_in_cart($listing->level->id)) {
				update_post_meta($listing->post->ID, '_listing_status', 'unpaid');
				$this->add_listing_to_cart_package($cart_item_key, $listing->post->ID);
				return false;
			}
		}
		return $listing;
	}
	
	public function is_package_in_cart($level_id) {
		if (WC()->cart) {
			foreach(WC()->cart->get_cart() as $cart_item_key=>$value) {
				if (($product = wc_get_product($value['product_id'])) && $product->product_type == 'listings_package') {
					$listings_numbers = $product->get_listings_number_all();
					if ($listings_numbers[$level_id]['unlimited'] || $listings_numbers[$level_id]['number'] > 0) {
						return $cart_item_key;
					}
				}
			}
		}
	}

	public function add_order_item_meta($item_id, $item, $order_id) {
		if (isset($item->legacy_values['_w2dc_listing_id'])) {
			wc_add_order_item_meta($item_id, '_w2dc_listing_id', $item->legacy_values['_w2dc_listing_id']);
		}
	}
	
	// if package was in cart and listing was already created - activate and process it after payment
	public function add_listing_to_cart_package($cart_item_key, $listing_id) {
		if (WC()->cart) {
			foreach (WC()->cart->cart_contents as $item_key=>$value) {
				if ($item_key == $cart_item_key) {
					$product = $value['data'];
					if ($product->product_type == 'listings_package') {
						WC()->cart->cart_contents[$cart_item_key]['_w2dc_listing_id'] = $listing_id;
	
						if (!is_user_logged_in()) {
							WC()->cart->cart_contents[$cart_item_key]['_w2dc_anonymous_user'] = true;
						}
						break;
					}
				}
			}
			
			WC()->cart->set_session();
		
			if ($checkout_url = wc_get_checkout_url()) {
				wp_redirect($checkout_url);
				die();
			}
		}
	}
	
	public function renew_html($listing) {
		$out = $this->available_listings_descr($listing->level->id, __("renew", "W2DC"));
		if ($out)
			echo "<p>" . $out . "</p>";
		elseif (w2dc_is_only_woo_packages()) {
			echo "<p>" . __("You are not able to renew this listing. Purchase listings package first.", "W2DC") . "</p>";
		}
	}
	
	public function renew_listing_order($continue, $listing, $continue_invoke_hooks) {
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
	
		$packages_manager = new w2dc_listings_packages_manager;
		if ($packages_manager->can_user_create_listing_in_level($listing->level->id)) {
			$listing->processActivate(false);
			$packages_manager->process_listing_creation_for_user($listing->level->id);
			$continue_invoke_hooks[0] = false;
			w2dc_addMessage(__("Listing was renewed successfully.", "W2DC"));
			return false;
		}
		return $continue;
	}
	
	public function raiseup_html($listing) {
		$out = $this->available_listings_descr($listing->level->id, __("raise up", "W2DC"));
		if ($out)
			echo "<p>" . $out . "</p>";
		elseif (w2dc_is_only_woo_packages()) {
			echo "<p>" . __("You are not able to raise up this listing. Purchase listings package first.", "W2DC") . "</p>";
		}
	}
	
	public function listing_raiseup_order($continue, $listing, $continue_invoke_hooks) {
		$packages_manager = new w2dc_listings_packages_manager;
		if ($packages_manager->can_user_create_listing_in_level($listing->level->id)) {
			$packages_manager->process_listing_creation_for_user($listing->level->id);
			$continue_invoke_hooks[0] = false;
			return true;
		} else {
			if (w2dc_is_only_woo_packages()) {
				w2dc_addMessage(__("You are not able to raise up this listing. Purchase listings package first.", "W2DC"), "error");
				return false;
			}
		}

		return $continue;
	}
	
	public function level_upgrade_option($link_text, $old_level, $new_level) {
		$out = $link_text;

		$packages_manager = new w2dc_listings_packages_manager;
		if ($packages_manager->can_user_create_listing_in_level($new_level->id))
			$out .= ' ' . __("(you can upgrade to this level for free)", "W2DC");
		
		return $out;
	}

	public function listing_upgrade_order($continue, $listing, $continue_invoke_hooks) {
		$new_level_id = get_post_meta($listing->post->ID, '_new_level_id', true);

		$packages_manager = new w2dc_listings_packages_manager;
		if ($packages_manager->can_user_create_listing_in_level($new_level_id)) {
			$packages_manager->process_listing_creation_for_user($new_level_id);
			$continue_invoke_hooks[0] = false;
			return true;
		} else {
			if (w2dc_is_only_woo_packages()) {
				w2dc_addMessage(__("You are not able to change listing to this level. Purchase listings package first.", "W2DC"), "error");
				return false;
			}
		}

		return $continue;
	}
}
?>