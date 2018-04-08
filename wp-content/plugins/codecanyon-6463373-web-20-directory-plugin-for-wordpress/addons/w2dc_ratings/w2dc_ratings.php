<?php

define('W2DC_RATINGS_PATH', plugin_dir_path(__FILE__));

function w2dc_ratings_loadPaths() {
	define('W2DC_RATINGS_TEMPLATES_PATH',  W2DC_RATINGS_PATH . 'templates/');
	define('W2DC_RATINGS_RESOURCES_URL', plugins_url('/', __FILE__) . 'resources/');
}
add_action('init', 'w2dc_ratings_loadPaths', 0);

define('W2DC_RATING_PREFIX', '_w2dc_rating_');
define('W2DC_AVG_RATING_KEY', '_w2dc_avg_rating');

include_once W2DC_RATINGS_PATH . 'classes/ratings.php';

class w2dc_ratings_plugin {

	public function __construct() {
		register_activation_hook(__FILE__, array($this, 'activation'));
	}
	
	public function activation() {
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		if (!defined('W2DC_VERSION')) {
			deactivate_plugins(basename(__FILE__)); // Deactivate ourself
			wp_die("Web 2.0 Directory plugin required.");
		}
	}

	public function init() {
		global $w2dc_instance;
		
		if (!get_option('w2dc_installed_ratings'))
			//w2dc_install_ratings();
			add_action('init', 'w2dc_install_ratings', 0);
		add_action('w2dc_version_upgrade', 'w2dc_upgrade_ratings');

		add_filter('w2dc_build_settings', array($this, 'plugin_settings'));
		
		add_action('wp_ajax_w2dc_save_rating', array($this, 'save_rating'));
		add_action('wp_ajax_nopriv_w2dc_save_rating', array($this, 'save_rating'));
		
		add_action('wp_ajax_w2dc_flush_ratings', array($this, 'flush_ratings'));
		add_action('wp_ajax_nopriv_w2dc_flush_ratings', array($this, 'flush_ratings'));
		
		add_filter('w2dc_listing_loading', array($this, 'load_listing'));
		add_filter('w2dc_listing_map_loading', array($this, 'load_listing'));

		add_filter('comment_text', array($this, 'rating_in_comment'), 10000);
		
		//add_action('w2dc_listing_pre_logo_wrap_html', array($this, 'render_rating'));
		add_action('w2dc_listing_title_html', array($this, 'render_rating'), 10, 2);
		add_action('w2dc_dashboard_listing_title', array($this, 'render_rating_dashboard'));

		add_filter('w2dc_map_info_window_fields', array($this, 'add_rating_field_to_map_window'));
		add_filter('w2dc_map_info_window_fields_values', array($this, 'render_rating_in_map_window'), 10, 3);
		
		add_filter('w2dc_default_orderby_options', array($this, 'order_by_rating_option'));
		add_filter('w2dc_ordering_options', array($this, 'order_by_rating_html'), 10, 3);
		add_filter('w2dc_order_args', array($this, 'order_by_rating_args'), 101, 2);
		
		$this->loadRatingsByLevels();
		add_filter('w2dc_levels_loading', array($this, 'loadRatingsByLevels'), 10, 2);
		add_filter('w2dc_level_html', array($this, 'ratings_options_in_level_html'));
		add_filter('w2dc_level_validation', array($this, 'ratings_options_in_level_validation'));
		add_filter('w2dc_level_create_edit_args', array($this, 'ratings_options_in_level_create_add'), 1, 2);
		
		add_action('add_meta_boxes', array($this, 'addRatingsMetabox'), 301);

		add_action('w2dc_edit_listing_metaboxes_post', array($this, 'frontendRatingsMetabox'));

		add_filter('manage_'.W2DC_POST_TYPE.'_posts_columns', array($this, 'add_listings_table_columns'));
		add_filter('manage_'.W2DC_POST_TYPE.'_posts_custom_column', array($this, 'manage_listings_table_rows'), 10, 2);
		
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts_styles'));
	}

