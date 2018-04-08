<?php 

add_action('widgets_init', 'w2dc_register_search_widget');
function w2dc_register_search_widget() {
	register_widget('w2dc_search_widget');
}

class w2dc_search_widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
				'w2dc_search_widget',
				__('W2DC - Search', 'W2DC'),
				array('description' => __( 'Search Form', 'W2DC'),)
		);
		
		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
		add_action('wp_head', array($this, 'enqueue_dynamic_css'), 9999);
	}

	public function widget($args, $instance) {
		global $w2dc_instance;

		// Show only on directory pages and only when main search form wasn't displayed
		// also check what and where search sections
		if ((!$instance['visibility'] || !empty($w2dc_instance->frontend_controllers)) && (get_option('w2dc_show_what_search') || get_option('w2dc_show_where_search'))) {
			if (!empty($w2dc_instance->frontend_controllers))
				foreach ($w2dc_instance->frontend_controllers AS $shortcode_controllers)
					foreach ($shortcode_controllers AS $controller)
						if (is_object($controller) && $controller->search_form && $instance['search_visibility'])
							return false;
			
			$title = apply_filters('widget_title', $instance['title']);
			
			// it is auto selection - take current directory
			if ($instance['directory'] == 0) {
				// probably we are on single listing page - it could be found only after frontend controllers were loaded, so we have to repeat setting
				$w2dc_instance->setCurrentDirectory();
				
				$instance['directory'] = $w2dc_instance->current_directory->id;
			}
	
			w2dc_renderTemplate('widgets/search_widget.tpl.php', array('args' => $args, 'title' => $title, 'directory' => $instance['directory'], 'uid' => $instance['uid']));
		}
	}

	public function form($instance) {
		global $w2dc_instance;
		
		$defaults = array(
				'title' => __('Search listings', 'W2DC'),
				'directory' => $w2dc_instance->directories->getDefaultDirectory()->id,
				'uid' => '', 'visibility' => 1,
				'search_visibility' => 1
		);
		$instance = wp_parse_args((array) $instance, $defaults);

		w2dc_renderTemplate('widgets/search_widget_options.tpl.php', array('widget' => $this, 'instance' =>$instance));
	}
	
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['directory'] = (!empty($new_instance['directory'])) ? strip_tags($new_instance['directory']) : 0;
		$instance['uid'] = (!empty($new_instance['uid'])) ? strip_tags($new_instance['uid']) : '';
		$instance['visibility'] = (!empty($new_instance['visibility'])) ? strip_tags($new_instance['visibility']) : '';
		$instance['search_visibility'] = (!empty($new_instance['search_visibility'])) ? strip_tags($new_instance['search_visibility']) : '';

		return $instance;
	}
	
	public function wp_enqueue_scripts() {
		$widget_options_all = get_option($this->option_name);
		if (isset($widget_options_all[$this->number])) {
			$current_widget_options = $widget_options_all[$this->number];
			if (is_active_widget(false, false, $this->id_base, true) && !$current_widget_options['visibility'] && (get_option('w2dc_show_what_search') || get_option('w2dc_show_where_search'))) {
				global $w2dc_instance, $w2dc_fsubmit_instance, $w2dc_payments_instance, $w2dc_ratings_instance;
		
				$w2dc_instance->enqueue_scripts_styles(true);
				if ($w2dc_fsubmit_instance)
					$w2dc_fsubmit_instance->enqueue_scripts_styles(true);
				if ($w2dc_payments_instance)
					$w2dc_payments_instance->enqueue_scripts_styles(true);
				if ($w2dc_ratings_instance)
					$w2dc_ratings_instance->enqueue_scripts_styles(true);
			}
		}
	}
	
	public function enqueue_dynamic_css() {
		$widget_options_all = get_option($this->option_name);
		if (isset($widget_options_all[$this->number])) {
			$current_widget_options = $widget_options_all[$this->number];
			if (is_active_widget(false, false, $this->id_base, true) && !$current_widget_options['visibility'] && (get_option('w2dc_show_what_search') || get_option('w2dc_show_where_search'))) {
				global $w2dc_instance;
					
				$w2dc_instance->enqueue_dynamic_css(true);
			}
		}
	}
}




