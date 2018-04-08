<?php
/*
Plugin Name: Web 2.0 Directory plugin
Plugin URI: http://www.salephpscripts.com/wordpress_directory/
Description: Provides an ability to build any kind of directory site: classifieds, events directory, cars, bikes, boats and other vehicles dealers site, pets, real estate portal on your WordPress powered site. In other words - whatever you want.
Version: 1.14.3
Author: salephpscripts.com
Author URI: http://www.salephpscripts.com
License: GPLv2 or any later version
*/

define('W2DC_VERSION', '1.14.3');

define('W2DC_PATH', plugin_dir_path(__FILE__));
define('W2DC_URL', plugins_url('/', __FILE__));

define('W2DC_TEMPLATES_PATH', W2DC_PATH . 'templates/');

define('W2DC_RESOURCES_PATH', W2DC_PATH . 'resources/');
define('W2DC_RESOURCES_URL', W2DC_URL . 'resources/');

// Categories icons constant
define('W2DC_CATEGORIES_ICONS_PATH', W2DC_RESOURCES_PATH . 'images/categories_icons/');
define('W2DC_CATEGORIES_ICONS_URL', W2DC_RESOURCES_URL . 'images/categories_icons/');

// Categories icons constant
define('W2DC_LOCATION_ICONS_PATH', W2DC_RESOURCES_PATH . 'images/locations_icons/');
define('W2DC_LOCATIONS_ICONS_URL', W2DC_RESOURCES_URL . 'images/locations_icons/');

// Map Markers Icons Path
define('W2DC_MAP_ICONS_PATH', W2DC_RESOURCES_PATH . 'images/map_icons/');
define('W2DC_MAP_ICONS_URL', W2DC_RESOURCES_URL . 'images/map_icons/');

define('W2DC_POST_TYPE', 'w2dc_listing');
define('W2DC_CATEGORIES_TAX', 'w2dc-category');
define('W2DC_LOCATIONS_TAX', 'w2dc-location');
define('W2DC_TAGS_TAX', 'w2dc-tag');

// Deactivate deprecated modules
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
$fsubmit =  'w2dc_fsubmit/w2dc_fsubmit.php';
if (is_plugin_active($fsubmit)) {
	add_option('w2dc_fsubmit_addon', 1);
	deactivate_plugins($fsubmit);
	exit('Press F5!!!');
}
$payments =  'w2dc_payments/w2dc_payments.php';
if (is_plugin_active($payments)) {
	add_option('w2dc_payments_addon', 1);
	deactivate_plugins($payments);
	exit('Press F5!!!');
}
$ratings =  'w2dc_ratings/w2dc_ratings.php';
if (is_plugin_active($ratings)) {
	add_option('w2dc_ratings_addon', 1);
	deactivate_plugins($ratings);
	exit('Press F5!!!');
}
$elocations =  'w2dc_elocations/w2dc_elocations.php';
if (is_plugin_active($elocations)) {
	deactivate_plugins($elocations);
	exit('Press F5!!!');
}
$esearch =  'w2dc_esearch/w2dc_esearch.php';
if (is_plugin_active($esearch)) {
	deactivate_plugins($esearch);
	exit('Press F5!!!');
}

include_once W2DC_PATH . 'install.php';
include_once W2DC_PATH . 'classes/admin.php';
include_once W2DC_PATH . 'classes/form_validation.php';
include_once W2DC_PATH . 'classes/listings/listings_manager.php';
include_once W2DC_PATH . 'classes/listings/listing.php';
include_once W2DC_PATH . 'classes/categories_manager.php';
include_once W2DC_PATH . 'classes/media_manager.php';
include_once W2DC_PATH . 'classes/content_fields/content_fields_manager.php';
include_once W2DC_PATH . 'classes/content_fields/content_fields.php';
include_once W2DC_PATH . 'classes/locations/locations_manager.php';
include_once W2DC_PATH . 'classes/locations/locations_levels_manager.php';
include_once W2DC_PATH . 'classes/locations/locations_levels.php';
include_once W2DC_PATH . 'classes/locations/location.php';
include_once W2DC_PATH . 'classes/levels/levels_manager.php';
include_once W2DC_PATH . 'classes/levels/levels.php';
include_once W2DC_PATH . 'classes/directories/directories_manager.php';
include_once W2DC_PATH . 'classes/directories/directories.php';
include_once W2DC_PATH . 'classes/frontend_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/directory_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/listings_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/map_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/categories_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/locations_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/search_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/slider_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/buttons_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/term_description_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/breadcrumbs_controller.php';
include_once W2DC_PATH . 'classes/ajax_controller.php';
include_once W2DC_PATH . 'classes/settings_manager.php';
include_once W2DC_PATH . 'classes/google_maps.php';
include_once W2DC_PATH . 'classes/widgets.php';
include_once W2DC_PATH . 'classes/csv_manager.php';
include_once W2DC_PATH . 'classes/location_geoname.php';
include_once W2DC_PATH . 'classes/search_form.php';
include_once W2DC_PATH . 'classes/search_map_form.php';
include_once W2DC_PATH . 'classes/search_fields/search_fields.php';
include_once W2DC_PATH . 'functions.php';
include_once W2DC_PATH . 'functions_ui.php';
include_once W2DC_PATH . 'maps_styles.php';
include_once W2DC_PATH . 'vc.php';
include_once W2DC_PATH . 'vafpress-framework/bootstrap.php';
include_once W2DC_PATH . 'classes/customization/color_schemes.php';
include_once W2DC_PATH . 'classes/maps_importer.php';

global $w2dc_instance;
global $w2dc_messages;

define('W2DC_MAIN_SHORTCODE', 'webdirectory');
define('W2DC_LISTING_SHORTCODE', 'webdirectory-listing-page');

/*
 * There are 2 types of shortcodes in the system:
 1. those process as simple wordpress shortcodes
 2. require initialization on 'wp' hook
 
 [webdirectory] shortcode must be initialized on 'wp' hook and then renders as simple shortcode
 */
global $w2dc_shortcodes, $w2dc_shortcodes_init;
$w2dc_shortcodes = array(
		W2DC_MAIN_SHORTCODE => 'w2dc_directory_controller',
		W2DC_LISTING_SHORTCODE => 'w2dc_directory_controller', // listings page
		'webdirectory-listing' => 'w2dc_directory_controller', // one single listing by ID
		'webdirectory-listings' => 'w2dc_listings_controller',
		'webdirectory-map' => 'w2dc_map_controller',
		'webdirectory-categories' => 'w2dc_categories_controller',
		'webdirectory-locations' => 'w2dc_locations_controller',
		'webdirectory-search' => 'w2dc_search_controller',
		'webdirectory-slider' => 'w2dc_slider_controller',
		'webdirectory-buttons' => 'w2dc_buttons_controller',
		'webdirectory-term-description' => 'w2dc_term_description_controller',
		'webdirectory-breadcrumbs' => 'w2dc_breadcrumbs_controller',
);
$w2dc_shortcodes_init = array(
		W2DC_MAIN_SHORTCODE => 'w2dc_directory_controller',
		W2DC_LISTING_SHORTCODE => 'w2dc_directory_controller', // listings page
		'webdirectory-listing' => 'w2dc_directory_controller', // one single listing by ID
		'webdirectory-listings' => 'w2dc_listings_controller',
);

class w2dc_plugin {
	public $admin;
	public $listings_manager;
	public $locations_manager;
	public $locations_levels_manager;
	public $categories_manager;
	public $content_fields_manager;
	public $media_manager;
	public $settings_manager;
	public $directories_manager;
	public $levels_manager;
	public $csv_manager;