	public function plugin_settings($options) {
		$options['template']['menus']['pages_views']['controls']['ratings'] = array(
			'type' => 'section',
			'title' => __('Ratings settings', 'W2DC'),
			'fields' => array(
				array(
					'type' => 'toggle',
					'name' => 'w2dc_only_registered_users',
					'label' => __('Only registered users may place ratings', 'W2DC'),
					'default' => get_option('w2dc_only_registered_users'),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_rating_on_map',
					'label' => __('Show rating in map info window', 'W2DC'),
					'default' => get_option('w2dc_rating_on_map'),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_manage_ratings',
					'label' => __('Allow users to flush ratings of own listings', 'W2DC'),
					'default' => get_option('w2dc_manage_ratings'),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_orderby_rating',
					'label' => __('Allow sorting by ratings', 'W2DC'),
					'default' => get_option('w2dc_orderby_rating'),
				),
			),
		);
		return $options;
	}

	public function loadRatingsByLevels($level = null, $array = array()) {
		global $w2dc_instance, $wpdb;
	
		if (!$array && isset($w2dc_instance->levels->levels_array)) {
			$array = $wpdb->get_results("SELECT * FROM {$wpdb->w2dc_levels} ORDER BY order_num", ARRAY_A);

			foreach ($array AS $row) {
				$w2dc_instance->levels->levels_array[$row['id']]->ratings_enabled = $row['ratings_enabled'];
	
				if (is_object($level) && $level->id == $row['id'])
					$level->ratings_enabled = $row['ratings_enabled'];
			}
		} else
			$level->ratings_enabled = $array['ratings_enabled'];
	
		return $level;
	}
	
	public function ratings_options_in_level_html($level) {
		w2dc_renderTemplate(array(W2DC_RATINGS_TEMPLATES_PATH, 'ratings_options_in_level.tpl.php'), array('level' => $level));
	}
	
	public function ratings_options_in_level_validation($validation) {
		$validation->set_rules('ratings_enabled', __('Ratings', 'W2DC'), 'is_checked');
			
		return $validation;
	}
	
	public function ratings_options_in_level_create_add($insert_update_args, $array) {
		$insert_update_args['ratings_enabled'] = w2dc_getValue($array, 'ratings_enabled', 1);
		return $insert_update_args;
	}
	
	public function load_listing($listing) {
		if ($listing->level->ratings_enabled)
			$listing->avg_rating = new w2dc_avg_rating($listing->post->ID);
		
		return $listing;
	}
	
	public function addRatingsMetabox($post_type) {
		if ($post_type == W2DC_POST_TYPE && ($level = w2dc_getCurrentListingInAdmin()->level) && $level->ratings_enabled) {
			add_meta_box('w2dc_ratings',
					__('Listing ratings', 'W2DC'),
					array($this, 'listingRatingsMetabox'),
					W2DC_POST_TYPE,
					'normal',
					'high');
		}
	}
	