add_action('widgets_init', 'w2dc_register_categories_widget');
function w2dc_register_categories_widget() {
	register_widget('w2dc_categories_widget');
}

class w2dc_categories_widget extends WP_Widget {
	
	public function __construct() {
		parent::__construct(
			'w2dc_categories_widget',
			__('W2DC - Categories', 'W2DC'),
			array('description' => __( 'Categories list', 'W2DC'),)
		);
		
		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
		add_action('wp_head', array($this, 'enqueue_dynamic_css'), 9999);
	}

	public function widget($args, $instance) {
		global $w2dc_instance;

		if (!$instance['visibility'] || !empty($w2dc_instance->frontend_controllers)) {
			$title = apply_filters('widget_title', $instance['title']);
			
			// it is auto selection - take current directory
			if ($instance['directory'] == 0) {
				// probably we are on single listing page - it could be found only after frontend controllers were loaded, so we have to repeat setting
				$w2dc_instance->setCurrentDirectory();
			
				$directory = $w2dc_instance->current_directory;
			} else {
				$directory = $w2dc_instance->directories->getDirectoryById($instance['directory']);
			}
			
			$exact_categories = $directory->categories;
			
			$parent = $instance['parent'];

			// adapted for WPML
			global $sitepress;
			if ($instance['parent'] && function_exists('wpml_object_id_filter') && $sitepress) {
				if ($tparent = apply_filters('wpml_object_id', $instance['parent'], W2DC_CATEGORIES_TAX))
					$parent = $tparent;
			}
			
			// Show related subcategories on categories pages
			if (
				$instance['related_subcats']
				&& ($directory_controller = $w2dc_instance->getShortcodeProperty('webdirectory'))
				&& $directory_controller->is_category
				&& $directory_controller->category->term_id
				&& get_term_children($directory_controller->category->term_id, W2DC_CATEGORIES_TAX)
			)
				$parent = $directory_controller->category->term_id;

			// force specific directory to build right URLs
			global $w2dc_directory_flag;
			$w2dc_directory_flag = $directory->id;
			w2dc_renderTemplate('widgets/categories_widget.tpl.php', array('args' => $args, 'title' => $title, 'exact_categories' => $exact_categories, 'depth' => $instance['depth'], 'counter' => $instance['counter'], 'subcats' => $instance['subcats'], 'parent' => $parent));
			$w2dc_directory_flag = null;
		}
	}
	
	public function form($instance) {
		global $w2dc_instance;

		$defaults = array(
				'title' => __('Categories list', 'W2DC'),
				'directory' => $w2dc_instance->directories->getDefaultDirectory()->id,
				'depth' => 1,
				'counter' => 0,
				'subcats' => 0,
				'related_subcats' => 0,
				'visibility' => 1,
				'parent' => 0
		);
		$instance = wp_parse_args((array) $instance, $defaults);
		
		w2dc_renderTemplate('widgets/categories_widget_options.tpl.php', array('widget' => $this, 'instance' => $instance));
	}
	
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['directory'] = (!empty($new_instance['directory'])) ? strip_tags($new_instance['directory']) : 0;
		$instance['depth'] = (!empty($new_instance['depth'])) ? strip_tags($new_instance['depth']) : '';
		$instance['counter'] = (!empty($new_instance['counter'])) ? strip_tags($new_instance['counter']) : '';
		$instance['subcats'] = strip_tags($new_instance['subcats']);
		$instance['related_subcats'] = strip_tags($new_instance['related_subcats']);
		$instance['parent'] = strip_tags($new_instance['parent']);
		$instance['visibility'] = (!empty($new_instance['visibility'])) ? strip_tags($new_instance['visibility']) : '';
	