	public $current_directory = null;
	public $directories;
	public $current_listing; // this is object of listing under edition right now
	public $levels;
	public $locations_levels;
	public $content_fields;
	public $search_fields;
	public $ajax_controller;
	public $index_page_id;
	public $index_page_slug;
	public $index_page_url;
	public $index_pages_all = array();
	public $original_index_page_id;
	public $listing_page_id;
	public $listing_page_slug;
	public $listing_page_url;
	public $frontend_controllers = array();
	public $_frontend_controllers = array(); // this duplicate property needed because we unset each controller when we render shortcodes, but WP doesn't really know which shortcode already was processed
	public $action;
	
	public $radius_values_array = array();
	
	public $order_by_date = false; // special flag, used to display or hide sticky pin

	public function __construct() {
		register_activation_hook(__FILE__, array($this, 'activation'));
		register_deactivation_hook(__FILE__, array($this, 'deactivation'));
	}
	
	public function activation() {
		global $wp_version;

		if (version_compare($wp_version, '3.6', '<')) {
			deactivate_plugins(basename(__FILE__)); // Deactivate ourself
			wp_die("Sorry, but you can't run this plugin on current WordPress version, it requires WordPress v3.6 or higher.");
		}
		flush_rewrite_rules();
		
		wp_schedule_event(current_time('timestamp'), 'hourly', 'scheduled_events');
	}

	public function deactivation() {
		flush_rewrite_rules();

		wp_clear_scheduled_hook('scheduled_events');
	}
	
	public function init() {
		global $w2dc_instance, $w2dc_shortcodes, $wpdb;

		$_GET = stripslashes_deep($_GET);
		if (isset($_REQUEST['w2dc_action']))
			$this->action = $_REQUEST['w2dc_action'];

		add_action('plugins_loaded', array($this, 'load_textdomains'));

		if (!isset($wpdb->w2dc_content_fields))
			$wpdb->w2dc_content_fields = $wpdb->prefix . 'w2dc_content_fields';
		if (!isset($wpdb->w2dc_content_fields_groups))
			$wpdb->w2dc_content_fields_groups = $wpdb->prefix . 'w2dc_content_fields_groups';
		if (!isset($wpdb->w2dc_directories))
			$wpdb->w2dc_directories = $wpdb->prefix . 'w2dc_directories';
		if (!isset($wpdb->w2dc_levels))
			$wpdb->w2dc_levels = $wpdb->prefix . 'w2dc_levels';
		if (!isset($wpdb->w2dc_levels_relationships))
			$wpdb->w2dc_levels_relationships = $wpdb->prefix . 'w2dc_levels_relationships';
		if (!isset($wpdb->w2dc_locations_levels))
			$wpdb->w2dc_locations_levels = $wpdb->prefix . 'w2dc_locations_levels';
		if (!isset($wpdb->w2dc_locations_relationships))
			$wpdb->w2dc_locations_relationships = $wpdb->prefix . 'w2dc_locations_relationships';

		add_action('scheduled_events', array($this, 'suspend_expired_listings'));
		
		foreach ($w2dc_shortcodes AS $shortcode=>$function)
			add_shortcode($shortcode, array($this, 'renderShortcode'));
		
		add_action('init', array($this, 'register_post_type'), 0);
		add_action('wp', array($this, 'loadPagesDirectories'), 1);
		add_action('admin_init', array($this, 'loadPagesDirectories'), 1);
		//add_action('wp', array($this, 'getIndexPage'), 1);
		add_action('wp', array($this, 'checkMainShortcode'), 1);

		//add_action('wp', array($this, 'setCurrentDirectory'), 1);
		add_action('wp', array($this, 'loadFrontendControllers'), 1);

		if (!get_option('w2dc_installed_directory') || get_option('w2dc_installed_directory_version') != W2DC_VERSION) {
			if (get_option('w2dc_installed_directory'))
				$this->loadClasses();

			add_action('init', 'w2dc_install_directory', 0);
		} else {
			$this->loadClasses();
		}

		add_filter('template_include', array($this, 'printlisting_template'), 100000);

		add_action('wp', array($this, 'wp_loaded'));
		add_filter('query_vars', array($this, 'add_query_vars'));
		add_filter('rewrite_rules_array', array($this, 'rewrite_rules'));
		
		add_filter('redirect_canonical', array($this, 'prevent_wrong_redirect'), 10, 2);
		add_filter('post_type_link', array($this, 'listing_permalink'), 10, 3);
		add_filter('term_link', array($this, 'category_permalink'), 10, 3);
		add_filter('term_link', array($this, 'location_permalink'), 10, 3);
		add_filter('term_link', array($this, 'tag_permalink'), 10, 3);

		// adapted for Polylang
		add_action('init', array($this, 'pll_setup'));

		add_filter('comments_open', array($this, 'filter_comment_status'), 100, 2);
		
		add_filter('wp_unique_post_slug_is_bad_flat_slug', array($this, 'reserve_slugs'), 10, 2);
		
		add_filter('no_texturize_shortcodes', array($this, 'w2dc_no_texturize'));

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'));
		add_action('wp_head', array($this, 'enqueue_dynamic_css'), 9999);
	}

	public function load_textdomains() {
		load_plugin_textdomain('W2DC', '', dirname(plugin_basename( __FILE__ )) . '/languages');
	}
	
	public function loadClasses() {
		$this->directories = new w2dc_directories;
		$this->levels = new w2dc_levels;
		$this->locations_levels = new w2dc_locations_levels;
		$this->content_fields = new w2dc_content_fields;
		$this->search_fields = new w2dc_search_fields;
		$this->ajax_controller = new w2dc_ajax_controller;
		$this->admin = new w2dc_admin();
	}

	public function w2dc_no_texturize($shortcodes) {
		global $w2dc_shortcodes;
		
		foreach ($w2dc_shortcodes AS $shortcode=>$function)
			$shortcodes[] = $shortcode;
		
		return $shortcodes;
	}

	public function renderShortcode() {
		global $w2dc_shortcodes;

		// remove content filters in order not to break the layout of page
		remove_filter('the_content', 'wpautop');
		remove_filter('the_content', 'wptexturize');
		remove_filter('the_content', 'shortcode_unautop');
		remove_filter('the_content', 'convert_chars');
		remove_filter('the_content', 'prepend_attachment');
		remove_filter('the_content', 'convert_smilies');

		$attrs = func_get_args();
		$shortcode = $attrs[2];

		$filters_where_not_to_display = array(
				'wp_head',
				'init',
				'wp',
				'edit_attachment',
		);
		
		//var_dump(current_filter());
		if (isset($this->_frontend_controllers[$shortcode]) && !in_array(current_filter(), $filters_where_not_to_display)) {
			$shortcode_controllers = $this->_frontend_controllers[$shortcode];
			foreach ($shortcode_controllers AS $key=>&$controller) {
				unset($this->_frontend_controllers[$shortcode][$key]); // there are possible more than 1 same shortcodes on a page, so we have to unset which already was displayed
				if (method_exists($controller, 'display'))
					return $controller->display();
			}
		}

		if (isset($w2dc_shortcodes[$shortcode])) {
			$shortcode_class = $w2dc_shortcodes[$shortcode];
			if ($attrs[0] === '')
				$attrs[0] = array();
			$shortcode_instance = new $shortcode_class();
			$this->frontend_controllers[$shortcode][] = $shortcode_instance;
			$shortcode_instance->init($attrs[0], $shortcode);

			if (method_exists($shortcode_instance, 'display'))
				return $shortcode_instance->display();
		}
	}

	public function loadFrontendControllers() {
		global $post, $wp_query;

		if ($wp_query->posts) {
			$pattern = get_shortcode_regex();
			foreach ($wp_query->posts AS $archive_post) {
				if (isset($archive_post->post_content))
					$this->loadNestedFrontendController($pattern, $archive_post->post_content);
			}
		} elseif ($post && isset($post->post_content)) {
			$pattern = get_shortcode_regex();
			$this->loadNestedFrontendController($pattern, $post->post_content);
		}
	}