	public function listingRatingsMetabox($post) {
		$listing = new w2dc_listing();
		$listing->loadListingFromPost($post);

		$total_counts = array('1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0);
		foreach ($listing->avg_rating->ratings AS $rating)
			$total_counts[$rating->value]++;
		
		krsort($total_counts);

		w2dc_renderTemplate(array(W2DC_RATINGS_TEMPLATES_PATH, 'ratings_metabox.tpl.php'), array('listing' => $listing, 'total_counts' => $total_counts));
	}
	
	public function frontendRatingsMetabox($listing) {
		if ($listing->level->ratings_enabled) {
			echo '<div class="w2dc-submit-section">';
				echo '<h3 class="w2dc-submit-section-label">' . __('Listing ratings', 'W2DC') . '</h3>';
				echo '<div class="w2dc-submit-section-inside">';
					$this->listingRatingsMetabox($listing->post);
				echo '</div>';
			echo '</div>';
		}
	}
	
	public function flush_ratings() {
		$post_id = w2dc_getValue($_POST, 'post_id');
		
		if (($post = get_post($post_id)) && ((get_option('w2dc_manage_ratings') && w2dc_current_user_can_edit_listing($post_id)) || current_user_can('edit_others_posts'))) {
			w2dc_flush_ratings($post_id);
		}
		die();
	}
	
	public function add_listings_table_columns($columns) {
		$w2dc_columns['w2dc_rating'] = __('Rating', 'W2DC');

		$comments_index = array_search("comments", array_keys($columns));

		return array_slice($columns, 0, $comments_index, true) + $w2dc_columns + array_slice($columns, $comments_index, count($columns)-$comments_index, true);
	}
	
	public function manage_listings_table_rows($column, $post_id) {
		if ($column == "w2dc_rating") {
			$listing = new w2dc_listing();
			$listing->loadListingFromPost($post_id);
			$this->render_rating($listing, false, false);
		}
	}
	
	public function save_rating() {
		$post_id = w2dc_getValue($_GET, 'post_id');
		$rating = w2dc_getValue($_POST, 'rating');
		$_wpnonce = wp_verify_nonce(w2dc_getValue($_GET, '_wpnonce'), 'save_rating');

		if (($post = get_post($post_id)) && $rating && ($rating >= 1 && $rating <= 5) && $_wpnonce) {
			if (!$this->is_listing_rated($post->ID)) {
				$user_id = get_current_user_id();
				$ip = w2dc_ip_address();
				if (get_option('w2dc_only_registered_users') && !$user_id)
					return false;

				if ($user_id)
					add_post_meta($post->ID, W2DC_RATING_PREFIX . $user_id, $rating);
				elseif ($ip)
					add_post_meta($post->ID, W2DC_RATING_PREFIX . $ip, $rating);

				setcookie(W2DC_RATING_PREFIX . $post->ID, $rating, time() + 31536000);

				$avg_rating = new w2dc_avg_rating($post->ID);
				$avg_rating->update_avg_rating();
				echo $avg_rating->avg_value;
			} else {
				$avg_rating = new w2dc_avg_rating($post->ID);
				echo $avg_rating->avg_value;
			}
		}
		die();
	}
	
	public function is_listing_rated($id) {
		if (!isset($_COOKIE[W2DC_RATING_PREFIX . $id])) {
			if ($user_id = get_current_user_id())
				if (get_post_meta($id, W2DC_RATING_PREFIX . $user_id, true))
					return true;
		
			if ($ip = w2dc_ip_address())
				if (get_post_meta($id, W2DC_RATING_PREFIX . $ip, true))
					return true;
		} else {
			return true;
		}
	}

	public function render_rating($listing, $meta_tags = false, $active = true, $show_avg = true) {
		global $w2dc_instance;
		
		// Single Listing page
		/* if ($w2dc_instance->getShortcodeProperty('webdirectory', 'is_single') || $w2dc_instance->getShortcodeProperty('webdirectory-listing', 'is_single')) {
			// Do not render rating at this hook when we are on single listing page
			return ;
		} */

		if ($listing->level->ratings_enabled) {
			if ($this->is_listing_rated($listing->post->ID))
				$active = false;
			if (get_option('w2dc_only_registered_users') && !get_current_user_id())
				$active = false;
			if ((get_current_user_id() == $listing->post->post_author) && !current_user_can('manage_options'))
				$active = false;
			if ($w2dc_instance->action == 'printlisting' || $w2dc_instance->action == 'pdflisting')
				$active = false;
			
			/* if (
				$w2dc_instance->getShortcodeProperty('webdirectory') || $w2dc_instance->getShortcodeProperty('webdirectory-listings') ||
				$w2dc_instance->getShortcodeProperty('webdirectory', 'is_single') || $w2dc_instance->getShortcodeProperty('webdirectory-listing')
			)
				$show_avg = false;  */

			w2dc_renderTemplate(array(W2DC_RATINGS_TEMPLATES_PATH, 'avg_rating.tpl.php'), array('listing' => $listing, 'meta_tags' => $meta_tags, 'active' => $active, 'show_avg' => $show_avg));
		}
		
		return $listing;
	}

	public function render_rating_dashboard($listing) {
		global $w2dc_instance;

		if ($listing->level->ratings_enabled)
			w2dc_renderTemplate(array(W2DC_RATINGS_TEMPLATES_PATH, 'avg_rating.tpl.php'), array('listing' => $listing, 'meta_tags' => false, 'active' => false, 'show_avg' => true));
		
		return $listing;
	}
	
	public function add_rating_field_to_map_window($fields) {
		if (get_option('w2dc_rating_on_map'))
			$fields = array('rating' => '') + $fields;

		return $fields;
	}

	public function render_rating_in_map_window($content_field, $field_slug, $listing) {
		if (get_option('w2dc_rating_on_map') && $field_slug == 'rating' && $listing->level->ratings_enabled && isset($listing->avg_rating))
			return w2dc_renderTemplate(array(W2DC_RATINGS_TEMPLATES_PATH, 'avg_rating.tpl.php'), array('listing' => $listing, 'meta_tags' => false, 'active' => false, 'show_avg' => true), true);
	}
	
	public function order_by_rating_args($args, $defaults = array(), $include_GET_params = true) {
		if (get_option('w2dc_orderby_rating')) {
			if ($include_GET_params && isset($_REQUEST['order_by']) && $_REQUEST['order_by']) {
				$order_by = $_REQUEST['order_by'];
				$order = w2dc_getValue($_REQUEST, 'order', 'DESC');
			} else {
				if (isset($defaults['order_by']) && $defaults['order_by']) {
					$order_by = $defaults['order_by'];
					$order = w2dc_getValue($defaults, 'order', 'DESC');
				}
			}
	
			if (isset($order_by) && $order_by == 'rating_order') {
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = W2DC_AVG_RATING_KEY;
				$args['order'] = $order;
				if (get_option('w2dc_orderby_sticky_featured')) {
					add_filter('get_meta_sql', array($this, 'add_null_values'));
					add_filter('w2dc_frontend_controller_construct', array($this, 'remove_query_filters'));
				}
				if (get_option('w2dc_orderby_exclude_null')) {
					add_filter('posts_join', 'join_levels');
					add_filter('posts_where', array($this, 'where_ratings_levels'));
					add_filter('w2dc_frontend_controller_construct', array($this, 'remove_query_filters'));
				}
			}
		}

		return $args;
	}
	public function where_ratings_levels($where = '') {
		$where .= " AND w2dc_levels.ratings_enabled=1";
		return $where;
	}
	/**
	* Listings with empty values must be sorted as well
	*
	*/
	public function add_null_values($clauses) {
		$clauses['where'] = str_replace("wp_postmeta.meta_key = '".W2DC_AVG_RATING_KEY."'", "(wp_postmeta.meta_key = '".W2DC_AVG_RATING_KEY."' OR wp_postmeta.meta_value IS NULL)", $clauses['where']);
		return $clauses;
	}
	public function remove_query_filters() {
		remove_filter('posts_join', 'join_levels');
		remove_filter('posts_where', array($this, 'where_ratings_levels'));
		remove_filter('get_meta_sql', array($this, 'add_null_values'));
	}
	
	public function order_by_rating_option($ordering) {
		if (get_option('w2dc_orderby_rating'))
			$ordering['rating_order'] = __('Rating', 'W2DC');
		
		return $ordering;
	}

	public function order_by_rating_html($ordering, $base_url, $defaults = array()) {
		if (get_option('w2dc_orderby_rating')) {
			$order_by = false;
			if (isset($_REQUEST['order_by']) && $_REQUEST['order_by']) {
				$order_by = $_REQUEST['order_by'];
				$order = w2dc_getValue($_REQUEST, 'order', 'DESC');
			} else {
				if (isset($defaults['order_by']) && $defaults['order_by']) {
					$order_by = $defaults['order_by'];
					$order = w2dc_getValue($defaults, 'order', 'DESC');
				}
			}
		
			$class = '';
			$next_order = 'DESC';
			if ($order_by == 'rating_order') {
				if ($order == 'ASC') {
					$class = 'ascending';
					$next_order = 'DESC';
					$url = esc_url(add_query_arg('order_by', 'rating_order', $base_url));
				} elseif (!$order || $order == 'DESC') {
					$class = 'descending';
					$next_order = 'ASC';
					$url = esc_url(add_query_arg(array('order_by' => 'rating_order', 'order' => 'ASC'), $base_url));
				}
			} else
				$url = esc_url(add_query_arg('order_by', 'rating_order', $base_url));
	
			$ordering['links']['rating_order'] = '<a class="' . $class . '" href="' . $url . '">' . __('Rating', 'W2DC') . '</a>';
			$ordering['array']['rating_order'] = __('Rating', 'W2DC');
			$ordering['struct']['rating_order'] = array('class' => $class, 'url' => $url, 'field_name' => __('Rating', 'W2DC'), 'order' => $next_order);
		}
	
		return $ordering;
	}
	
	public function rating_in_comment($output) {
		$comment = 0;
		if (($comment = get_comment($comment)) && ($post = get_post()) && $post->post_type == W2DC_POST_TYPE) {
			if ($rating = w2dc_build_single_rating($comment->comment_post_ID, $comment->user_id))
				$output = w2dc_renderTemplate(array(W2DC_RATINGS_TEMPLATES_PATH, 'single_rating.tpl.php'), array('rating' => $rating), true) . $output;
		}
	
		return $output;
	}

	public function enqueue_scripts_styles($load_scripts_styles = false) {
		global $w2dc_instance, $w2dc_ratings_enqueued;
		if ((is_admin() || $w2dc_instance->frontend_controllers || $load_scripts_styles) && !$w2dc_ratings_enqueued) {
			if (!(function_exists('is_rtl') && is_rtl())) {
				wp_register_script('rater', W2DC_RATINGS_RESOURCES_URL . 'js/jquery.rater.js', array('jquery'), false, true);
				wp_register_style('rater', W2DC_RATINGS_RESOURCES_URL . 'css/rater.css');
			} else { 
				wp_register_script('rater', W2DC_RATINGS_RESOURCES_URL . 'js/jquery.rater-rtl.js', array('jquery'), false, true);
				wp_register_style('rater', W2DC_RATINGS_RESOURCES_URL . 'css/rater-rtl.css');
			}
	
			
			if (is_file(W2DC_RATINGS_RESOURCES_URL . 'css/rater-custom.css'))
				wp_register_style('rater-custom', W2DC_RATINGS_RESOURCES_URL . 'css/rater-custom.css');
			
			wp_enqueue_script('rater');
			wp_enqueue_style('rater');
			
			wp_enqueue_style('rater-custom');

			$w2dc_ratings_enqueued = true;
		}
	}
}

function w2dc_install_ratings() {
	global $wpdb;

	// there may be possible bug in WP, on some servers it doesn't allow to execute more than one SQL query in one request
	$wpdb->query("ALTER TABLE {$wpdb->w2dc_levels} ADD `ratings_enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `google_map_markers`");
	if (array_search('ratings_enabled', $wpdb->get_col("DESC {$wpdb->w2dc_levels}"))) {
		add_option('w2dc_only_registered_users', 0);
		add_option('w2dc_rating_on_map', 1);
		add_option('w2dc_manage_ratings', 1);

		w2dc_upgrade_ratings('1.5.8');
		
		add_option('w2dc_installed_ratings', 1);
	}
}

function w2dc_upgrade_ratings($new_version) {
	if ($new_version == '1.5.8') {
		add_option('w2dc_orderby_rating', 1);
	}
}

global $w2dc_ratings_instance;

$w2dc_ratings_instance = new w2dc_ratings_plugin();
$w2dc_ratings_instance->init();

?>