		return $instance;
	}
	
	public function wp_enqueue_scripts() {
		$widget_options_all = get_option($this->option_name);
		if (isset($widget_options_all[$this->number])) {
			$current_widget_options = $widget_options_all[$this->number];
			if (is_active_widget(false, false, $this->id_base, true) && !$current_widget_options['visibility']) {
				global $w2dc_instance, $w2dc_fsubmit_instance, $w2dc_payments_instance, $w2dc_ratings_instance;
	
				$w2dc_instance->enqueue_scripts_styles(true);
				if ($w2dc_fsubmit_instance)
					$w2dc_fsubmit_instance->enqueue_scripts_styles(true);
				if ($w2dc_payments_instance)
					$w2dc_payments_instance->enqueue_scripts_styles(true);
				if ($w2dc_ratings_instance)
					$w2dc_ratings_instance->enqueue_scripts_styles(true);
			}
		}
	}

	public function enqueue_dynamic_css() {
		$widget_options_all = get_option($this->option_name);
		if (isset($widget_options_all[$this->number])) {
			$current_widget_options = $widget_options_all[$this->number];
			if (is_active_widget(false, false, $this->id_base, true) && !$current_widget_options['visibility']) {
				global $w2dc_instance;
				
				$w2dc_instance->enqueue_dynamic_css(true);
			}
		}
	}
}





add_action('widgets_init', 'w2dc_register_locations_widget');
function w2dc_register_locations_widget() {
	register_widget('w2dc_locations_widget');
}

class w2dc_locations_widget extends WP_Widget {
	
	public function __construct() {
		parent::__construct(
			'w2dc_locations_widget',
			__('W2DC - Locations', 'W2DC'),
			array('description' => __( 'Locations list', 'W2DC'),)
		);
		
		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
		add_action('wp_head', array($this, 'enqueue_dynamic_css'), 9999);
	}

	public function widget($args, $instance) {
		global $w2dc_instance;
		
		if (!$instance['visibility'] || !empty($w2dc_instance->frontend_controllers)) {
			$title = apply_filters('widget_title', $instance['title']);
			
			// it is auto selection - take current directory
			if ($instance['directory'] == 0) {
				// probably we are on single listing page - it could be found only after frontend controllers were loaded, so we have to repeat setting
				$w2dc_instance->setCurrentDirectory();
			
				$directory = $w2dc_instance->current_directory;
			} else {
				$directory = $w2dc_instance->directories->getDirectoryById($instance['directory']);
			}
			
			$exact_locations = $directory->locations;
			
			// adapted for WPML
			global $sitepress;
			if ($instance['parent'] && function_exists('wpml_object_id_filter') && $sitepress) {
				if ($tparent = apply_filters('wpml_object_id', $instance['parent'], W2DC_LOCATIONS_TAX))
					$instance['parent'] = $tparent;
			}
			
			$parent = $instance['parent'];
			
			// Show related sublocations on categories pages
			if (
				$instance['related_sublocations']
				&& ($directory_controller = $w2dc_instance->getShortcodeProperty('webdirectory'))
				&& $directory_controller->is_location
				&& $directory_controller->location->term_id
				&& get_term_children($directory_controller->location->term_id, W2DC_LOCATIONS_TAX)
			)
				$parent = $directory_controller->location->term_id;

			// force specific directory to build right URLs
			global $w2dc_directory_flag;
			$w2dc_directory_flag = $directory->id;
			w2dc_renderTemplate('widgets/locations_widget.tpl.php', array('args' => $args, 'title' => $title, 'exact_locations' => $exact_locations, 'depth' => $instance['depth'], 'counter' => $instance['counter'], 'sublocations' => $instance['sublocations'], 'parent' => $parent));
			$w2dc_directory_flag = null;
		}
	}
	