	// this may be recursive function to catch nested shortcodes
	public function loadNestedFrontendController($pattern, $content) {
		global $w2dc_shortcodes_init, $w2dc_shortcodes;

		if (preg_match_all('/'.$pattern.'/s', $content, $matches) && array_key_exists(2, $matches)) {
			foreach ($matches[2] AS $key=>$shortcode) {
				if ($shortcode != 'shortcodes') {
					if (isset($w2dc_shortcodes_init[$shortcode]) && class_exists($w2dc_shortcodes_init[$shortcode])) {
						$shortcode_class = $w2dc_shortcodes_init[$shortcode];
						if (!($attrs = shortcode_parse_atts($matches[3][$key])))
							$attrs = array();
						$shortcode_instance = new $shortcode_class();
						$this->frontend_controllers[$shortcode][] = $shortcode_instance;
						$this->_frontend_controllers[$shortcode][] = $shortcode_instance;
						$shortcode_instance->init($attrs, $shortcode);
					} elseif (isset($w2dc_shortcodes[$shortcode]) && class_exists($w2dc_shortcodes[$shortcode])) {
						$shortcode_class = $w2dc_shortcodes[$shortcode];
						$this->frontend_controllers[$shortcode][] = $shortcode_class;
					}
					if ($shortcode_content = $matches[5][$key])
						$this->loadNestedFrontendController($pattern, $shortcode_content);
				}
			}
		}
	}
	
	public function loadPagesDirectories() {
		$this->getIndexPage();
		$this->setCurrentDirectory();
		
		do_action('w2dc_load_pages_directories');
	}
	
	public function checkMainShortcode() {
		if ($this->index_page_id === 0 && is_admin())
			w2dc_addMessage(sprintf(__("<b>Web 2.0 Directory plugin</b>: sorry, but there isn't any page with [webdirectory] shortcode. Create <a href=\"%s\">this special page</a> for you?", 'W2DC'), admin_url('admin.php?page=w2dc_settings&action=directory_page_installation')));
		
		if (w2dc_is_maps_used() && (!get_option('w2dc_google_api_key') || !get_option('w2dc_google_api_key_server')) && is_admin())
			w2dc_addMessage(sprintf(__("<b>Web 2.0 Directory plugin</b>: since 22 June 2016 Google requires mandatory Maps API keys for maps created on NEW websites/domains. Please, <a href=\"http://www.salephpscripts.com/wordpress_directory/demo/documentation/#google_maps_keys\" target=\"_blank\">follow instructions</a> and enter API keys on <a href=\"%s\">directory settings page</a>. Otherwise it may cause problems with Google Maps, Geocoding, addition/edition listings locations, autocomplete on addresses fields.", 'W2DC'), admin_url('admin.php?page=w2dc_settings#_advanced')));
	}

	public function getIndexPage() {
		if ($array = w2dc_getIndexPage()) {
			$this->index_page_id = $array['id'];
			$this->index_page_slug = $array['slug'];
			$this->index_page_url = $array['url'];
		}

		if ($array = w2dc_getTemplatePage(W2DC_LISTING_SHORTCODE)) {
			// backward compatibility with [webdirectory-listing] shortcode from previous version
			if (!$array['id']) {
				$array = w2dc_getTemplatePage('webdirectory-listing');
			}
			$this->listing_page_id = $array['id'];
			$this->listing_page_slug = $array['slug'];
			$this->listing_page_url = $array['url'];

		}
	}
	
	public function setCurrentDirectory() {
		global $pagenow;
		
		$current_directory = null;

		if (isset($_GET['directory']) && is_numeric($_GET['directory']) && ($directory = $this->directories->getDirectoryById($_GET['directory']))) {
			$current_directory = $directory;
		}
		if (is_admin() && $pagenow == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == W2DC_POST_TYPE && isset($_GET['directory_id']) && is_numeric($_GET['directory_id']) && ($directory = $this->directories->getDirectoryById($_GET['directory_id']))) {
			$current_directory = $directory;
		}

		if (!$current_directory && $this->frontend_controllers && ($listing = w2dc_is_listing())) {
			$current_directory = $listing->directory;
		}

		if (!$current_directory && get_query_var('directory-w2dc')) {
			$current_directory = $this->directories->getDirectoryById(get_query_var('directory-w2dc'));
		}

		if (!$current_directory) {
			$this->getIndexPage();
			
			// If current page is not webdirectory index page, then pass, and make current directory = default directory
			if (($this->index_page_id == get_queried_object_id()) || (defined('DOING_AJAX') && DOING_AJAX)) {
				$current_directory = $this->directories->getDirectoryOfPage($this->index_page_id);
			}
		}
		if (!$current_directory) {
			$current_directory = $this->directories->getDefaultDirectory();
		}
		return ($this->current_directory = $current_directory);
	}

	public function add_query_vars($vars) {
		$vars[] = 'directory-w2dc';
		$vars[] = 'listing-w2dc';
		$vars[] = 'category-w2dc';
		$vars[] = 'location-w2dc';
		$vars[] = 'tag-w2dc';
		$vars[] = 'tax-slugs-w2dc';
		$vars[] = 'homepage';

		if (!is_admin()) {
			// order query var may damage sorting of listings at the frontend - it shows WP posts instead of directory listings
			$key = array_search('order', $vars);
			unset($vars[$key]);
		}

		return $vars;
	}
	
	public function rewrite_rules($rules) {
		return $this->w2dc_addRules() + $rules;
	}
	
	public function w2dc_addRules() {
		$rules = array();
		foreach ($this->index_pages_all AS $page) {
			$this->index_page_id = $page['id'];
			$this->index_page_slug = $page['slug'];
			$this->index_page_url = get_permalink($page['id']);
				
			// adapted for WPML
			global $sitepress;
			if (function_exists('wpml_object_id_filter') && $sitepress && ($languages = $sitepress->get_active_languages()) && count($languages) > 1) {
				$this->original_index_page_id = $this->index_page_id;
				$original_listing_page_id = $this->listing_page_id;
				foreach ($languages AS $lang_code=>$lang) {
					if ($this->index_page_id = apply_filters('wpml_object_id', $this->original_index_page_id, 'page', false, $lang_code)) {
						$post = get_post($this->index_page_id);
						$this->index_page_slug = $post->post_name;
						$this->listing_page_id = apply_filters('wpml_object_id', $original_listing_page_id, 'page', true, $lang_code);
	
						$rules = $rules + $this->buildRules($lang_code);
					}
				}
				//$this->getIndexPage();
				//return $rules;
			} else {
				$rules = $rules + $this->buildRules();
			}
		}
		$this->getIndexPage();
		return $rules;
	}
	
