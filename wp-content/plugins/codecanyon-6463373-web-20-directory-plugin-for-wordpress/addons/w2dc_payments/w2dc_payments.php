<?php

define('W2DC_INVOICE_TYPE', 'w2dc_invoice');

define('W2DC_PAYMENTS_PATH', plugin_dir_path(__FILE__));

function w2dc_payments_loadPaths() {
	define('W2DC_PAYMENTS_TEMPLATES_PATH',  W2DC_PAYMENTS_PATH . 'templates/');
	define('W2DC_PAYMENTS_RESOURCES_URL', plugins_url('/', __FILE__) . 'resources/');
}
add_action('init', 'w2dc_payments_loadPaths', 0);

include_once W2DC_PAYMENTS_PATH . 'classes/invoice.php';

class w2dc_payments_plugin {
	
	public function __construct() {
		register_activation_hook(__FILE__, array($this, 'activation'));
	}
	
	public function activation() {
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		if (!defined('W2DC_VERSION') && version_compare(W2DC_VERSION, '1.2.0', '<=')) {
			deactivate_plugins(basename(__FILE__)); // Deactivate ourself
			wp_die("Web 2.0 Directory plugin v1.2.0 or greater required.");
		}
	}

	public function init() {
		global $w2dc_instance;
		
		if (!get_option('w2dc_installed_payments'))
			//w2dc_install_payments();
			add_action('init', 'w2dc_install_payments', 0);
		add_action('w2dc_version_upgrade', 'w2dc_upgrade_payments');

		add_action('init', array($this, 'register_invoice_type'));
		add_action('load-post-new.php', array($this, 'disable_new_invoices_page'));
		// remove links on all pages - 2 hooks needed
		add_action('admin_menu', array($this, 'disable_new_invoices_link'));
		add_action('admin_head', array($this, 'disable_new_invoices_link'));

		add_filter('w2dc_build_settings', array($this, 'plugin_settings'));
		
		add_filter('manage_'.W2DC_INVOICE_TYPE.'_posts_columns', array($this, 'add_invoices_table_columns'));
		add_filter('manage_'.W2DC_INVOICE_TYPE.'_posts_custom_column', array($this, 'manage_invoices_table_rows'), 10, 2);
		add_filter('post_row_actions', array($this, 'remove_row_actions'), 10, 2);
		add_action('wp_before_admin_bar_render', array($this, 'remove_create_invoice_link'));

		add_action('admin_init', array($this, 'remove_metaboxes'));
		add_action('add_meta_boxes', array($this, 'add_invoice_info_metabox'));
		add_action('add_meta_boxes', array($this, 'add_invoice_payment_metabox'));
		add_action('add_meta_boxes', array($this, 'add_invoice_log_metabox'));
		add_action('add_meta_boxes', array($this, 'add_invoice_actions_metabox'));

		add_filter('template_include', array($this, 'print_invoice_template'), 100000);
		
		$this->loadPricesByLevels();
		add_filter('w2dc_levels_loading', array($this, 'loadPricesByLevels'), 10, 2);

		add_filter('w2dc_level_html', array($this, 'levels_price_in_level_html'));
		add_filter('w2dc_level_validation', array($this, 'levels_price_in_level_validation'));
		add_filter('w2dc_level_create_edit_args', array($this, 'levels_price_in_level_create_add'), 1, 2);
		add_filter('w2dc_level_table_header', array($this, 'levels_price_table_header'));
		add_filter('w2dc_level_table_row', array($this, 'levels_price_table_row'), 10, 2);

		add_action('w2dc_submitlisting_levels_th', array($this, 'levels_price_front_table_header'), 10, 2);
		add_action('w2dc_submitlisting_levels_rows', array($this, 'levels_price_front_table_row'), 10, 3);
		
		add_filter('w2dc_level_upgrade_meta', array($this, 'levels_upgrade_meta'), 10, 2);
		add_action('w2dc_upgrade_meta_html', array($this, 'levels_upgrade_meta_html'), 10, 2);
		
		add_filter('w2dc_create_listings_steps_html', array($this, 'pay_invoice_step'), 10, 2);

		add_filter('w2dc_create_option', array($this, 'create_price'), 10, 2);
		add_filter('w2dc_raiseup_option', array($this, 'raiseup_price'), 10, 2);
		add_filter('w2dc_renew_option', array($this, 'renew_price'), 10, 2);
		add_filter('w2dc_level_upgrade_option', array($this, 'upgrade_price'), 10, 3);
		
		add_action('init', array($this, 'invoice_actions'));
		
		add_filter('query_vars', array($this, 'w2dc_payments_query_vars'));
		
		// This is really strange thing, that users may see ANY attachments (including invoices) owned by other users, so we need this hack
		add_filter('pre_get_posts', array($this, 'prevent_users_see_other_invoices'));
		
		add_filter('bulk_actions-edit-'.W2DC_INVOICE_TYPE, array($this, 'remove_bulk_actions'));
		
		add_action('w2dc_dashboard_links', array($this, 'add_invoices_dashboard_link'));
		add_filter('w2dc_frontend_controller_construct', array($this, 'handle_dashboard_controller'));
		add_filter('w2dc_get_edit_invoice_link', array($this, 'edit_invoices_links'), 10, 2);
		
		// sometimes user under contributor role can not view own invoice, that may cause blank page on listings submission
		add_filter('map_meta_cap', array($this, 'map_meta_cap'), 10, 4);

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts_styles'));
	}