	public function form($instance) {
		global $w2dc_instance;

		$defaults = array(
				'title' => __('Locations list', 'W2DC'),
				'directory' => $w2dc_instance->directories->getDefaultDirectory()->id,
				'depth' => 1,
				'counter' => 0,
				'sublocations' => 0,
				'related_sublocations' => 0,
				'visibility' => 1,
				'parent' => 0
		);
		$instance = wp_parse_args((array) $instance, $defaults);
		
		w2dc_renderTemplate('widgets/locations_widget_options.tpl.php', array('widget' => $this, 'instance' => $instance));
	}
	
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['directory'] = (!empty($new_instance['directory'])) ? strip_tags($new_instance['directory']) : 0;
		$instance['depth'] = (!empty($new_instance['depth'])) ? strip_tags($new_instance['depth']) : '';
		$instance['counter'] = (!empty($new_instance['counter'])) ? strip_tags($new_instance['counter']) : '';
		$instance['sublocations'] = strip_tags($new_instance['sublocations']);
		$instance['related_sublocations'] = strip_tags($new_instance['related_sublocations']);
		$instance['parent'] = strip_tags($new_instance['parent']);
		$instance['visibility'] = (!empty($new_instance['visibility'])) ? strip_tags($new_instance['visibility']) : '';
	
		return $instance;
	}
	
	public function wp_enqueue_scripts() {
		$widget_options_all = get_option($this->option_name);
		if (isset($widget_options_all[$this->number])) {
			$current_widget_options = $widget_options_all[$this->number];
			if (is_active_widget(false, false, $this->id_base, true) && !$current_widget_options['visibility']) {
				global $w2dc_instance, $w2dc_fsubmit_instance, $w2dc_payments_instance, $w2dc_ratings_instance;
		
				$w2dc_instance->enqueue_scripts_styles(true);
				if ($w2dc_fsubmit_instance)
					$w2dc_fsubmit_instance->enqueue_scripts_styles(true);
				if ($w2dc_payments_instance)
					$w2dc_payments_instance->enqueue_scripts_styles(true);
				if ($w2dc_ratings_instance)
					$w2dc_ratings_instance->enqueue_scripts_styles(true);
			}
		}
	}
	
	public function enqueue_dynamic_css() {
		$widget_options_all = get_option($this->option_name);
		if (isset($widget_options_all[$this->number])) {
			$current_widget_options = $widget_options_all[$this->number];
			if (is_active_widget(false, false, $this->id_base, true) && !$current_widget_options['visibility']) {
				global $w2dc_instance;
					
				$w2dc_instance->enqueue_dynamic_css(true);
			}
		}
	}
}






add_action('widgets_init', 'w2dc_register_listings_widget');
function w2dc_register_listings_widget() {
	register_widget('w2dc_listings_widget');
}

class w2dc_listings_widget extends WP_Widget {
	
	public function __construct() {
		parent::__construct(
			'w2dc_listings_widget',
			__('W2DC - Listings', 'W2DC'),
			array('description' => __( 'Listings', 'W2DC'),)
		);
		
		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
		add_action('wp_head', array($this, 'enqueue_dynamic_css'), 9999);
	}

	public function widget($args, $instance) {
		global $w2dc_instance;

		if (!$instance['visibility'] || !empty($w2dc_instance->frontend_controllers)) {
			$title = apply_filters('widget_title', $instance['title']);

			if ($instance['is_sticky_featured'] || $instance['only_sticky_featured']) {
				add_filter('posts_join', 'join_levels');
				add_filter('posts_orderby', 'orderby_levels', 1);
				if ($instance['only_sticky_featured'])
					add_filter('posts_where', 'where_sticky_featured');
			}
			$query_args = array(
					'post_type' => W2DC_POST_TYPE,
					'post_status' => 'publish',
					'meta_query' => array(array('key' => '_listing_status', 'value' => 'active')),
					'posts_per_page' => $instance['number_of_listings'],
					'orderby' => 'date',
					'order' => 'desc',
					//'orderby' => 'meta_value_num',
					//'meta_key' => '_order_date',
					//'suppress_filters' => false,
			);
			/* $posts = get_posts($query_args);
			$listings = array();
			foreach ($posts AS $post) {
				$listing = new w2dc_listing;
				$listing->loadListingFromPost($post);
				$listings[$post->ID] = $listing;
			} */
			
			$query = new WP_Query($query_args);
			$listings = array();
			while ($query->have_posts()) {
				$query->the_post();

				$listing = new w2dc_listing;
				$listing->loadListingFromPost(get_post());
				$listings[get_the_ID()] = $listing;
			}
			//this is reset is really required after the loop ends
			wp_reset_postdata();
			if ($instance['is_sticky_featured']) {
				remove_filter('posts_join', 'join_levels');
				remove_filter('posts_orderby', 'orderby_levels', 1);
				if ($instance['only_sticky_featured'])
					remove_filter('posts_where', 'where_sticky_featured');
			}

			if ($listings)
				w2dc_renderTemplate('widgets/listings_widget.tpl.php', array('args' => $args, 'title' => $title, 'listings' => $listings));
		}
	}
	
