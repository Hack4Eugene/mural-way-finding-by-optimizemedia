<?php

class w2dc_admin {

	public function __construct() {
		global $w2dc_instance;
		
		add_action('admin_init', array($w2dc_instance, 'getIndexPage'));

		add_action('admin_menu', array($this, 'menu'));

		$w2dc_instance->settings_manager = new w2dc_settings_manager;

		$w2dc_instance->directories_manager = new w2dc_directories_manager;

		$w2dc_instance->levels_manager = new w2dc_levels_manager;

		$w2dc_instance->listings_manager = new w2dc_listings_manager;

		$w2dc_instance->locations_manager = new w2dc_locations_manager;

		$w2dc_instance->locations_levels_manager = new w2dc_locations_levels_manager;

		$w2dc_instance->categories_manager = new w2dc_categories_manager;

		$w2dc_instance->content_fields_manager = new w2dc_content_fields_manager;

		$w2dc_instance->media_manager = new w2dc_media_manager;

		$w2dc_instance->csv_manager = new w2dc_csv_manager;
		
		$w2dc_instance->maps_importer = new w2dc_maps_importer;

		add_action('admin_menu', array($this, 'addChooseLevelPage'));
		add_action('load-post-new.php', array($this, 'handleLevel'));

		// hide some meta-blocks when create/edit posts
		add_action('admin_init', array($this, 'hideMetaBlocks'));

		// adapted for Relevanssi
		//add_action('admin_init', array($this, 'relevanssi_add_disable_shortcodes'));
		
		add_action('admin_head-post-new.php', array($this, 'hidePreviewButton'));
		
		add_filter('post_row_actions', array($this, 'removeQuickEdit'), 10, 2);
		add_filter('quick_edit_show_taxonomy', array($this, 'removeQuickEditTax'), 10, 2);

		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts_styles'), 0);
		
		add_action('admin_print_scripts', array($w2dc_instance, 'dequeue_maps_googleapis'), 1000);

		add_action('admin_notices', 'w2dc_renderMessages');

		add_action('wp_ajax_w2dc_generate_color_palette', array($this, 'generate_color_palette'));
		add_action('wp_ajax_nopriv_w2dc_generate_color_palette', array($this, 'generate_color_palette'));
		add_action('wp_ajax_w2dc_get_jqueryui_theme', array($this, 'get_jqueryui_theme'));
		add_action('wp_ajax_nopriv_w2dc_get_jqueryui_theme', array($this, 'get_jqueryui_theme'));
		add_action('vp_w2dc_option_before_ajax_save', array($this, 'remove_colorpicker_cookie'));
		add_action('wp_footer', array($this, 'render_colorpicker'));
		
		add_filter('upgrader_package_options', array($this, 'update_does_not_clear_destination'));
	}

	public function addChooseLevelPage() {
		add_submenu_page('options.php',
			__('Choose level of new listing', 'W2DC'),
			__('Choose level of new listing', 'W2DC'),
			'publish_posts',
			'w2dc_choose_level',
			array($this, 'chooseLevelsPage')
		);
	}

	// Special page to choose the level for new listing
	public function chooseLevelsPage() {
		global $w2dc_instance;

		$w2dc_instance->levels_manager->displayChooseLevelTable();
	}
	
	public function handleLevel() {
		global $w2dc_instance;

		if (isset($_GET['post_type']) && $_GET['post_type'] == W2DC_POST_TYPE) {
			if (!isset($_GET['level_id'])) {
				// adapted for WPML
				global $sitepress;
				if (function_exists('wpml_object_id_filter') && $sitepress && isset($_GET['trid']) && isset($_GET['lang']) && isset($_GET['source_lang'])) {
					global $sitepress;
					$listing_id = $sitepress->get_original_element_id_by_trid($_GET['trid']);
					
					$listing = new w2dc_listing();
					$listing->loadListingFromPost($listing_id);
					wp_redirect(add_query_arg(array('post_type' => 'w2dc_listing', 'level_id' => $listing->level->id, 'trid' => $_GET['trid'], 'lang' => $_GET['lang'], 'source_lang' => $_GET['source_lang']), admin_url('post-new.php')));
				} else {
					if (count($w2dc_instance->levels->levels_array) != 1) {
						wp_redirect(add_query_arg('page', 'w2dc_choose_level', admin_url('options.php')));
					} else {
						$single_level = array_shift($w2dc_instance->levels->levels_array);
						wp_redirect(add_query_arg(array('post_type' => 'w2dc_listing', 'level_id' => $single_level->id), admin_url('post-new.php')));
					}
				}
				die();
			}
		}
	}