	public function add_invoices_table_columns($columns) {
		$w2dc_columns['item'] = __('Item', 'W2DC');
		$w2dc_columns['price'] = __('Price', 'W2DC');
		$w2dc_columns['payment'] = __('Payment', 'W2DC');

		$columns['title'] = __('Invoice', 'W2DC');
		
		unset($columns['cb']);

		return array_slice($columns, 0, 1, true) + $w2dc_columns + array_slice($columns, 1, count($columns)-1, true);
	}
	
	public function manage_invoices_table_rows($column, $invoice_id) {
		switch ($column) {
			case "item":
				if (($invoice = getInvoiceByID($invoice_id)) && is_object($invoice->item_object))
					echo $invoice->item_object->getItemLink();
				break;
			case "price":
				if ($invoice = getInvoiceByID($invoice_id))
					echo $invoice->price();
				break;
			case "payment":
				if ($invoice = getInvoiceByID($invoice_id))
					if ($invoice->status == 'unpaid') {
						echo '<span class="w2dc-badge w2dc-invoice-status-unpaid">' . __('unpaid', 'W2DC') . '</span>';
						if (w2dc_current_user_can_edit_listing($invoice->post->ID) && current_user_can('edit_published_posts'))
							echo '<br /><a href="' . w2dc_get_edit_invoice_link($invoice_id) . '"><img src="' . W2DC_PAYMENTS_RESOURCES_URL . 'images/money_add.png' . '" class="w2dc-field-icon" />' . __('pay invoice', 'W2DC') . '</a>';
					} elseif ($invoice->status == 'paid') {
						echo '<span class="w2dc-badge w2dc-invoice-status-paid">' . __('paid', 'W2DC') . '</span>';
						if ($invoice->gateway)
							echo '<br /><b>' . gatewayName($invoice->gateway) . '</b>';
					} elseif ($invoice->status == 'pending') {
						echo '<span class="w2dc-badge w2dc-invoice-status-pending">' . __('pending', 'W2DC') . '</span>';
						if ($invoice->gateway)
							echo '<br /><b>' . gatewayName($invoice->gateway) . '</b>';
					}
				break;
		}
	}
	
	public function remove_row_actions($actions, $post) {
		if ($post->post_type == W2DC_INVOICE_TYPE) {
			unset($actions['inline hide-if-no-js']);
			unset($actions['view']);
			//unset($actions['trash']);
		}
		return $actions;
	}
	
	public function remove_create_invoice_link() {
		global $wp_admin_bar;

		$wp_admin_bar->remove_menu('new-w2dc_invoice');
	}
	
	public function remove_metaboxes() {
		remove_meta_box('submitdiv', W2DC_INVOICE_TYPE, 'side');
		remove_meta_box('slugdiv', W2DC_INVOICE_TYPE, 'normal');
		remove_meta_box('authordiv', W2DC_INVOICE_TYPE, 'normal');
	}
	
	public function add_invoice_info_metabox($post_type) {
		if ($post_type == W2DC_INVOICE_TYPE) {
			add_meta_box('w2dc_invoice_info',
					__('Invoice Info', 'W2DC'),
					array($this, 'invoice_info_metabox'),
					W2DC_INVOICE_TYPE,
					'normal',
					'high');
		}
	}
	
	public function invoice_info_metabox($post) {
		$invoice = getInvoiceByID($post->ID);
		w2dc_renderTemplate(array(W2DC_PAYMENTS_TEMPLATES_PATH, 'info_metabox.tpl.php'), array('invoice' => $invoice));
	}
	
	public function add_invoice_log_metabox($post_type) {
		global $post;

		if ($post_type == W2DC_INVOICE_TYPE) {
			if ($post && ($invoice = getInvoiceByID($post->ID)) && $invoice->log) {
				add_meta_box('w2dc_invoice_log',
						__('Invoice Log', 'W2DC'),
						array($this, 'invoice_log_metabox'),
						W2DC_INVOICE_TYPE,
						'normal',
						'high');
			}
		}
	}
	
	public function invoice_log_metabox($post) {
		$invoice = getInvoiceByID($post->ID);
		w2dc_renderTemplate(array(W2DC_PAYMENTS_TEMPLATES_PATH, 'log_metabox.tpl.php'), array('invoice' => $invoice));
	}

	public function add_invoice_payment_metabox($post_type) {
		global $post;

		if ($post_type == W2DC_INVOICE_TYPE) {
			if (get_option('w2dc_paypal_email') || get_option('w2dc_allow_bank') || ((get_option('w2dc_stripe_test') && get_option('w2dc_stripe_test_secret') && get_option('w2dc_stripe_test_public')) || (get_option('w2dc_stripe_live_secret') && get_option('w2dc_stripe_live_public')))) {
				if ($post && ($invoice = getInvoiceByID($post->ID)) && $invoice->status == 'unpaid' && !$invoice->gateway) {
					add_meta_box('w2dc_invoice_payment',
							__('Invoice Payment - choose payment gateway', 'W2DC'),
							array($this, 'invoice_payment_metabox'),
							W2DC_INVOICE_TYPE,
							'normal',
							'high');
				}
			}
		}
	}
	
	public function invoice_payment_metabox($post) {
		$invoice = getInvoiceByID($post->ID);
		
		$paypal = new w2dc_paypal();
		$paypal_subscription = new w2dc_paypal_subscription();
		$bank_transfer = new w2dc_bank_transfer();
		$stripe = new w2dc_stripe();
		
		w2dc_renderTemplate(array(W2DC_PAYMENTS_TEMPLATES_PATH, 'payment_metabox.tpl.php'), array('invoice' => $invoice, 'paypal' => $paypal, 'paypal_subscription' => $paypal_subscription, 'bank_transfer' => $bank_transfer, 'stripe' => $stripe));
	}