	public function form($instance) {
		$defaults = array('title' => __('Listings', 'W2DC'), 'number_of_listings' => 5, 'is_sticky_featured' => 0, 'only_sticky_featured' => 0, 'visibility' => 1);
		$instance = wp_parse_args((array) $instance, $defaults);
		
		w2dc_renderTemplate('widgets/listings_widget_options.tpl.php', array('widget' => $this, 'instance' => $instance));
	}
	
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['number_of_listings'] = (!empty($new_instance['number_of_listings'])) ? strip_tags($new_instance['number_of_listings']) : '';
		$instance['is_sticky_featured'] = (!empty($new_instance['is_sticky_featured'])) ? strip_tags($new_instance['is_sticky_featured']) : '';
		$instance['only_sticky_featured'] = (!empty($new_instance['only_sticky_featured'])) ? strip_tags($new_instance['only_sticky_featured']) : '';
		$instance['visibility'] = (!empty($new_instance['visibility'])) ? strip_tags($new_instance['visibility']) : '';
	
		return $instance;
	}
	
	public function wp_enqueue_scripts() {
		$widget_options_all = get_option($this->option_name);
		if (isset($widget_options_all[$this->number])) {
			$current_widget_options = $widget_options_all[$this->number];
			if (is_active_widget(false, false, $this->id_base, true) && !$current_widget_options['visibility']) {
				global $w2dc_instance, $w2dc_fsubmit_instance, $w2dc_payments_instance, $w2dc_ratings_instance;
		
				$w2dc_instance->enqueue_scripts_styles(true);
				if ($w2dc_fsubmit_instance)
					$w2dc_fsubmit_instance->enqueue_scripts_styles(true);
				if ($w2dc_payments_instance)
					$w2dc_payments_instance->enqueue_scripts_styles(true);
				if ($w2dc_ratings_instance)
					$w2dc_ratings_instance->enqueue_scripts_styles(true);
			}
		}
	}
	
	public function enqueue_dynamic_css() {
		$widget_options_all = get_option($this->option_name);
		if (isset($widget_options_all[$this->number])) {
			$current_widget_options = $widget_options_all[$this->number];
			if (is_active_widget(false, false, $this->id_base, true) && !$current_widget_options['visibility']) {
				global $w2dc_instance;
					
				$w2dc_instance->enqueue_dynamic_css(true);
			}
		}
	}
}






add_action('widgets_init', 'w2dc_register_social_widget');
function w2dc_register_social_widget() {
	register_widget('w2dc_social_widget');
}

class w2dc_social_widget extends WP_Widget {
	
	public function __construct() {
		parent::__construct(
			'w2dc_social_widget',
			__('W2DC - Social', 'W2DC'),
			array('description' => __( 'Social services', 'W2DC'))
		);
		
		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
		add_action('wp_head', array($this, 'enqueue_dynamic_css'), 9999);
	}

	public function widget($args, $instance) {
		global $w2dc_instance;
		
		if (!$instance['visibility'] || !empty($w2dc_instance->frontend_controllers)) {
			$title = apply_filters('widget_title', $instance['title']);
	
			w2dc_renderTemplate('widgets/social_widget.tpl.php', array('args' => $args, 'title' => $title, 'instance' => $instance));
		}
	}
	