	public function buildRules($lang_code = '') {
		// adapted for WPML
		//
		// If it was set to use different languages in directories ((http://wp/ - English, http://wp/it/ - Italian)),
		// WPML removes this directory from home page url and we can not match "language-based" rewrite rule by exact request - 
		// home_url() simply gives path without directory and $wp->parse_request() does not see any difference between language URLs,
		// so we have to build rules "on the fly" for each switching of language.
		// The last chance we might have when webdirectory page could not be home page.
		global $sitepress;
		if (function_exists('wpml_object_id_filter') && $sitepress) {
			if (
			$sitepress->get_setting('language_negotiation_type') == 1 &&
			$lang_code != ICL_LANGUAGE_CODE &&
			get_option('show_on_front') != 'posts' &&
			get_option('page_on_front') == $this->original_index_page_id
			) {
				return array();
			}
		}
		
		global $wp_rewrite;

		$lang_param = '';

		// adapted for WPML
		global $sitepress;
		if ($lang_code && function_exists('wpml_object_id_filter') && $sitepress) {
			if ($sitepress->get_setting('language_negotiation_type') == 3 && $lang_code != $sitepress->get_default_language()) {
				$lang_param = '\?lang='.$lang_code;
			}
		}

		$page_url = $this->index_page_slug;

		foreach (get_post_ancestors($this->index_page_id) AS $parent_id) {
			$parent = get_page($parent_id);
			$page_url = $parent->post_name . '/' . $page_url;
		}
		
		$rules['(' . $page_url . ')/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?' . $lang_param . '$'] = 'index.php?page_id=' .  $this->index_page_id . '&paged=$matches[2]';
		$rules['(' . $page_url . ')/?' . $lang_param . '$'] = 'index.php?page_id=' .  $this->index_page_id;
		
		$category_page_id = $this->index_page_id;
		$location_page_id = $this->index_page_id;
		$tag_page_id = $this->index_page_id;
		
		if ($this->listing_page_id)
			$listing_page_id = $this->listing_page_id;
		else
			$listing_page_id = $this->index_page_id;
		
		foreach ($this->directories->directories_array AS $directory) {
			$listing_slug = $directory->listing_slug;
			$category_slug = $directory->category_slug;
			$location_slug = $directory->location_slug;
			$tag_slug = $directory->tag_slug;

			$rules['(' . $page_url . ')?/?' . $category_slug . '/(.+?)/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?' . $lang_param . '$'] = 'index.php?page_id=' .  $category_page_id . '&category-w2dc=$matches[2]&paged=$matches[3]';
			$rules['(' . $page_url . ')?/?' . $category_slug . '/(.+?)/?' . $lang_param . '$'] = 'index.php?page_id=' .  $category_page_id . '&category-w2dc=$matches[2]&directory-w2dc=' . $directory->id;
	
			$rules['(' . $page_url . ')?/?' . $location_slug . '/(.+?)/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?' . $lang_param . '$'] = 'index.php?page_id=' .  $location_page_id . '&location-w2dc=$matches[2]&paged=$matches[3]';
			$rules['(' . $page_url . ')?/?' . $location_slug . '/(.+?)/?' . $lang_param . '$'] = 'index.php?page_id=' .  $location_page_id . '&location-w2dc=$matches[2]&directory-w2dc=' . $directory->id;
	
			$rules['(' . $page_url . ')?/?' . $tag_slug . '/([^\/.]+)/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?' . $lang_param . '$'] = 'index.php?page_id=' .  $tag_page_id . '&tag-w2dc=$matches[2]&paged=$matches[3]';
			$rules['(' . $page_url . ')?/?' . $tag_slug . '/([^\/.]+)/?' . $lang_param . '$'] = 'index.php?page_id=' .  $tag_page_id . '&tag-w2dc=$matches[2]&directory-w2dc=' . $directory->id;

			// here directory ID we will take from post meta
			$rules['(' . $page_url . ')?/?' . $listing_slug . '/(.+?)/([^\/.]+)/?' . $lang_param . '$'] = 'index.php?page_id=' . $listing_page_id . '&tax-slugs-w2dc=$matches[2]&listing-w2dc=$matches[3]';
			$rules['(' . $page_url . ')?/?' . $listing_slug . '/([^\/.]+)/?' . $lang_param . '$'] = 'index.php?page_id=' . $listing_page_id . '&listing-w2dc=$matches[2]';
		}
		
		$rules[$page_url . '/([^\/.]+)/?' . $lang_param . '$'] = 'index.php?page_id=' . $listing_page_id . '&listing-w2dc=$matches[1]';
		if (strpos(get_option('permalink_structure'), '/%post_id%/%postname%') === FALSE) {
			// /%post_id%/%postname%/ will not work when the same structure enabled for native WP posts
			// also avoid mismatches with archive pages with /%year%/%monthnum%/ permalinks structure
			$rules['(' . $page_url . ')?/?(?!(?:199[0-9]|20[012][0-9])/(?:0[1-9]|1[012]))([0-9]+)/([^\/.]+)/?' . $lang_param . '$'] = 'index.php?page_id=' . $listing_page_id . '&listing-w2dc=$matches[3]';
		}
		
		return $rules;
	}
	
	public function wp_loaded() {
		if ($rules = get_option('rewrite_rules'))
			foreach ($this->w2dc_addRules() as $key=>$value) {
				if (!isset($rules[$key]) || $rules[$key] != $value) {
					global $wp_rewrite;
					$wp_rewrite->flush_rules();
					return;
				}
			}
	}
	
	public function prevent_wrong_redirect($redirect_url, $requested_url) {
		
		if ($this->frontend_controllers) {
			// add/remove www. into/from $requested_url when needed
			$user_home = @parse_url(home_url());
			if (!empty($user_home['host'])) {
				if (strpos($user_home['host'], 'www.') === 0) {
					$requested_home = @parse_url($requested_url);
					if (!empty($requested_home['host'])) {
						if (strpos($requested_home['host'], 'www.') !== 0) {
							$requested_url = str_replace($requested_home['host'], 'www.'.$requested_home['host'], $requested_url);
						}
					}
				} else {
					$requested_home = @parse_url($requested_url);
					if (!empty($requested_home['host'])) {
						if (strpos($requested_home['host'], 'www.') === 0) {
							$pos = strpos($requested_url, 'www.');
							$requested_url = substr_replace($requested_url, '', $pos, 4);
						}
					}
				}
			}
			return $requested_url;
		}
	
		return $redirect_url;
	}