	public function add_invoice_actions_metabox($post_type) {
		if ($post_type == W2DC_INVOICE_TYPE) {
			add_meta_box('w2dc_invoice_actions',
					__('Invoice actions', 'W2DC'),
					array($this, 'invoice_actions_metabox'),
					W2DC_INVOICE_TYPE,
					'side',
					'high');
		}
	}
	
	public function invoice_actions_metabox($post) {
		$invoice = getInvoiceByID($post->ID);
		w2dc_renderTemplate(array(W2DC_PAYMENTS_TEMPLATES_PATH, 'actions_metabox.tpl.php'), array('invoice' => $invoice));
	}
	
	public function plugin_settings($options) {
		$options['template']['menus']['payments'] = array(
			'name' => 'payments',
			'title' => __('Payments settings', 'W2DC'),
			'icon' => 'font-awesome:w2dc-fa-dollar',
			'controls' => array(
				'payments' => array(
					'type' => 'section',
					'title' => __('General payments settings', 'W2DC'),
					'fields' => array(
						array(
							'type' => 'toggle',
							'name' => 'w2dc_payments_free_for_admins',
							'label' => __('Any services are Free for administrators', 'W2DC'),
							'default' => get_option('w2dc_payments_free_for_admins'),
						),
						array(
							'type' => 'select',
							'name' => 'w2dc_payments_currency',
							'label' => __('Currency', 'W2DC'),
							'items' => array(
								array('value' => 'USD', 'label' => __('US Dollars ($)', 'W2DC')),
								array('value' => 'EUR', 'label' => __('Euros (€)', 'W2DC')),
								array('value' => 'GBP', 'label' => __('Pounds Sterling (£)', 'W2DC')),
								array('value' => 'AUD', 'label' => __('Australian Dollars ($)', 'W2DC')),
								array('value' => 'BRL', 'label' => __('Brazilian Real (R$)', 'W2DC')),
								array('value' => 'CAD', 'label' => __('Canadian Dollars ($)', 'W2DC')),
								array('value' => 'CZK', 'label' => __('Czech Koruna (Kč)', 'W2DC')),
								array('value' => 'DKK', 'label' => __('Danish Krone (kr)', 'W2DC')),
								array('value' => 'HKD', 'label' => __('Hong Kong Dollar ($)', 'W2DC')),
								array('value' => 'HUF', 'label' => __('Hungarian Forint (Ft)', 'W2DC')),
								array('value' => 'ILS', 'label' => __('Israeli Shekel (₪)', 'W2DC')),
								array('value' => 'JPY', 'label' => __('Japanese Yen (¥)', 'W2DC')),
								array('value' => 'MYR', 'label' => __('Malaysian Ringgits (RM)', 'W2DC')),
								array('value' => 'MXN', 'label' => __('Mexican Peso ($)', 'W2DC')),
								array('value' => 'NZD', 'label' => __('New Zealand Dollar ($)', 'W2DC')),
								array('value' => 'NOK', 'label' => __('Norwegian Krone (kr)', 'W2DC')),
								array('value' => 'PHP', 'label' => __('Philippine Pesos (P)', 'W2DC')),
								array('value' => 'PLN', 'label' => __('Polish Zloty (zł)', 'W2DC')),
								array('value' => 'SGD', 'label' => __('Singapore Dollar ($)', 'W2DC')),
								array('value' => 'SEK', 'label' => __('Swedish Krona (kr)', 'W2DC')),
								array('value' => 'CHF', 'label' => __('Swiss Franc (Fr)', 'W2DC')),
								array('value' => 'TWD', 'label' => __('Taiwan New Dollar ($)', 'W2DC')),
								array('value' => 'THB', 'label' => __('Thai Baht (฿)', 'W2DC')),
								array('value' => 'TRY', 'label' => __('Turkish Lira (₤)', 'W2DC')),
							),
							'default' => array(get_option('w2dc_payments_currency')),
						),
						array(
							'type' => 'textbox',
							'name' => 'w2dc_payments_symbol_code',
							'label' => __('Currency symbol or code', 'W2DC'),
							'default' => get_option('w2dc_payments_symbol_code'),
						),
						array(
							'type' => 'radiobutton',
							'name' => 'w2dc_payments_symbol_position',
							'label' => __('Currency symbol or code position', 'W2DC'),
							'items' => array(
								array('value' => 1, 'label' => '$1.00'),
								array('value' => 2, 'label' => '$ 1.00'),
								array('value' => 3, 'label' => '1.00$'),
								array('value' => 4, 'label' => '1.00 $'),
							),
							'default' => array(get_option('w2dc_payments_symbol_position')),
						),
						array(
							'type' => 'radiobutton',
							'name' => 'w2dc_payments_decimal_separator',
							'label' => __('Decimal separator', 'W2DC'),
							'items' => array(
								array('value' => '.', 'label' => __('dot', 'W2DC')),
								array('value' => ',', 'label' => __('comma', 'W2DC')),
							),
							'default' => array(get_option('w2dc_payments_decimal_separator')),
						),
						array(
							'type' => 'toggle',
							'name' => 'w2dc_hide_decimals',
							'label' => __('Hide decimals in levels price table', 'W2DC'),
							'default' => get_option('w2dc_hide_decimals'),
						),
						array(
							'type' => 'radiobutton',
							'name' => 'w2dc_payments_thousands_separator',
							'label' => __('Thousands separator', 'W2DC'),
							'items' => array(
								array('value' => '', 'label' => __('no separator', 'W2DC')),
								array('value' => '.', 'label' => __('dot', 'W2DC')),
								array('value' => ',', 'label' => __('comma', 'W2DC')),
								array('value' => 'space', 'label' => __('space', 'W2DC')),
							),
							'default' => array(get_option('w2dc_payments_thousands_separator')),
						),
					),
				),
				'taxes' => array(
					'type' => 'section',
					'title' => __('Sales tax', 'W2DC'),
					'fields' => array(
						array(
							'type' => 'toggle',
							'name' => 'w2dc_enable_taxes',
							'label' => __('Enable taxes', 'W2DC'),
							'default' => get_option('w2dc_enable_taxes'),
						),
						array(
							'type' => 'textarea',
							'name' => 'w2dc_taxes_info',
							'label' => __('Selling company information', 'W2DC'),
							'default' => get_option('w2dc_taxes_info'),
						),
						array(
							'type' => 'textbox',
							'name' => 'w2dc_tax_name',
							'label' => __('Tax name', 'W2DC'),
							'description' => __('abbreviation, e.g. "VAT"', 'W2DC'),
							'default' => get_option('w2dc_tax_name'),
						),
						array(
							'type' => 'textbox',
							'name' => 'w2dc_tax_rate',
							'label' => __('Tax rate', 'W2DC'),
							'description' => __('In percents', 'W2DC'),
							'default' => get_option('w2dc_tax_rate'),
						),
						array(
							'type' => 'radiobutton',
							'name' => 'w2dc_taxes_mode',
							'label' => __('Include or exclude value added taxes', 'W2DC'),
							'description' => __('Do you want prices on the website to be quoted including or excluding value added taxes?', 'W2DC'),
							'items' => array(
								array('value' => 'include', 'label' => __('Include', 'W2DC')),
								array('value' => 'exclude', 'label' => __('Exclude', 'W2DC')),
							),
							'default' => array(get_option('w2dc_taxes_mode')),
						),
					),
				),
				'bank' => array(
					'type' => 'section',
					'title' => __('Bank transfer settings', 'W2DC'),
					'fields' => array(
						array(
							'type' => 'toggle',
							'name' => 'w2dc_allow_bank',
							'label' => __('Allow bank transfer', 'W2DC'),
							'default' => get_option('w2dc_allow_bank'),
						),
						array(
							'type' => 'textarea',
							'name' => 'w2dc_bank_info',
							'label' => __('Bank transfer information', 'W2DC'),
							'default' => get_option('w2dc_bank_info'),
						),
					),
				),
				'paypal' => array(
					'type' => 'section',
					'title' => __('PayPal settings', 'W2DC'),
					'fields' => array(
						array(
							'type' => 'textbox',
							'name' => 'w2dc_paypal_email',
							'label' => __('Business email', 'W2DC'),
							'default' => get_option('w2dc_paypal_email'),
						),
						array(
							'type' => 'toggle',
							'name' => 'w2dc_paypal_single',
							'label' => __('Allow single payment', 'W2DC'),
							'default' => get_option('w2dc_paypal_single'),
						),
						array(
							'type' => 'toggle',
							'name' => 'w2dc_paypal_subscriptions',
							'label' => __('Allow subscriptions', 'W2DC'),
							'description' => __('Only for listings with limited active period', 'W2DC'),
							'default' => get_option('w2dc_paypal_subscriptions'),
						),
						array(
							'type' => 'toggle',
							'name' => 'w2dc_paypal_test',
							'label' => __('Test Sandbox mode', 'W2DC'),
							'description' => sprintf(__('You must have a <a href="%s" target="_blank">PayPal Sandbox</a> account setup before using this feature. <a href="%s">IPN URL</a>', 'W2DC'), 'http://developer.paypal.com/', home_url('ipn_token/'.ipn_token().'/gateway/paypal')),
							'default' => get_option('w2dc_paypal_test'),
						),
					),
				),
				'stripe' => array(
					'type' => 'section',
					'title' => __('Stripe settings', 'W2DC'),
					'fields' => array(
						array(
							'type' => 'textbox',
							'name' => 'w2dc_stripe_test_secret',
							'label' => __('Test secret key', 'W2DC'),
							'default' => get_option('w2dc_stripe_test_secret'),
						),
						array(
							'type' => 'textbox',
							'name' => 'w2dc_stripe_test_public',
							'label' => __('Test publishable key', 'W2DC'),
							'default' => get_option('w2dc_stripe_test_public'),
						),
						array(
							'type' => 'textbox',
							'name' => 'w2dc_stripe_live_secret',
							'label' => __('Live secret key', 'W2DC'),
							'default' => get_option('w2dc_stripe_live_secret'),
						),
						array(
							'type' => 'textbox',
							'name' => 'w2dc_stripe_live_public',
							'label' => __('Live publishable key', 'W2DC'),
							'default' => get_option('w2dc_stripe_live_public'),
						),
						array(
							'type' => 'toggle',
							'name' => 'w2dc_stripe_test',
							'label' => __('Test Sandbox mode', 'W2DC'),
							'description' => __('You can only use <a href="http://stripe.com/" target="_blank">Stripe</a> in test mode until you activate your account.', 'W2DC'),
							'default' => get_option('w2dc_stripe_test'),
						),
					),
				),
			),
		);
		
		$options['template']['menus']['notifications']['controls']['notifications']['fields'][] = array(
				'type' => 'textarea',
				'name' => 'w2dc_invoice_paid_notification',
				'label' => __('Notification of paid invoice', 'W2DC'),
				'default' => get_option('w2dc_invoice_paid_notification'),
		);
		
		return $options;
	}