	public function menu() {
		add_menu_page(__("Directory settings", "W2DC"),
			__('Directory Admin', 'W2DC'),
			'administrator',
			'w2dc_settings',
			null,
			W2DC_RESOURCES_URL . 'images/menuicon.png'
		);
		add_submenu_page(
			'w2dc_settings',
			__("Directory settings", "W2DC"),
			__("Directory settings", "W2DC"),
			'administrator',
			'w2dc_settings',
			null
		);

		add_submenu_page(
			null,
			__("Directory Debug", "W2DC"),
			__("Directory Debug", "W2DC"),
			'administrator',
			'w2dc_debug',
			array($this, 'debug')
		);
		add_submenu_page(
			null,
			__("Directory Reset", "W2DC"),
			__("Directory Reset", "W2DC"),
			'administrator',
			'w2dc_reset',
			array($this, 'reset')
		);
	}

	public function debug() {
		global $w2dc_instance, $wpdb;
		
		$w2dc_locationGeoname = new w2dc_locationGeoname();
		$geolocation_response = $w2dc_locationGeoname->geonames_request('1600 Amphitheatre Parkway Mountain View, CA 94043', 'test');

		$settings = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'w2dc_%'", ARRAY_A);

		w2dc_renderTemplate('debug.tpl.php', array(
			'rewrite_rules' => get_option('rewrite_rules'),
			'geolocation_response' => $geolocation_response,
			'settings' => $settings,
			'levels' => $w2dc_instance->levels,
			'content_fields' => $w2dc_instance->content_fields,
		));
	}

	public function reset() {
		global $w2dc_instance, $wpdb;
		
		if (isset($_GET['reset']) && $_GET['reset'] == 'settings') {
			if ($wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'w2dc_%'") !== false) {
				delete_option('vpt_option');
				w2dc_save_dynamic_css();
				w2dc_addMessage('All directory settings were deleted!');
			}
		}
		w2dc_renderTemplate('reset.tpl.php');
	}
	
	public function hideMetaBlocks() {
		global $post, $pagenow;

		if (($pagenow == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == W2DC_POST_TYPE) || ($pagenow == 'post.php' && $post && $post->post_type == W2DC_POST_TYPE)) {
			$user_id = get_current_user_id();
			update_user_meta($user_id, 'metaboxhidden_' . W2DC_POST_TYPE, array('authordiv', 'trackbacksdiv', 'commentstatusdiv', 'postcustom'));
		}
	}

	public function hidePreviewButton() {
		global $post_type;
    	if ($post_type == W2DC_POST_TYPE)
    		echo '<style type="text/css">#preview-action {display: none;}</style>';
	}

	public function removeQuickEdit($actions, $post) {
		if ($post->post_type == W2DC_POST_TYPE) {
			unset($actions['inline hide-if-no-js']);
			unset($actions['view']);
		}
		return $actions;
	}

	public function removeQuickEditTax($show_in_quick_edit, $taxonomy_name) {
		if ($taxonomy_name == W2DC_CATEGORIES_TAX || $taxonomy_name == W2DC_LOCATIONS_TAX)
			$show_in_quick_edit = false;
		
		return $show_in_quick_edit;
	}
	
	public function admin_enqueue_scripts_styles() {
		global $w2dc_instance;

		if (is_customize_preview())
			$this->enqueue_global_vars();
		else
			add_action('admin_head', array($this, 'enqueue_global_vars'));

		add_action('wp_print_scripts', array($w2dc_instance, 'dequeue_maps_googleapis'), 1000);

		wp_register_style('w2dc_bootstrap', W2DC_RESOURCES_URL . 'css/bootstrap.css');
		wp_register_style('w2dc_admin', W2DC_RESOURCES_URL . 'css/admin.css');
		wp_register_style('w2dc_font_awesome', W2DC_RESOURCES_URL . 'css/font-awesome.css');
		wp_register_script('w2dc_js_functions', W2DC_RESOURCES_URL . 'js/js_functions.js', array('jquery'), false, true);

		// this jQuery UI version 1.10.3 is for WP v3.7.1
		wp_register_style('w2dc-jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.css');

		wp_register_script('w2dc_google_maps_edit', W2DC_RESOURCES_URL . 'js/google_maps_edit.js', array('jquery'));

		wp_register_script('w2dc_categories_edit_scripts', W2DC_RESOURCES_URL . 'js/categories_icons.js', array('jquery'));
		wp_register_script('w2dc_categories_scripts', W2DC_RESOURCES_URL . 'js/manage_categories.js', array('jquery'));
		
		wp_register_script('w2dc_locations_edit_scripts', W2DC_RESOURCES_URL . 'js/locations_icons.js', array('jquery'));
		
		wp_register_style('w2dc_media_styles', W2DC_RESOURCES_URL . 'lightbox/css/lightbox.css');
		wp_register_script('w2dc_media_scripts_lightbox', W2DC_RESOURCES_URL . 'lightbox/js/lightbox.min.js', array('jquery'));
		wp_register_script('w2dc_media_scripts', W2DC_RESOURCES_URL . 'js/ajaxfileupload.js', array('jquery'));
		
		wp_enqueue_style('w2dc_bootstrap');
		wp_enqueue_style('w2dc_admin');
		wp_enqueue_style('w2dc_font_awesome');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_style('w2dc-jquery-ui-style');
		wp_enqueue_script('w2dc_js_functions');

		wp_localize_script(
			'w2dc_js_functions',
			'w2dc_google_maps_callback',
			array(
					'callback' => 'w2dc_load_maps_api_backend'
			)
		);

		wp_enqueue_script('w2dc_google_maps_edit');
	}