	public function listing_permalink($permalink, $post, $leavename) {
		if ($post->post_type == W2DC_POST_TYPE) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks()) {
				if ($leavename)
					$postname = '%postname%';
				else
					$postname = $post->post_name;

				/* if ($this->current_directory) {
					$listing_slug = $this->current_directory->listing_slug;
				} else {
					$listing_slug = get_option('w2dc_listing_slug');
				} */
				$listing_directory = w2dc_getListingDirectory($post->ID);
				$listing_slug = $listing_directory->listing_slug;

				switch (get_option('w2dc_permalinks_structure')) {
					case 'post_id':
						return w2dc_directoryUrl($post->ID . '/' . $postname, $listing_directory);
						break;
					case 'postname':
						if (get_option('page_on_front') == $this->index_page_id)
							return w2dc_directoryUrl($post->ID . '/' . $postname, $listing_directory);
						else
							return w2dc_directoryUrl($postname, $listing_directory);
						break;
					case 'listing_slug':
						if ($listing_slug)
							return w2dc_directoryUrl($listing_slug . '/' . $postname, $listing_directory);
						else
							if (get_option('page_on_front') == $this->index_page_id)
								return w2dc_directoryUrl($post->ID . '/' . $postname, $listing_directory);
							else
								return w2dc_directoryUrl($postname, $listing_directory);
						break;
					case 'category_slug':
						if ($listing_slug && ($terms = get_the_terms($post->ID, W2DC_CATEGORIES_TAX))) {
							$term = array_shift($terms);
							if ($cur_term = w2dc_get_term_by_path(get_query_var('category-w2dc'))) {
								foreach ($terms AS $lterm) {
									$term_path_ids = w2dc_get_term_parents_ids($lterm->term_id, W2DC_CATEGORIES_TAX);
									if ($cur_term->term_id == $lterm->term_id) { $term = $lterm; break; }  // exact term much more better
									if (in_array($cur_term->term_id, $term_path_ids)) { $term = $lterm; break; }
								}
							}
							$uri = '';
							if ($parents = w2dc_get_term_parents_slugs($term->term_id, W2DC_CATEGORIES_TAX))
								$uri = implode('/', $parents);
							return w2dc_directoryUrl($listing_slug . '/' . $uri . '/' . $postname, $listing_directory);
						} else
							if (get_option('page_on_front') == $this->index_page_id)
								return w2dc_directoryUrl($post->ID . '/' . $postname, $listing_directory);
							else
								return w2dc_directoryUrl($postname, $listing_directory);
						break;
					case 'location_slug':
						if ($listing_slug && ($terms = get_the_terms($post->ID, W2DC_LOCATIONS_TAX)) && ($term = array_shift($terms))) {
							if ($cur_term = w2dc_get_term_by_path(get_query_var('location-w2dc'))) {
								foreach ($terms AS $lterm) {
									$term_path_ids = w2dc_get_term_parents_ids($lterm->term_id, W2DC_LOCATIONS_TAX);
									if ($cur_term->term_id == $lterm->term_id) { $term = $lterm; break; }  // exact term much more better
									if (in_array($cur_term->term_id, $term_path_ids)) { $term = $lterm; break; }
								}
							}
							$uri = '';
							if ($parents = w2dc_get_term_parents_slugs($term->term_id, W2DC_LOCATIONS_TAX))
								$uri = implode('/', $parents);
							return w2dc_directoryUrl($listing_slug . '/' . $uri . '/' . $postname, $listing_directory);
						} else {
							if (get_option('page_on_front') == $this->index_page_id)
								return w2dc_directoryUrl($post->ID . '/' . $postname, $listing_directory);
							else
								return w2dc_directoryUrl($postname, $listing_directory);
						}
						break;
					case 'tag_slug':
						if ($listing_slug && ($terms = get_the_terms($post->ID, W2DC_TAGS_TAX)) && ($term = array_shift($terms))) {
							return w2dc_directoryUrl($listing_slug . '/' . $term->slug . '/' . $postname, $listing_directory);
						} else
							if (get_option('page_on_front') == $this->index_page_id)
								return w2dc_directoryUrl($post->ID . '/' . $postname, $listing_directory);
							else
								return w2dc_directoryUrl($postname, $listing_directory);
						break;
					default:
						if (get_option('page_on_front') == $this->index_page_id)
							return w2dc_directoryUrl($post->ID . '/' . $postname, $listing_directory);
						else
							return w2dc_directoryUrl($postname, $listing_directory);
				}
			} else
				return w2dc_templatePageUri(array('listing-w2dc' => $post->post_name), $this->listing_page_url);
		}
		return $permalink;
	}

	public function category_permalink($permalink, $category, $tax) {
		if ($tax == W2DC_CATEGORIES_TAX) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks()) {
				/* if ($this->current_directory) {
					$category_slug = $this->current_directory->category_slug;
				} else {
					$category_slug = get_option('w2dc_category_slug');
				} */
				global $w2dc_directory_flag;
				if ($w2dc_directory_flag) {
					$directory = $this->directories->getDirectoryById($w2dc_directory_flag);
				} else {
					$directory = w2dc_getListingDirectory(get_the_ID());
				}
				$category_slug = $directory->category_slug;

				$uri = '';
				if ($parents = w2dc_get_term_parents_slugs($category->term_id, W2DC_CATEGORIES_TAX))
					$uri = implode('/', $parents);
				return w2dc_directoryUrl($category_slug . '/' . $uri, $directory);
			} else
				return w2dc_templatePageUri(array('category-w2dc' => $category->slug), $this->index_page_url);
		}
		return $permalink;
	}

	public function location_permalink($permalink, $location, $tax) {
		if ($tax == W2DC_LOCATIONS_TAX) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks()) {
				/* if ($this->current_directory) {
					$location_slug = $this->current_directory->location_slug;
				} else {
					$location_slug = get_option('w2dc_location_slug');
				} */
				global $w2dc_directory_flag;
				if ($w2dc_directory_flag) {
					$directory = $this->directories->getDirectoryById($w2dc_directory_flag);
				} else {
					$directory = w2dc_getListingDirectory(get_the_ID());
				}
				$location_slug = $directory->location_slug;

				$uri = '';
				if ($parents = w2dc_get_term_parents_slugs($location->term_id, W2DC_LOCATIONS_TAX))
					$uri = implode('/', $parents);
				return w2dc_directoryUrl($location_slug . '/' . $uri, $directory);
			} else
				return w2dc_templatePageUri(array('location-w2dc' => $location->slug), $this->index_page_url);
		}
		return $permalink;
	}

	public function tag_permalink($permalink, $tag, $tax) {
		if ($tax == W2DC_TAGS_TAX) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks()) {
				/* if ($this->current_directory) {
					$tag_slug = $this->current_directory->tag_slug;
				} else {
					$tag_slug = get_option('w2dc_tag_slug');
				} */
				$tag_slug = w2dc_getListingDirectory(get_the_ID())->tag_slug;

				return w2dc_directoryUrl($tag_slug . '/' . $tag->slug, $directory);
			} else {
				return w2dc_templatePageUri(array('tag-w2dc' => $tag->slug), $this->index_page_url);
			}
		}
		return $permalink;
	}
	
	public function reserve_slugs($is_bad_flat_slug, $slug) {
		$slugs_to_check = array();
		foreach ($this->directories->directories_array AS $directory) {
			$slugs_to_check[] = $directory->listing_slug;
			$slugs_to_check[] = $directory->category_slug;
			$slugs_to_check[] = $directory->location_slug;
			$slugs_to_check[] = $directory->tag_slug;
		}

		if (in_array($slug, $slugs_to_check))
			return true;
		return $is_bad_flat_slug;
	}

	public function register_post_type() {
		$args = array(
			'labels' => array(
				'name' => __('Directory listings', 'W2DC'),
				'singular_name' => __('Directory listing', 'W2DC'),
				'add_new' => __('Create new listing', 'W2DC'),
				'add_new_item' => __('Create new listing', 'W2DC'),
				'edit_item' => __('Edit listing', 'W2DC'),
				'new_item' => __('New listing', 'W2DC'),
				'view_item' => __('View listing', 'W2DC'),
				'search_items' => __('Search listings', 'W2DC'),
				'not_found' =>  __('No listings found', 'W2DC'),
				'not_found_in_trash' => __('No listings found in trash', 'W2DC')
			),
			'has_archive' => true,
			'description' => __('Directory listings', 'W2DC'),
			'public' => true,
			'exclude_from_search' => false, // this must be false otherwise it breaks pagination for custom taxonomies
			'supports' => array('title', 'author', 'comments'),
			'menu_icon' => W2DC_RESOURCES_URL . 'images/menuicon.png',
		);
		if (get_option('w2dc_enable_description'))
			$args['supports'][] = 'editor';
		if (get_option('w2dc_enable_summary'))
			$args['supports'][] = 'excerpt';
		register_post_type(W2DC_POST_TYPE, $args);
		
		register_taxonomy(W2DC_CATEGORIES_TAX, W2DC_POST_TYPE, array(
				'hierarchical' => true,
				'has_archive' => true,
				'labels' => array(
					'name' =>  __('Listing categories', 'W2DC'),
					'menu_name' =>  __('Directory categories', 'W2DC'),
					'singular_name' => __('Category', 'W2DC'),
					'add_new_item' => __('Create category', 'W2DC'),
					'new_item_name' => __('New category', 'W2DC'),
					'edit_item' => __('Edit category', 'W2DC'),
					'view_item' => __('View category', 'W2DC'),
					'update_item' => __('Update category', 'W2DC'),
					'search_items' => __('Search categories', 'W2DC'),
				),
			)
		);
		register_taxonomy(W2DC_LOCATIONS_TAX, W2DC_POST_TYPE, array(
				'hierarchical' => true,
				'has_archive' => true,
				'labels' => array(
					'name' =>  __('Listing locations', 'W2DC'),
					'menu_name' =>  __('Directory locations', 'W2DC'),
					'singular_name' => __('Location', 'W2DC'),
					'add_new_item' => __('Create location', 'W2DC'),
					'new_item_name' => __('New location', 'W2DC'),
					'edit_item' => __('Edit location', 'W2DC'),
					'view_item' => __('View location', 'W2DC'),
					'update_item' => __('Update location', 'W2DC'),
					'search_items' => __('Search locations', 'W2DC'),
					
				),
			)
		);
		register_taxonomy(W2DC_TAGS_TAX, W2DC_POST_TYPE, array(
				'hierarchical' => false,
				'labels' => array(
					'name' =>  __('Listing tags', 'W2DC'),
					'menu_name' =>  __('Directory tags', 'W2DC'),
					'singular_name' => __('Tag', 'W2DC'),
					'add_new_item' => __('Create tag', 'W2DC'),
					'new_item_name' => __('New tag', 'W2DC'),
					'edit_item' => __('Edit tag', 'W2DC'),
					'view_item' => __('View tag', 'W2DC'),
					'update_item' => __('Update tag', 'W2DC'),
					'search_items' => __('Search tags', 'W2DC'),
				),
			)
		);
	}

	public function suspend_expired_listings() {
		global $wpdb;

		$posts_ids = $wpdb->get_col($wpdb->prepare("
				SELECT
					wp_pm1.post_id
				FROM
					{$wpdb->postmeta} AS wp_pm1
				LEFT JOIN
					{$wpdb->postmeta} AS wp_pm2 ON wp_pm1.post_id=wp_pm2.post_id
				LEFT JOIN
					{$wpdb->w2dc_levels_relationships} AS wp_lr ON wp_lr.post_id=wp_pm1.post_id
				LEFT JOIN
					{$wpdb->w2dc_levels} AS wp_l ON wp_l.id=wp_lr.level_id
				WHERE
					wp_pm1.meta_key = '_expiration_date' AND
					wp_pm1.meta_value < %d AND
					wp_pm2.meta_key = '_listing_status' AND
					(wp_pm2.meta_value = 'active' OR wp_pm2.meta_value = 'stopped') AND
					(wp_l.active_years != 0 OR wp_l.active_months != 0 OR wp_l.active_days != 0)
			", time()));
		$listings_ids_to_suspend = array();
		foreach ($posts_ids AS $post_id) {
			if ($post = get_post($post_id)) {
				if (get_option('w2dc_expiration_notification')) {
					$listing_owner = get_userdata($post->post_author);
			
					$subject = __('Expiration notification', 'W2DC');
			
					$body = str_replace('[listing]', $post->post_title,
							str_replace('[link]', ((get_option('w2dc_fsubmit_addon') && isset($this->dashboard_page_url) && $this->dashboard_page_url) ? w2dc_dashboardUrl(array('w2dc_action' => 'renew_listing', 'listing_id' => $post->ID)) : admin_url('options.php?page=w2dc_renew&listing_id=' . $post->ID)),
							get_option('w2dc_expiration_notification')));
					w2dc_mail($listing_owner->user_email, $subject, $body);
				}
			}

			// adapted for WPML
			global $sitepress;
			if (function_exists('wpml_object_id_filter') && $sitepress) {
				$trid = $sitepress->get_element_trid($post_id, 'post_' . W2DC_POST_TYPE);
				$translations = $sitepress->get_element_translations($trid, 'post_' . W2DC_POST_TYPE, false, true);
				foreach ($translations AS $lang=>$translation) {
					$listings_ids_to_suspend[] = $translation->element_id;
				}
			} else
				$listings_ids_to_suspend[] = $post_id;
		}
		$listings_ids_to_suspend = array_unique($listings_ids_to_suspend);
		foreach ($listings_ids_to_suspend AS $listing_id) {
			add_post_meta($listing_id, '_expiration_notification_sent', true);
			update_post_meta($listing_id, '_listing_status', 'expired');
			wp_update_post(array('ID' => $listing_id, 'post_status' => 'draft')); // This needed in order terms counts were always actual
		}

		$posts_ids = $wpdb->get_col($wpdb->prepare("
				SELECT
					wp_pm1.post_id
				FROM
					{$wpdb->postmeta} AS wp_pm1
				LEFT JOIN
					{$wpdb->postmeta} AS wp_pm2 ON wp_pm1.post_id=wp_pm2.post_id
				LEFT JOIN
					{$wpdb->w2dc_levels_relationships} AS wp_lr ON wp_lr.post_id=wp_pm1.post_id
				LEFT JOIN
					{$wpdb->w2dc_levels} AS wp_l ON wp_l.id=wp_lr.level_id
				WHERE
					wp_pm1.meta_key = '_expiration_date' AND
					wp_pm1.meta_value < %d AND
					wp_pm2.meta_key = '_listing_status' AND
					(wp_pm2.meta_value = 'active' OR wp_pm2.meta_value = 'stopped') AND
					(wp_l.active_years != 0 OR wp_l.active_months != 0 OR wp_l.active_days != 0)
			", time()+(get_option('w2dc_send_expiration_notification_days')*86400)));

		$listings_ids = array();

		// adapted for WPML
		global $sitepress;
		if (function_exists('wpml_object_id_filter') && $sitepress) {
			foreach ($posts_ids AS $post_id) {
				$trid = $sitepress->get_element_trid($post_id, 'post_' . W2DC_POST_TYPE);
				$listings_ids[] = $trid;
			}
		} else
			$listings_ids = $posts_ids;

		$listings_ids = array_unique($listings_ids);
		foreach ($listings_ids AS $listing_id) {
			if (!get_post_meta($listing_id, '_preexpiration_notification_sent', true) && ($post = get_post($listing_id))) {
				if (get_option('w2dc_preexpiration_notification')) {
					$listing_owner = get_userdata($post->post_author);

					$subject = __('Expiration notification', 'W2DC');
					
					$body = str_replace('[listing]', $post->post_title,
							str_replace('[days]', get_option('w2dc_send_expiration_notification_days'),
							get_option('w2dc_preexpiration_notification')));
					w2dc_mail($listing_owner->user_email, $subject, $body);
				}

				$continue_invoke_hooks = true;
				if ($listing = $this->listings_manager->loadListing($listing_id))
					apply_filters('w2dc_listing_renew', false, $listing, array(&$continue_invoke_hooks));
			}
			
			add_post_meta($listing_id, '_preexpiration_notification_sent', true);
		}
	}

	/**
	 * Special template for listings printing functionality
	 */
	public function printlisting_template($template) {
		if ((is_page($this->index_page_id) || is_page($this->listing_page_id)) && ($this->action == 'printlisting' || $this->action == 'pdflisting')) {
			if (is_file(W2DC_TEMPLATES_PATH . 'frontend/listing_print-custom.tpl.php'))
				$template = W2DC_TEMPLATES_PATH . 'frontend/listing_print-custom.tpl.php';
			else
				$template = W2DC_TEMPLATES_PATH . 'frontend/listing_print.tpl.php';
		}
		return $template;
	}
	
	function filter_comment_status($open, $post_id) {
		$post = get_post($post_id);
		if ($post->post_type == W2DC_POST_TYPE) {
			if (get_option('w2dc_listings_comments_mode') == 'enabled')
				return true;
			elseif (get_option('w2dc_listings_comments_mode') == 'disabled')
				return false;
			else 
				return $open;
		} else
			return $open;
	}

	/**
	 * Get property by shortcode name
	 * 
	 * @param string $shortcode
	 * @param string $property if property missed - return controller object
	 * @return mixed
	 */
	public function getShortcodeProperty($shortcode, $property = false) {
		if (!isset($this->frontend_controllers[$shortcode]) || !isset($this->frontend_controllers[$shortcode][0]))
			return false;

		if ($property && !isset($this->frontend_controllers[$shortcode][0]->$property))
			return false;

		if ($property)
			return $this->frontend_controllers[$shortcode][0]->$property;
		else 
			return $this->frontend_controllers[$shortcode][0];
	}
	
	public function getShortcodeByHash($hash) {
		if (!isset($this->frontend_controllers) || !is_array($this->frontend_controllers) || empty($this->frontend_controllers))
			return false;

		foreach ($this->frontend_controllers AS $shortcodes)
			foreach ($shortcodes AS $controller)
				if (is_object($controller) && $controller->hash == $hash)
					return $controller;
	}
	
	public function getListingsShortcodeByuID($uid) {
		foreach ($this->frontend_controllers AS $shortcodes)
			foreach ($shortcodes AS $controller)
				if (is_object($controller) && get_class($controller) == 'w2dc_listings_controller' && $controller->args['uid'] == $uid)
					return $controller;
	}

	public function enqueue_scripts_styles($load_scripts_styles = false) {
		global $w2dc_enqueued;
		if ((($this->frontend_controllers || $load_scripts_styles) && !$w2dc_enqueued) || get_option('w2dc_force_include_js_css')) {
			add_action('wp_head', array($this, 'enqueue_global_vars'));
			add_action('wp_print_scripts', array($this, 'dequeue_maps_googleapis'), 1000);

			wp_register_style('w2dc_bootstrap', W2DC_RESOURCES_URL . 'css/bootstrap.css');
			if (!(function_exists('is_rtl') && is_rtl()))
				wp_register_style('w2dc_frontend', W2DC_RESOURCES_URL . 'css/frontend.css');
			else
				wp_register_style('w2dc_frontend', W2DC_RESOURCES_URL . 'css/frontend-rtl.css');
			wp_register_style('w2dc_font_awesome', W2DC_RESOURCES_URL . 'css/font-awesome.css');
	
			if (is_file(W2DC_RESOURCES_PATH . 'css/frontend-custom.css'))
				wp_register_style('w2dc_frontend-custom', W2DC_RESOURCES_URL . 'css/frontend-custom.css');
	
			wp_register_script('w2dc_js_functions', W2DC_RESOURCES_URL . 'js/js_functions.js', array('jquery'), false, true);

			wp_register_script('w2dc_categories_scripts', W2DC_RESOURCES_URL . 'js/manage_categories.js', array('jquery'), false, true);

			wp_register_style('w2dc_media_styles', W2DC_RESOURCES_URL . 'lightbox/css/lightbox.css');
			wp_register_script('w2dc_media_scripts_lightbox', W2DC_RESOURCES_URL . 'lightbox/js/lightbox.min.js', array('jquery'), false, true);
			wp_register_script('w2dc_media_scripts', W2DC_RESOURCES_URL . 'js/ajaxfileupload.js', array('jquery'), false, true);

			// this jQuery UI version 1.10.4
			if (get_option('w2dc_jquery_ui_schemas')) $ui_theme = w2dc_get_dynamic_option('w2dc_jquery_ui_schemas'); else $ui_theme = 'smoothness';
			wp_register_style('w2dc-jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/' . $ui_theme . '/jquery-ui.css');

			wp_register_script('w2dc_google_maps_edit', W2DC_RESOURCES_URL . 'js/google_maps_edit.js', array('jquery'), false, true);

			wp_enqueue_style('w2dc_bootstrap');
			wp_enqueue_style('w2dc_frontend');
			wp_enqueue_style('w2dc_font_awesome');
	
			// Include dynamic-css file only when we are not in palettes comparison mode
			if (!isset($_COOKIE['w2dc_compare_palettes']) || !get_option('w2dc_compare_palettes')) {
				// Include dynamically generated css file if this file exists
				$upload_dir = wp_upload_dir();
				$filename = trailingslashit(set_url_scheme($upload_dir['baseurl'])) . 'w2dc-plugin.css';
				$filename_dir = trailingslashit($upload_dir['basedir']) . 'w2dc-plugin.css';
				global $wp_filesystem;
				if (empty($wp_filesystem)) {
					require_once(ABSPATH .'/wp-admin/includes/file.php');
					WP_Filesystem();
				}
				if ($wp_filesystem && trim($wp_filesystem->get_contents($filename_dir))) // if css file creation success
					wp_enqueue_style('w2dc-dynamic-css', $filename);
			}
	
			wp_enqueue_style('w2dc_frontend-custom');

			wp_enqueue_script('jquery-ui-dialog');
			wp_enqueue_script('jquery-ui-draggable');
			if (!get_option('w2dc_notinclude_jqueryui_css'))
				wp_enqueue_style('w2dc-jquery-ui-style');

			wp_enqueue_script('w2dc_js_functions');
			
			wp_register_style('w2dc_listings_slider', W2DC_RESOURCES_URL . 'css/bxslider/jquery.bxslider.css');
			wp_enqueue_style('w2dc_listings_slider');

			// Single Listing page
			if ($this->getShortcodeProperty('webdirectory', 'is_single') || $this->getShortcodeProperty(W2DC_LISTING_SHORTCODE, 'is_single') || $this->getShortcodeProperty('webdirectory-listing', 'is_single')) {
				if (get_option('w2dc_images_lightbox') && get_option('w2dc_enable_lighbox_gallery')) {
					wp_enqueue_style('w2dc_media_styles');
					wp_enqueue_script('w2dc_media_scripts_lightbox');
				}
			}
			
			wp_localize_script(
				'w2dc_js_functions',
				'w2dc_google_maps_callback',
				array(
						'callback' => 'w2dc_load_maps_api'
				)
			);
			
			if (get_option('w2dc_enable_recaptcha') && get_option('w2dc_recaptcha_public_key') && get_option('w2dc_recaptcha_private_key')) {
				wp_register_script('w2dc_recaptcha', '//google.com/recaptcha/api.js');
				wp_enqueue_script('w2dc_recaptcha');
			}

			$w2dc_enqueued = true;
		}
	}
	
	public function dequeue_maps_googleapis() {
		if ((w2dc_is_maps_used() && get_option('w2dc_google_api_key') && !(defined('W2DC_NOTINCLUDE_MAPS_API') && W2DC_NOTINCLUDE_MAPS_API)) && !(defined('W2DC_NOT_DEQUEUE_MAPS_API') && W2DC_NOT_DEQUEUE_MAPS_API)) {
			global $wp_scripts;
			foreach ($wp_scripts->registered AS $key=>$script) {
				if (strpos($script->src, 'maps.googleapis.com') !== false || strpos($script->src, 'maps.google.com/maps/api') !== false)
					unset($wp_scripts->registered[$key]);
			}
		}
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
		echo 'var w2dc_controller_args_array = {};
';
		echo 'var w2dc_map_markers_attrs_array = [];
';
		echo 'var w2dc_map_markers_attrs = (function(map_id, markers_array, enable_radius_circle, enable_clusters, show_summary_button, show_readmore_button, draw_panel, map_style_name, enable_full_screen, enable_wheel_zoom, enable_dragging_touchscreens, center_map_onclick, map_attrs) {
		this.map_id = map_id;
		this.markers_array = markers_array;
		this.enable_radius_circle = enable_radius_circle;
		this.enable_clusters = enable_clusters;
		this.show_summary_button = show_summary_button;
		this.show_readmore_button = show_readmore_button;
		this.draw_panel = draw_panel;
		this.map_style_name = map_style_name;
		this.enable_full_screen = enable_full_screen;
		this.enable_wheel_zoom = enable_wheel_zoom;
		this.enable_dragging_touchscreens = enable_dragging_touchscreens;
		this.center_map_onclick = center_map_onclick;
		this.map_attrs = map_attrs;
		});
';
		global $w2dc_maps_styles;
		echo 'var w2dc_js_objects = ' . json_encode(
				array(
						'ajaxurl' => $ajaxurl,
						'ajax_loader_url' => W2DC_RESOURCES_URL . 'images/ajax-loader.gif',
						'ajax_iloader_url' => W2DC_RESOURCES_URL . 'images/ajax-indicator.gif',
						'ajax_loader_text' => __('Loading...', 'W2DC'),
						'search_button_text' => __('Search', 'W2DC'),
						'search_map_button_text' => __('Search on map', 'W2DC'),
						'ajax_map_loader_url' => W2DC_RESOURCES_URL . 'images/ajax-map-loader.gif',
						'in_favourites_icon' => 'w2dc-glyphicon-heart-empty',
						'not_in_favourites_icon' => 'w2dc-glyphicon-heart',
						'in_favourites_msg' => __('Add Bookmark', 'W2DC'),
						'not_in_favourites_msg' => __('Remove Bookmark', 'W2DC'),
						'ajax_load' => (int)get_option('w2dc_ajax_load'),
						'ajax_initial_load' => (int)get_option('w2dc_ajax_initial_load'),
						'is_rtl' => is_rtl(),
						'send_button_text' => __('Send message', 'W2DC'),
						'send_button_sending' => __('Sending...', 'W2DC'),
						'recaptcha_public_key' => ((get_option('w2dc_enable_recaptcha') && get_option('w2dc_recaptcha_public_key') && get_option('w2dc_recaptcha_private_key')) ? get_option('w2dc_recaptcha_public_key') : ''),
						'lang' => (($sitepress && get_option('w2dc_map_language_from_wpml')) ? ICL_LANGUAGE_CODE : ''),
						'is_maps_used' => w2dc_is_maps_used(),
				)
		) . ';
';
			
		$map_content_fields = $this->content_fields->getMapContentFields();
		$map_content_fields_icons = array('w2dc-fa-info-circle');
		foreach ($map_content_fields AS $content_field)
			if (is_a($content_field, 'w2dc_content_field') && $content_field->icon_image)
				$map_content_fields_icons[] = $content_field->icon_image;
			else
				$map_content_fields_icons[] = '';
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
						'infowindow_width' => (int)get_option('w2dc_map_infowindow_width'),
						'infowindow_offset' => -(int)get_option('w2dc_map_infowindow_offset'),
						'infowindow_logo_width' => (int)get_option('w2dc_map_infowindow_logo_width'),
						'w2dc_map_info_window_button_readmore' => __('Read more »', 'W2DC'),
						'w2dc_map_info_window_button_summary' => __('« Summary', 'W2DC'),
						'map_style_name' => get_option('w2dc_map_style'),
						'draw_area_button' => __('Draw Area', 'W2DC'),
						'edit_area_button' => __('Edit Area', 'W2DC'),
						'apply_area_button' => __('Apply Area', 'W2DC'),
						'reload_map_button' => __('Reload Map', 'W2DC'),
						'my_location_button' => __('My Location', 'W2DC'),
						'locations_targeting_text' => __('Locations targeting...', 'W2DC'),
						'w2dc_map_content_fields_icons' => $map_content_fields_icons,
						'map_markers_array' => w2dc_get_fa_icons_names(),
						'map_styles' => $w2dc_maps_styles,
						'address_autocomplete_code' => get_option('w2dc_address_autocomplete_code'),
				)
		) . ';
';
		echo '</script>
';
	}

	// Include dynamically generated css code if css file does not exist.
	public function enqueue_dynamic_css($load_scripts_styles = false) {
		if ($this->frontend_controllers || $load_scripts_styles || get_option('w2dc_force_include_js_css')) {
			$upload_dir = wp_upload_dir();
			$filename = trailingslashit(set_url_scheme($upload_dir['baseurl'])) . 'w2dc-plugin.css';
			$filename_dir = trailingslashit($upload_dir['basedir']) . 'w2dc-plugin.css';
			global $wp_filesystem;
			if (empty($wp_filesystem)) {
				require_once(ABSPATH .'/wp-admin/includes/file.php');
				WP_Filesystem();
			}
			if ((!$wp_filesystem || !trim($wp_filesystem->get_contents($filename_dir))) ||
				// When we are in palettes comparison mode - this will build css according to $_COOKIE['w2dc_compare_palettes']
				(isset($_COOKIE['w2dc_compare_palettes']) && get_option('w2dc_compare_palettes')))
			{
				ob_start();
				include W2DC_PATH . '/classes/customization/dynamic_css.php';
				$dynamic_css = ob_get_contents();
				ob_get_clean();
				echo '<style type="text/css">
	';
				echo $dynamic_css;
				echo '</style>';
			}
		}
	}

	// adapted for Polylang
	public function pll_setup() {
		if (defined("POLYLANG_VERSION")) {
			add_filter('post_type_link', array($this, 'pll_stop_add_lang_to_url_post'), 0, 2);
			add_filter('post_type_link', array($this, 'pll_start_add_lang_to_url_post'), 100, 2);
			add_filter('term_link', array($this, 'pll_stop_add_lang_to_url_term'), 0, 3);
			add_filter('term_link', array($this, 'pll_start_add_lang_to_url_term'), 100, 3);
			add_filter('rewrite_rules_array', array($this, 'pll_rewrite_rules'));
		}
	}
	public function pll_stop_add_lang_to_url_post($permalink, $post) {
		$this->pll_force_lang = false;
		if ($post->post_type == W2DC_POST_TYPE) {
			global $polylang;
			if (isset($polylang->links->links_model->model->options['force_lang']) && $polylang->links->links_model->model->options['force_lang']) {
				$this->pll_force_lang = true;
				$polylang->links->links_model->model->options['force_lang'] = 0;
			}
		}
		return $permalink;
	}
	public function pll_start_add_lang_to_url_post($permalink, $post) {
		if ($this->pll_force_lang && $post->post_type == W2DC_POST_TYPE) {
			global $polylang;
			$polylang->links->links_model->model->options['force_lang'] = 1;
		}
		return $permalink;
	}
	public function pll_stop_add_lang_to_url_term($permalink, $term, $tax) {
		$this->pll_force_lang = false;
		if ($tax == W2DC_CATEGORIES_TAX || $tax == W2DC_LOCATIONS_TAX || $tax == W2DC_TAGS_TAX) {
			global $polylang;
			if (isset($polylang->links->links_model->model->options['force_lang']) && $polylang->links->links_model->model->options['force_lang']) {
				$this->pll_force_lang = true;
				$polylang->links->links_model->model->options['force_lang'] = 0;
			}
		}
	}
	public function pll_start_add_lang_to_url_term($permalink, $term, $tax) {
		if ($this->pll_force_lang && ($tax == W2DC_CATEGORIES_TAX || $tax == W2DC_LOCATIONS_TAX || $tax == W2DC_TAGS_TAX)) {
			global $polylang;
			$polylang->links->links_model->model->options['force_lang'] = 1;
		}
		return $permalink;
	}
	public function pll_rewrite_rules($rules) {
		global $polylang, $wp_current_filter;
		$wp_current_filter[] = 'w2dc_listing';
		return $polylang->links->links_model->rewrite_rules($this->buildRules()) + $rules;
	}
}

$w2dc_instance = new w2dc_plugin();
$w2dc_instance->init();

if (get_option('w2dc_fsubmit_addon'))
	include_once W2DC_PATH . 'addons/w2dc_fsubmit/w2dc_fsubmit.php';
if (get_option('w2dc_payments_addon'))
	include_once W2DC_PATH . 'addons/w2dc_payments/w2dc_payments.php';
if (get_option('w2dc_ratings_addon'))
	include_once W2DC_PATH . 'addons/w2dc_ratings/w2dc_ratings.php';

?>