	public function register_invoice_type() {
		$args = array(
			'labels' => array(
				'name' => __('Directory invoices', 'W2DC'),
				'singular_name' => __('Directory invoice', 'W2DC'),
				'edit_item' => __('View Invoice', 'W2DC'),
				'search_items' => __('Search invoices', 'W2DC'),
				'not_found' =>  __('No invoices found', 'W2DC'),
				'not_found_in_trash' => __('No invoices found in trash', 'W2DC')
			),
			'has_archive' => true,
			'description' => __('Directory invoices', 'W2DC'),
			'show_ui' => true,
			'supports' => array('author'),
			'menu_icon' => W2DC_PAYMENTS_RESOURCES_URL . 'images/dollar.png',
		);
		register_post_type(W2DC_INVOICE_TYPE, $args);
	}
	
	function disable_new_invoices_page() {
		if (isset($_GET['post_type']) && $_GET['post_type'] == W2DC_INVOICE_TYPE)
			wp_die("You ain't allowed to do that!");
	}
	function disable_new_invoices_link() {
		global $submenu;
		unset($submenu['edit.php?post_type=' . W2DC_INVOICE_TYPE][10]);

		if (function_exists('get_current_screen')) {
			$screen = get_current_screen();
			if ($screen && $screen->post_type == W2DC_INVOICE_TYPE)
				echo '<style type="text/css">.add-new-h2, h1 .page-title-action { display:none; }</style>';
		}
	}
	