	public function enqueue_global_vars() {
		// adapted for WPML
		global $sitepress;
		if (function_exists('wpml_object_id_filter') && $sitepress) {
			$ajaxurl = admin_url('admin-ajax.php?lang=' .  $sitepress->get_current_language());
		} else
			$ajaxurl = admin_url('admin-ajax.php');

		echo '
<script>
';
		echo 'var w2dc_js_objects = ' . json_encode(
				array(
						'ajaxurl' => $ajaxurl,
						'ajax_loader_url' => W2DC_RESOURCES_URL . 'images/ajax-loader.gif',
						'ajax_loader_text' => __('Loading...', 'W2DC'),
						'is_rtl' => is_rtl(),
						'is_maps_used' => w2dc_is_maps_used(),
				)
		) . ';
';

		global $w2dc_maps_styles;
		echo 'var w2dc_google_maps_objects = ' . json_encode(
				array(
						'notinclude_maps_api' => ((defined('W2DC_NOTINCLUDE_MAPS_API') && W2DC_NOTINCLUDE_MAPS_API) ? 1 : 0),
						'google_api_key' => trim(get_option('w2dc_google_api_key')),
						'map_markers_type' => get_option('w2dc_map_markers_type'),
						'default_marker_color' => get_option('w2dc_default_marker_color'),
						'default_marker_icon' => get_option('w2dc_default_marker_icon'),
						'global_map_icons_path' => W2DC_MAP_ICONS_URL,
						'marker_image_width' => (int)get_option('w2dc_map_marker_width'),
						'marker_image_height' => (int)get_option('w2dc_map_marker_height'),
						'marker_image_anchor_x' => (int)get_option('w2dc_map_marker_anchor_x'),
						'marker_image_anchor_y' => (int)get_option('w2dc_map_marker_anchor_y'),
						'default_geocoding_location' => get_option('w2dc_default_geocoding_location'),
						'locations_targeting_text' => __('Locations targeting...', 'W2DC'),
						'map_style_name' => get_option('w2dc_map_style'),
						'map_markers_array' => w2dc_get_fa_icons_names(),
						'map_styles' => $w2dc_maps_styles,
						'address_autocomplete_code' => get_option('w2dc_address_autocomplete_code'),
				)
		) . ';
';
		echo '</script>
';
	}

	public function generate_color_palette() {
		ob_start();
		include W2DC_PATH . '/classes/customization/dynamic_css.php';
		$dynamic_css = ob_get_contents();
		ob_get_clean();

		echo $dynamic_css;
		die();
	}

	public function get_jqueryui_theme() {
		global $w2dc_color_schemes;

		if (isset($_COOKIE['w2dc_compare_palettes']) && get_option('w2dc_compare_palettes')) {
			$scheme = $_COOKIE['w2dc_compare_palettes'];
			if ($scheme && isset($w2dc_color_schemes[$scheme]['w2dc_jquery_ui_schemas']))
				echo '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/' . $w2dc_color_schemes[$scheme]['w2dc_jquery_ui_schemas'] . '/jquery-ui.css';
		}
		die();
	}
	
	public function remove_colorpicker_cookie($opt) {
		if (isset($_COOKIE['w2dc_compare_palettes'])) {
			unset($_COOKIE['w2dc_compare_palettes']);
			setcookie('w2dc_compare_palettes', null, -1, '/');
		}
	}

	public function render_colorpicker() {
		global $w2dc_instance;

		if (!empty($w2dc_instance->frontend_controllers))
			if (get_option('w2dc_compare_palettes'))
				if (current_user_can('manage_options'))
					w2dc_renderTemplate('color_picker/color_picker_panel.tpl.php');
	}

	// adapted for Relevanssi
	// remove our shortcodes because it causes problems while Relevanssi indexing
	/* public function relevanssi_add_disable_shortcodes() {
		global $w2dc_shortcodes, $w2dc_shortcodes_init;

		if (function_exists('relevanssi_do_query')) {
			$shortcodes = explode(',', get_option('relevanssi_disable_shortcodes'));
			if (!in_array(W2DC_MAIN_SHORTCODE, (array) $shortcodes)) {
				foreach (array_keys($w2dc_shortcodes) AS $shortcode)
					$shortcodes[] = $shortcode;
				foreach (array_keys($w2dc_shortcodes_init) AS $shortcode)
					$shortcodes[] = $shortcode;
				update_option('relevanssi_disable_shortcodes', implode(',', $shortcodes));
			}
		}
	} */
	
	/**
	 * Do not clear destination folder, there may be custom files and templates
	 * 
	 * @param array $options
	 * @return array
	 */
	public function update_does_not_clear_destination($options) {
		if (strpos($options['package'], 'web-20-directory-plugin-for-wordpress') !== false) {
			$options['clear_destination'] = false;
		}
		return $options;
	}
}
?>