	public function form($instance) {
		$defaults = array(
				'title' => __('Social accounts', 'W2DC'),
				'facebook' => 'http://www.facebook.com/',
				'is_facebook' => 1,
				'twitter' => 'http://twitter.com/',
				'is_twitter' => 1,
				'google' => 'http://www.google.com/',
				'is_google' => 1,
				'linkedin' => 'http://www.linkedin.com/',
				'is_linkedin' => 1,
				'youtube' => 'http://www.youtube.com/',
				'is_youtube' => 1,
				'rss' => esc_url(add_query_arg('post_type', W2DC_POST_TYPE, home_url('feed'))),
				'is_rss' => 1,
				'visibility' => 1,
		);
		$instance = wp_parse_args((array) $instance, $defaults);
		
		w2dc_renderTemplate('widgets/social_widget_options.tpl.php', array('widget' => $this, 'instance' => $instance));
	}
	
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['facebook'] = (!empty($new_instance['facebook'])) ? strip_tags($new_instance['facebook']) : '';
		$instance['is_facebook'] = (!empty($new_instance['is_facebook'])) ? strip_tags($new_instance['is_facebook']) : '';
		$instance['twitter'] = (!empty($new_instance['twitter'])) ? strip_tags($new_instance['twitter']) : '';
		$instance['is_twitter'] = (!empty($new_instance['is_twitter'])) ? strip_tags($new_instance['is_twitter']) : '';
		$instance['google'] = (!empty($new_instance['google'])) ? strip_tags($new_instance['google']) : '';
		$instance['is_google'] = (!empty($new_instance['is_google'])) ? strip_tags($new_instance['is_google']) : '';
		$instance['linkedin'] = (!empty($new_instance['linkedin'])) ? strip_tags($new_instance['linkedin']) : '';
		$instance['is_linkedin'] = (!empty($new_instance['is_linkedin'])) ? strip_tags($new_instance['is_linkedin']) : '';
		$instance['youtube'] = (!empty($new_instance['youtube'])) ? strip_tags($new_instance['youtube']) : '';
		$instance['is_youtube'] = (!empty($new_instance['is_youtube'])) ? strip_tags($new_instance['is_youtube']) : '';
		$instance['rss'] = (!empty($new_instance['rss'])) ? strip_tags($new_instance['rss']) : '';
		$instance['is_rss'] = (!empty($new_instance['is_rss'])) ? strip_tags($new_instance['is_rss']) : '';
		$instance['visibility'] = (!empty($new_instance['visibility'])) ? strip_tags($new_instance['visibility']) : '';
	
		return $instance;
	}
	
	public function wp_enqueue_scripts() {
		$widget_options_all = get_option($this->option_name);
		if (isset($widget_options_all[$this->number])) {
			$current_widget_options = $widget_options_all[$this->number];
			if (is_active_widget(false, false, $this->id_base, true) && !$current_widget_options['visibility']) {
				global $w2dc_instance, $w2dc_fsubmit_instance, $w2dc_payments_instance, $w2dc_ratings_instance;
		
				$w2dc_instance->enqueue_scripts_styles(true);
				if ($w2dc_fsubmit_instance)
					$w2dc_fsubmit_instance->enqueue_scripts_styles(true);
				if ($w2dc_payments_instance)
					$w2dc_payments_instance->enqueue_scripts_styles(true);
				if ($w2dc_ratings_instance)
					$w2dc_ratings_instance->enqueue_scripts_styles(true);
			}
		}
	}
	
	public function enqueue_dynamic_css() {
		$widget_options_all = get_option($this->option_name);
		if (isset($widget_options_all[$this->number])) {
			$current_widget_options = $widget_options_all[$this->number];
			if (is_active_widget(false, false, $this->id_base, true) && !$current_widget_options['visibility']) {
				global $w2dc_instance;
					
				$w2dc_instance->enqueue_dynamic_css(true);
			}
		}
	}
}

?>