	public function loadPricesByLevels($level = null, $array = array()) {
		global $w2dc_instance, $wpdb;

		if (!$array) {
			$array = $wpdb->get_results("SELECT * FROM {$wpdb->w2dc_levels} ORDER BY order_num", ARRAY_A);

			foreach ($array AS $row) {
				$w2dc_instance->levels->levels_array[$row['id']]->price = $row['price'];
				$w2dc_instance->levels->levels_array[$row['id']]->raiseup_price = $row['raiseup_price'];
				
				if (is_object($level) && $level->id == $row['id']) {
					$level->price = $row['price'];
					$level->raiseup_price = $row['raiseup_price'];
				}
			}
		} else {
			$level->price = $array['price'];
			$level->raiseup_price = $array['raiseup_price'];
		}
		
		return $level;
	}
	
	public function levels_price_in_level_html($level) {
		w2dc_renderTemplate(array(W2DC_PAYMENTS_PATH, 'templates/levels_price_in_level.tpl.php'), array('level' => $level));
	}
	
	public function levels_price_in_level_validation($validation) {
		$validation->set_rules('price', __('Listings price', 'W2DC'), 'is_numeric');
		$validation->set_rules('raiseup_price', __('Listings raise up price', 'W2DC'), 'is_numeric');
		
		return $validation;
	}
	
	public function levels_price_in_level_create_add($insert_update_args, $array) {
		$insert_update_args['price'] = w2dc_getValue($array, 'price', 0);
		$insert_update_args['raiseup_price'] = w2dc_getValue($array, 'raiseup_price', 0);
		return $insert_update_args;
	}
	
	public function levels_price_table_header($columns) {
		$w2dc_columns['price'] = __('Price', 'W2DC');
		
		return array_slice($columns, 0, 2, true) + $w2dc_columns + array_slice($columns, 2, count($columns)-2, true);
	}

	public function levels_price_table_row($items_array, $level) {
		$w2dc_columns['price'] = formatPrice($level->price);
		
		return array_slice($items_array, 0, 1, true) + $w2dc_columns + array_slice($items_array, 1, count($items_array)-1, true);
	}
	
	public function levels_price_front_table_header($pre, $post) {
		echo $pre . __('Price', 'W2DC') . $post;
	}

	public function levels_price_front_table_row($level, $pre, $post) {
		if ($level->price == 0)
			$out = '<span class="w2dc-price w2dc-payments-free">' . __('FREE', 'W2DC') . '</span>';
		else {
			$thousands_separator = get_option('w2dc_payments_thousands_separator');
			if ($thousands_separator == 'space')
				$thousands_separator = ' ';

			$value = explode('.', number_format($level->price, 2, '.', $thousands_separator));
			$cents = array_pop($value);
			$price = implode('.', $value);
			if (!get_option('w2dc_hide_decimals'))
				$out = $price . '<span class="w2dc-price-cents">' . $cents . '</span>';
			else 
				$out = $price;
			switch (get_option('w2dc_payments_symbol_position')) {
				case 1:
					$out = get_option('w2dc_payments_symbol_code') . $out;
					break;
				case 2:
					$out = get_option('w2dc_payments_symbol_code') . ' ' . $out;
					break;
				case 3:
					$out = $out . get_option('w2dc_payments_symbol_code');
					break;
				case 4:
					$out = $out . ' ' . get_option('w2dc_payments_symbol_code');
					break;
			}
			$out = '<span class="w2dc-price">' . $out . '</span>';
			
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
	
	public function levels_upgrade_meta($upgrade_meta, $level) {
		global $w2dc_instance;

		if (w2dc_getValue($_GET, 'page') == 'w2dc_manage_upgrades') {
			$results = array();
			foreach ($w2dc_instance->levels->levels_array AS $_level) {
				if (($price = w2dc_getValue($_POST, 'level_price_' . $level->id . '_' . $_level->id)) && is_numeric($price))
					$results[$_level->id]['price'] = $price;
				else
					$results[$_level->id]['price'] = 0;
			}
	
			foreach ($upgrade_meta AS $level_id=>$meta)
				if (isset($results[$level_id]))
					$upgrade_meta[$level_id] = $results[$level_id] + $upgrade_meta[$level_id];
		}

		return $upgrade_meta;
	}
	
	public function levels_upgrade_meta_html($level1, $level2) {
		if (isset($level1->upgrade_meta[$level2->id]) && isset($level1->upgrade_meta[$level2->id]['price']))
			$price = $level1->upgrade_meta[$level2->id]['price'];
		else
			$price = 0;

		echo get_option('w2dc_payments_symbol_code') . '<input type="text" size="4" name="level_price_' . $level1->id . '_' . $level2->id . '" value="' . esc_attr($price) . '" /><br />';
	}
	
	public function pay_invoice_step($step, $level = null) {
		if ($level && recalcPrice($level->price)) {
			echo '<div class="w2dc-adv-line"></div>';
			echo '<div class="w2dc-adv-step">';
			echo '<div class="w2dc-adv-circle">' . __('Step', 'W2DC') . $step++ . '</div>';
			echo __('Pay Invoice', 'W2DC');
			echo '</div>';
		}
		return $step++;
	}
	
	public function create_price($link_text, $listing) {
		return  $link_text .' - ' . formatPrice(recalcPrice($listing->level->price));
	}

	public function raiseup_price($link_text, $listing) {
		return  $link_text .' - ' . formatPrice(recalcPrice($listing->level->raiseup_price));
	}

	public function renew_price($link_text, $listing) {
		return  $link_text .' - ' . formatPrice(recalcPrice($listing->level->price));
	}

	public function upgrade_price($link_text, $old_level, $new_level) {
		return  $link_text .' - ' . (isset($old_level->upgrade_meta[$new_level->id]) ? formatPrice(recalcPrice($old_level->upgrade_meta[$new_level->id]['price'])) : formatPrice(0));
	}

	public function print_invoice_template($template) {
		global $w2dc_instance;

		if (is_page($w2dc_instance->index_page_id) && $w2dc_instance->action == 'w2dc_print_invoice' && isset($_GET['invoice_id']) && is_numeric($_GET['invoice_id'])) {
			if (w2dc_current_user_can_edit_listing($_GET['invoice_id'])) {
				if (is_file(W2DC_PAYMENTS_TEMPLATES_PATH . 'invoice_print-custom.tpl.php'))
					$template = W2DC_PAYMENTS_TEMPLATES_PATH . 'invoice_print-custom.tpl.php';
				else
					$template = W2DC_PAYMENTS_TEMPLATES_PATH . 'invoice_print.tpl.php';
			} else
				wp_die('You are not able to access this page!');
		}
		return $template;
	}

	public function invoice_actions() {
		if (isset($_GET['post']) && is_numeric($_GET['post']) && w2dc_current_user_can_edit_listing($_GET['post'])) {
			$invoice_id = $_GET['post'];
			if (($post = get_post($invoice_id)) && $post->post_type == W2DC_INVOICE_TYPE && ($invoice = getInvoiceByID($invoice_id))) {
				$redirect = false;
				if (isset($_GET['w2dc_gateway']) && !$invoice->gateway) {
					switch ($_GET['w2dc_gateway']) {
						case 'paypal':
							if (get_option('w2dc_paypal_email') && get_option('w2dc_paypal_single'))
								$gateway = $_GET['w2dc_gateway'];
							break;
						case 'paypal_subscription':
							if (get_option('w2dc_paypal_email') && get_option('w2dc_paypal_subscriptions') && $invoice->is_subscription)
								$gateway = $_GET['w2dc_gateway'];
							break;
						case 'stripe':
							if ((get_option('w2dc_stripe_test') && get_option('w2dc_stripe_test_secret') && get_option('w2dc_stripe_test_public')) || (get_option('w2dc_stripe_live_secret') && get_option('w2dc_stripe_live_public')))
								$gateway = $_GET['w2dc_gateway'];
							break;
						case 'bank_transfer':
							if (get_option('w2dc_allow_bank'))
								$gateway = $_GET['w2dc_gateway'];
							break;
					}
					if (isset($gateway)) {
						$invoice->setStatus('pending');
						$invoice->setGateway($gateway);

						$gateway = $invoice->getGatewayObject();
						$invoice->logMessage(sprintf(__('Payment gateway was selected: %s', 'W2DC'), $gateway->name()));
						w2dc_addMessage(__('Payment gateway was selected!', 'W2DC'));
						$gateway->submitPayment($invoice);
						$redirect = true;
					}
				}
	
				if (isset($_GET['invoice_action']) && $_GET['invoice_action'] == 'reset_gateway') {
					$invoice->setStatus('unpaid');
					$invoice->setGateway('');
					$invoice->logMessage(__('Payment gateway was reset', 'W2DC'));
					w2dc_addMessage(__('Payment gateway was reset!', 'W2DC'));
					$redirect = true;
				}
				
				if (isset($_GET['invoice_action']) && $_GET['invoice_action'] == 'set_paid' && $invoice->status != 'paid' && current_user_can('edit_others_posts')) {
					if ($invoice->item_object->complete()) {
						$invoice->setStatus('paid');
						$invoice->logMessage(__('Invoice was manually set as paid', 'W2DC'));
						w2dc_addMessage(__('Invoice was manually set as paid!', 'W2DC'));
					} else 
						w2dc_addMessage(__('An error has occured!', 'W2DC'), 'error');
					$redirect = true;
				}

				if ($redirect) {
					wp_redirect(w2dc_get_edit_invoice_link($invoice_id, 'redirect'));
					die();
				}
			}
		}
	}
	
	public function w2dc_payments_query_vars($vars) {
		$vars[] = 'ipn_token';
		$vars[] = 'gateway';

		return $vars;
	}
	
	public function prevent_users_see_other_invoices($wp_query) {
		global $current_user;
		if (is_admin() && $current_user && !current_user_can('edit_others_posts') && isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] == W2DC_INVOICE_TYPE) {
			$wp_query->set('author', $current_user->ID);
			add_filter('views_edit-'.W2DC_INVOICE_TYPE, array($this, 'remove_invoices_counts'));
		}
	}
	public function remove_invoices_counts($views) {
		return array();
	}

	public function remove_bulk_actions($actions) {
		return array();
	}
	
	public function process_invoices_query($frontend_controller) {
		global $w2dc_instance;

		if ($w2dc_instance->action == 'invoices') {
			if (get_query_var('page'))
				$paged = get_query_var('page');
			elseif (get_query_var('paged'))
				$paged = get_query_var('paged');
			else
				$paged = 1;
		} else
			$paged = -1;

		$args = array(
				'post_type' => W2DC_INVOICE_TYPE,
				'author' => get_current_user_id(),
				'paged' => $paged,
				'posts_per_page' => 10,
		);
		$frontend_controller->invoices_query = new WP_Query($args);
		wp_reset_postdata();
	}
	
	public function add_invoices_dashboard_link($frontend_controller) {
		global $w2dc_instance;
		$this->process_invoices_query($frontend_controller);

		echo '<li ' . (($frontend_controller->active_tab == 'invoices') ? 'class="w2dc-active"' : '') . '><a href="' . w2dc_dashboardUrl(array('w2dc_action' => 'invoices')) . '">' . __('Invoices', 'W2DC'). ' (' . $frontend_controller->invoices_query->found_posts . ')</a></li>';
	}
	
	public function handle_dashboard_controller($frontend_controller) {
		global $w2dc_instance;

		if (get_class($frontend_controller) == 'w2dc_dashboard_controller') {
			if (!is_user_logged_in())
				$this->template = W2DC_FSUBMIT_TEMPLATES_PATH . 'login_form.tpl.php';
			else {
				if ($w2dc_instance->action == 'invoices') {
					$this->process_invoices_query($frontend_controller);
		
					$frontend_controller->invoices = array();
					while ($frontend_controller->invoices_query->have_posts()) {
						$frontend_controller->invoices_query->the_post();
						
						$invoice = getInvoiceByID(get_the_ID());
						$frontend_controller->invoices[get_the_ID()] = $invoice;
					}
					// this is reset is really required after the loop ends
					wp_reset_postdata();
					
					$frontend_controller->template = W2DC_FSUBMIT_TEMPLATES_PATH . 'dashboard.tpl.php';
					$frontend_controller->subtemplate = W2DC_PAYMENTS_TEMPLATES_PATH . 'invoices_dashboard.tpl.php';
					$frontend_controller->active_tab = 'invoices';
				} elseif ($w2dc_instance->action == 'view_invoice' && isset($_GET['post']) && is_numeric($_GET['post']) && w2dc_current_user_can_edit_listing($_GET['post'])) {
					if ($frontend_controller->invoice = getInvoiceByID($_GET['post'])) {
						$frontend_controller->paypal = new w2dc_paypal();
						$frontend_controller->paypal_subscription = new w2dc_paypal_subscription();
						$frontend_controller->bank_transfer = new w2dc_bank_transfer();
						$frontend_controller->stripe = new w2dc_stripe();
		
						$frontend_controller->template = W2DC_FSUBMIT_TEMPLATES_PATH . 'dashboard.tpl.php';
						$frontend_controller->subtemplate = W2DC_PAYMENTS_TEMPLATES_PATH . 'view_invoice_dashboard.tpl.php';
						$frontend_controller->active_tab = 'invoices';
					}
				}
			}
		}

		return $frontend_controller;
	}
	
	public function edit_invoices_links($url, $post_id) {
		global $w2dc_instance;

		if (!is_admin() && $w2dc_instance->dashboard_page_url && ($post = get_post($post_id)) && $post->post_type == W2DC_INVOICE_TYPE)
			return w2dc_dashboardUrl(array('w2dc_action' => 'view_invoice', 'post' => $post_id));
		
		return $url;
	}
	
	function map_meta_cap($caps, $cap, $user_id, $args) {
		if ('edit_post' == $cap) {
			$post = get_post($args[0]);
			$post_type = get_post_type_object($post->post_type);
			if (is_object($post_type) && $post_type->name == W2DC_INVOICE_TYPE) {
				$caps = array();
				if ($user_id == $post->post_author)
					$caps[] = $post_type->cap->edit_posts;
				else
					$caps[] = $post_type->cap->edit_others_posts;
			}
		}
		return $caps;
	}

	public function enqueue_scripts_styles($load_scripts_styles = false) {
		global $w2dc_instance, $w2dc_payments_enqueued;
		if ((is_admin() || $w2dc_instance->frontend_controllers || $load_scripts_styles) && !$w2dc_payments_enqueued) {
			if (!(function_exists('is_rtl') && is_rtl()))
				wp_register_style('w2dc_payments', W2DC_PAYMENTS_RESOURCES_URL . 'css/payments.css');
			else 
				wp_register_style('w2dc_payments', W2DC_PAYMENTS_RESOURCES_URL . 'css/payments-rtl.css');
	
			wp_enqueue_style('w2dc_payments');
			if (is_file(W2DC_PAYMENTS_RESOURCES_URL . 'css/payments-custom.css'))
				wp_register_style('w2dc_payments-custom', W2DC_PAYMENTS_RESOURCES_URL . 'css/payments-custom.css');
	
			wp_enqueue_style('w2dc_payments-custom');

			$w2dc_payments_enqueued = true;
		}
	}
}

function recalcPrice($price) {
	// if any services are free for admins - show 0 price
	if (get_option('w2dc_payments_free_for_admins') && current_user_can('manage_options')) {
		return 0;
	} else
		return $price;
}

function formatPrice($value = 0) {
	if ($value == 0) {
		$out = '<span class="w2dc-payments-free">' . __('FREE', 'W2DC') . '</span>';
	} else {
		$decimal_separator = get_option('w2dc_payments_decimal_separator');

		$thousands_separator = get_option('w2dc_payments_thousands_separator');
		if ($thousands_separator == 'space')
			$thousands_separator = ' ';

		$value = number_format($value, 2, $decimal_separator, $thousands_separator); 

		$out = '$ '.$value;
		switch (get_option('w2dc_payments_symbol_position')) {
			case 1:
				$out = get_option('w2dc_payments_symbol_code') . $value;
				break;
			case 2:
				$out = get_option('w2dc_payments_symbol_code') . ' ' . $value;
				break;
			case 3:
				$out = $value . get_option('w2dc_payments_symbol_code');
				break;
			case 4:
				$out = $value . ' ' . get_option('w2dc_payments_symbol_code');
				break;
		}
	}
	return $out;
}

function ipn_token() {
	return md5(site_url() . wp_salt());
}

function w2dc_install_payments() {
	global $wpdb;

	// there may be possible bug in WP, on some servers it doesn't allow to execute more than one SQL query in one request
	$wpdb->query("ALTER TABLE {$wpdb->w2dc_levels} ADD `price` FLOAT( 2 ) NOT NULL DEFAULT '0' AFTER `order_num`");
	if (array_search('price', $wpdb->get_col("DESC {$wpdb->w2dc_levels}"))) {
		$wpdb->query("ALTER TABLE {$wpdb->w2dc_levels} ADD `raiseup_price` FLOAT( 2 ) NOT NULL DEFAULT '0' AFTER `price`");
		if (array_search('raiseup_price', $wpdb->get_col("DESC {$wpdb->w2dc_levels}"))) {
			add_option('w2dc_payments_free_for_admins', 0);
			add_option('w2dc_payments_currency', 'USD');
			add_option('w2dc_payments_symbol_code', '$');
			add_option('w2dc_payments_symbol_position', 1);
			add_option('w2dc_payments_decimal_separator', ',');
			add_option('w2dc_payments_thousands_separator', 'space');
			add_option('w2dc_allow_bank', 1);
			add_option('w2dc_bank_info', '');
			add_option('w2dc_paypal_email', '');
			add_option('w2dc_paypal_subscriptions', 1);
			add_option('w2dc_paypal_test', 0);
			
			w2dc_upgrade_payments('1.6.0');
			w2dc_upgrade_payments('1.8.0');
			w2dc_upgrade_payments('1.9.0');
			w2dc_upgrade_payments('1.9.4');
			w2dc_upgrade_payments('1.12.7');
			
			add_option('w2dc_installed_payments', 1);
		}
	}
}

function w2dc_upgrade_payments($new_version) {
	if ($new_version == '1.6.0') {
		add_option('w2dc_stripe_test_secret', '');
		add_option('w2dc_stripe_test_public', '');
		add_option('w2dc_stripe_live_secret', '');
		add_option('w2dc_stripe_live_public', '');
		add_option('w2dc_stripe_test', 1);
	}

	if ($new_version == '1.8.0') {
		add_option('w2dc_paypal_single', 1);
	}

	if ($new_version == '1.9.0') {
		add_option('w2dc_enable_taxes', 0);
		add_option('w2dc_taxes_info', '');
		add_option('w2dc_tax_name', '');
		add_option('w2dc_tax_rate', 0);
		add_option('w2dc_taxes_mode', 'include');
	}
	
	if ($new_version == '1.9.4') {
		add_option('w2dc_hide_decimals', 0);
	}

	if ($new_version == '1.12.7') {
		add_option('w2dc_invoice_paid_notification', 'Hello [author],

your invoice "[invoice]" was successfully set as paid.

Price: [price]');
	}
}

function w2dc_get_edit_invoice_link($invoice_id, $context = 'display') {
	if (w2dc_current_user_can_edit_listing($invoice_id)) {
		return apply_filters('w2dc_get_edit_invoice_link', get_edit_post_link($invoice_id, $context), $invoice_id);
	}
}

global $w2dc_payments_instance;

$w2dc_payments_instance = new w2dc_payments_plugin();
$w2dc_payments_instance->init();

?>
