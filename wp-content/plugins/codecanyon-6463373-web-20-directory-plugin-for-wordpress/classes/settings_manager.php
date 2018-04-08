<?php

global $w2dc_wpml_dependent_options;
$w2dc_wpml_dependent_options[] = 'w2dc_listing_contact_form_7';
$w2dc_wpml_dependent_options[] = 'w2dc_directory_title';

class w2dc_settings_manager {
	public function __construct() {
		add_action('init', array($this, 'plugin_settings'));
		add_action('vp_w2dc_option_after_ajax_save', array($this, 'save_option'), 10, 3);
	}
	
	public function plugin_settings() {
		global $w2dc_instance, $w2dc_social_services, $w2dc_maps_styles, $sitepress;

		if ($w2dc_instance->index_page_id === 0 && isset($_GET['action']) && $_GET['action'] == 'directory_page_installation') {
			$page = array('post_status' => 'publish', 'post_title' => __('Web 2.0 Directory', 'W2DC'), 'post_type' => 'page', 'post_content' => '[webdirectory]', 'comment_status' => 'closed');
			if (wp_insert_post($page))
				w2dc_addMessage(__('"Web 2.0 Directory" page with [webdirectory] shortcode was successfully created, thank you!'));
		}

		$ordering_items = w2dc_orderingItems();
		
		$w2dc_social_services = array(
			'facebook' => array('value' => 'facebook', 'label' => __('Facebook', 'W2DC')),
			'twitter' => array('value' => 'twitter', 'label' => __('Twitter', 'W2DC')),
			'google' => array('value' => 'google', 'label' => __('Google+', 'W2DC')),
			'linkedin' => array('value' => 'linkedin', 'label' => __('LinkedIn', 'W2DC')),
			'digg' => array('value' => 'digg', 'label' => __('Digg', 'W2DC')),
			'reddit' => array('value' => 'reddit', 'label' => __('Reddit', 'W2DC')),
			'pinterest' => array('value' => 'pinterest', 'label' => __('Pinterest', 'W2DC')),
			'tumblr' => array('value' => 'tumblr', 'label' => __('Tumblr', 'W2DC')),
			'stumbleupon' => array('value' => 'stumbleupon', 'label' => __('StumbleUpon', 'W2DC')),
			'vk' => array('value' => 'vk', 'label' => __('VK', 'W2DC')),
			'email' => array('value' => 'email', 'label' => __('Email', 'W2DC')),
		);

		$listings_tabs = array(
				array('value' => 'addresses-tab', 'label' => __('Addresses tab', 'W2DC')),
				array('value' => 'comments-tab', 'label' => __('Comments tab', 'W2DC')),
				array('value' => 'videos-tab', 'label' => __('Videos tab', 'W2DC')),
				array('value' => 'contact-tab', 'label' => __('Contact tab', 'W2DC')));
		foreach ($w2dc_instance->content_fields->content_fields_groups_array AS $fields_group)
			if ($fields_group->on_tab)
				$listings_tabs[] = array('value' => 'field-group-tab-'.$fields_group->id, 'label' => $fields_group->name);
			
		$map_styles = array(array('value' => 'default', 'label' => 'Default style'));
		foreach ($w2dc_maps_styles AS $name=>$style)
			$map_styles[] = array('value' => $name, 'label' => $name);

		$country_codes = array(array('value' => 0, 'label' => 'Worldwide'));
		$w2dc_country_codes = w2dc_country_codes();
		foreach ($w2dc_country_codes AS $country=>$code)
			$country_codes[] = array('value' => $code, 'label' => $country);
		
		$theme_options = array(
				//'is_dev_mode' => true,
				'option_key' => 'vpt_option',
				'page_slug' => 'w2dc_settings',
				'template' => array(
					'title' => __('Web 2.0 Directory Settings', 'W2DC'),
					'logo' => W2DC_RESOURCES_URL . 'images/settings.png',
					'menus' => array(
						'general' => array(
							'name' => 'general',
							'title' => __('General settings', 'W2DC'),
							'icon' => 'font-awesome:w2dc-fa-home',
							'controls' => array(
								'addons' => array(
									'type' => 'section',
									'title' => __('Addons', 'W2DC'),
									'description' => __('Refresh this page after switch off/on any addons.', 'W2GM'),
									'fields' => array(
									 	array(
											'type' => 'toggle',
											'name' => 'w2dc_fsubmit_addon',
											'label' => __('Frontend submission & dashboard addon', 'W2DC'),
									 		'description' => __('Allow users to submit new listings at the frontend side of your site, also provides users dashboard functionality.', 'W2DC'),
											'default' => get_option('w2dc_fsubmit_addon'),
										),
									 	array(
											'type' => 'toggle',
											'name' => 'w2dc_payments_addon',
											'label' => __('Payments addon', 'W2DC'),
									 		'description' => __('Includes payments processing and invoices management functionality into directory/classifieds website.', 'W2DC'),
											'default' => get_option('w2dc_payments_addon'),
										),
									 	array(
											'type' => 'toggle',
											'name' => 'w2dc_ratings_addon',
											'label' => __('Ratings addon', 'W2DC'),
									 		'description' => __('Ability to place ratings for listings, then manage these ratings by listings owners.', 'W2DC'),
											'default' => get_option('w2dc_ratings_addon'),
										),
									),
								),
								'ajax_loading' => array(
									'type' => 'section',
									'title' => __('AJAX loading', 'W2DC'),
									'fields' => array(
									 	array(
											'type' => 'toggle',
											'name' => 'w2dc_ajax_load',
											'label' => __('Use AJAX loading', 'W2DC'),
									 		'description' => __('Load maps and listings using AJAX when click on search button, sorting buttons, pagination buttons.', 'W2DC'),
											'default' => get_option('w2dc_ajax_load'),
										),
									 	array(
											'type' => 'toggle',
											'name' => 'w2dc_ajax_initial_load',
											'label' => __('Initial AJAX loading', 'W2DC'),
									 		'description' => __('Initially load listings only after the page was completely loaded (not recommended).', 'W2DC'),
											'default' => get_option('w2dc_ajax_initial_load'),
										),
									 	array(
											'type' => 'toggle',
											'name' => 'w2dc_show_more_button',
											'label' => __('Display "Show More Listings" button instead of default paginator', 'W2DC'),
											'default' => get_option('w2dc_show_more_button'),
										),
									),
								),
								'title_slugs' => array(
									'type' => 'section',
									'title' => __('Titles, Slugs & Permalinks', 'W2DC'),
									'fields' => array(
									 	array(
											'type' => 'textbox',
											'name' => w2dc_get_wpml_dependent_option_name('w2dc_directory_title'), // adapted for WPML
											'label' => __('Directory title', 'W2DC'),
									 		'description' => w2dc_get_wpml_dependent_option_description(),
											'default' => w2dc_get_wpml_dependent_option('w2dc_directory_title'),  // adapted for WPML
										),
										array(
											'type' => 'notebox',
											'label' => __('Notice about slugs:', 'W2DC'),
											'description' => sprintf(__('You can manage listings, categories, locations and tags slugs in <a href="%s">directories settings</a>', 'W2DC'), admin_url('admin.php?page=w2dc_directories')),
											'status' => 'warning',
										),
									 	/* array(
											'type' => 'textbox',
											'name' => 'w2dc_listing_slug',
											'label' => __('Listing slug', 'W2DC'),
											'default' => get_option('w2dc_listing_slug'),
									 		'validation' => 'required',
										),
									 	array(
											'type' => 'textbox',
											'name' => 'w2dc_category_slug',
											'label' => __('Category slug', 'W2DC'),
											'default' => get_option('w2dc_category_slug'),
									 		'validation' => 'required',
										),
									 	array(
											'type' => 'textbox',
											'name' => 'w2dc_location_slug',
											'label' => __('Location slug', 'W2DC'),
											'default' => get_option('w2dc_location_slug'),
									 		'validation' => 'required',
										),
									 	array(
											'type' => 'textbox',
											'name' => 'w2dc_tag_slug',
											'label' => __('Tag slug', 'W2DC'),
											'default' => get_option('w2dc_tag_slug'),
									 		'validation' => 'required',
										), */
										array(
											'type' => 'radiobutton',
											'name' => 'w2dc_permalinks_structure',
											'label' => __('Listings permalinks structure', 'W2DC'),
											'description' => __('<b>/%postname%/</b> works only when directory page is not front page.<br /><b>/%post_id%/%postname%/</b> will not work when the same structure was enabled for native WP posts.', 'W2DC'),
											'default' => array(get_option('w2dc_permalinks_structure')),
											'items' => array(
													array(
														'value' => 'postname',
														'label' => __('/%postname%/', 'W2DC'),	
													),
													array(
														'value' => 'post_id',
														'label' => __('/%post_id%/%postname%/', 'W2DC'),	
													),
													array(
														'value' => 'listing_slug',
														'label' => __('/%listing_slug%/%postname%/', 'W2DC'),	
													),
													array(
														'value' => 'category_slug',
														'label' => __('/%listing_slug%/%category%/%postname%/', 'W2DC'),	
													),
													array(
														'value' => 'location_slug',
														'label' => __('/%listing_slug%/%location%/%postname%/', 'W2DC'),	
													),
													array(
														'value' => 'tag_slug',
														'label' => __('/%listing_slug%/%tag%/%postname%/', 'W2DC'),	
													),
											),
										),
									),
								),
							),
						),
						'listings' => array(
							'name' => 'listings',
							'title' => __('Listings', 'W2DC'),
							'icon' => 'font-awesome:w2dc-fa-list-alt',
							'controls' => array(
								'listings' => array(
									'type' => 'section',
									'title' => __('Listings settings', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'toggle',
											'name' => 'w2dc_listings_on_index',
											'label' => __('Show listings on index page', 'W2DC'),
											'default' => get_option('w2dc_listings_on_index'),
										),
										array(
											'type' => 'textbox',
											'name' => 'w2dc_listings_number_index',
											'label' => __('Number of listings on index page', 'W2DC'),
											'default' => get_option('w2dc_listings_number_index'),
											'validation' => 'required|numeric',
										),
										array(
											'type' => 'textbox',
											'name' => 'w2dc_listings_number_excerpt',
											'label' => __('Number of listings on excerpt pages (categories, locations, tags, search results)', 'W2DC'),
											'default' => get_option('w2dc_listings_number_excerpt'),
											'validation' => 'required|numeric',
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_listing_contact_form',
											'label' => __('Enable contact form on listing page', 'W2DC'),
											'description' => __('Contact Form 7 or standard form will be displayed on each listing page', 'W2DC'),
											'default' => get_option('w2dc_listing_contact_form'),
										),
										array(
											'type' => 'textbox',
											'name' => w2dc_get_wpml_dependent_option_name('w2dc_listing_contact_form_7'),
											'label' => __('Contact Form 7 shortcode', 'W2DC'),
											'description' => __('This will work only when Contact Form 7 plugin enabled, otherwise standard contact form will be displayed.', 'W2DC') . w2dc_get_wpml_dependent_option_description(),
											'default' => w2dc_get_wpml_dependent_option('w2dc_listing_contact_form_7'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_custom_contact_email',
											'label' => __('Allow custom contact emails', 'W2DC'),
											'description' => __('When enabled users may set up custom contact emails, otherwise messages will be sent directly to authors emails', 'W2DC'),
											'default' => get_option('w2dc_custom_contact_email'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_favourites_list',
											'label' => __('Enable bookmarks list', 'W2DC'),
											'default' => get_option('w2dc_favourites_list'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_print_button',
											'label' => __('Show print listing button', 'W2DC'),
											'default' => get_option('w2dc_print_button'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_pdf_button',
											'label' => __('Show listing in PDF button', 'W2DC'),
											'default' => get_option('w2dc_pdf_button'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_change_expiration_date',
											'label' => __('Allow regular users to change listings expiration dates', 'W2DC'),
											'default' => get_option('w2dc_change_expiration_date'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_hide_comments_number_on_index',
											'label' => __('Hide comments number on index and excerpt pages', 'W2DC'),
											'default' => get_option('w2dc_hide_comments_number_on_index'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_hide_listings_creation_date',
											'label' => __('Hide listings creation date', 'W2DC'),
											'default' => get_option('w2dc_hide_listings_creation_date'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_hide_author_link',
											'label' => __('Hide author information', 'W2DC'),
											'description' => __('Author name and possible link to author website will be hidden on single listing pages.', 'W2DC'),
											'default' => get_option('w2dc_hide_author_link'),
										),
										array(
											'type' => 'radiobutton',
											'name' => 'w2dc_listings_comments_mode',
											'label' => __('Listings comments mode', 'W2DC'),
											'default' => array(get_option('w2dc_listings_comments_mode')),
											'items' => array(
													array(
														'value' => 'enabled',
														'label' => __('Always enabled', 'W2DC'),	
													),
													array(
														'value' => 'disabled',
														'label' => __('Always disabled', 'W2DC'),	
													),
													array(
														'value' => 'wp_settings',
														'label' => __('As configured in WP settings', 'W2DC'),	
													),
											),
										),
										array(
											'type' => 'sorter',
											'name' => 'w2dc_listings_tabs_order',
											'label' => __('Priority of opening of listing tabs', 'W2DC'),
									 		'items' => $listings_tabs,
											'description' => __('Set up priority of tabs those are opened by default. If any listing does not have any tab - next tab in the order will be opened by default.'),
											'default' => get_option('w2dc_listings_tabs_order'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_stats',
											'label' => __('Enable statistics functionality', 'W2DC'),
											'default' => get_option('w2dc_enable_stats'),
										),
									),
								),
								'breadcrumbs' => array(
									'type' => 'section',
									'title' => __('Breadcrumbs settings', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_breadcrumbs',
											'label' => __('Enable breadcrumbs', 'W2DC'),
											'default' => get_option('w2dc_enable_breadcrumbs'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_hide_home_link_breadcrumb',
											'label' => __('Hide home link in breadcrumbs', 'W2DC'),
											'default' => get_option('w2dc_hide_home_link_breadcrumb'),
										),
										array(
											'type' => 'radiobutton',
											'name' => 'w2dc_breadcrumbs_mode',
											'label' => __('Breadcrumbs mode on listing single page', 'W2DC'),
											'default' => array(get_option('w2dc_breadcrumbs_mode')),
											'items' => array(
													array(
														'value' => 'title',
														'label' => __('%listing title%', 'W2DC'),	
													),
													array(
														'value' => 'category',
														'label' => __('%category% » %listing title%', 'W2DC'),	
													),
													array(
														'value' => 'location',
														'label' => __('%location% » %listing title%', 'W2DC'),	
													),
											),
										),
									),
								),
								'logos' => array(
									'type' => 'section',
									'title' => __('Listings logos & images', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_lighbox_gallery',
											'label' => __('Enable lightbox on images gallery', 'W2DC'),
											'default' => get_option('w2dc_enable_lighbox_gallery'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_auto_slides_gallery',
											'label' => __('Enable automatic rotating slideshow on images gallery', 'W2DC'),
											'default' => get_option('w2dc_auto_slides_gallery'),
										),
										array(
											'type' => 'textbox',
											'name' => 'w2dc_auto_slides_gallery_delay',
											'label' => __('The delay in rotation (in ms)', 'W2DC'),
											'default' => get_option('w2dc_auto_slides_gallery_delay'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_exclude_logo_from_listing',
											'label' => __('Exclude logo image from images gallery on single listing page', 'W2DC'),
											'default' => get_option('w2dc_exclude_logo_from_listing'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_nologo',
											'label' => __('Enable default logo image', 'W2DC'),
											'default' => get_option('w2dc_enable_nologo'),
										),
										array(
											'type' => 'upload',
											'name' => 'w2dc_nologo_url',
											'label' => __('Default logo image', 'W2DC'),
									 		'description' => __('This image will appear when listing owner did not upload own logo.', 'W2DC'),
											'default' => get_option('w2dc_nologo_url'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_100_single_logo_width',
											'label' => __('100% width of images gallery on single listing page', 'W2DC'),
											'default' => get_option('w2dc_100_logo_width'),
										),
										array(
											'type' => 'slider',
											'name' => 'w2dc_single_logo_width',
											'label' => __('Images gallery width on single listing page (in pixels)', 'W2DC'),
											'description' => __('This option needed only when 100% width of images gallery is switched off'),
											'min' => 100,
											'max' => 800,
											'default' => get_option('w2dc_single_logo_width'),
										),
										array(
											'type' => 'radiobutton',
											'name' => 'w2dc_big_slide_bg_mode',
											'label' => __('Images gallery main slide mode', 'W2DC'),
											'default' => array(get_option('w2dc_big_slide_bg_mode')),
											'items' => array(
													array(
														'value' => 'cover',
														'label' => __('Cut off image to fit width and height of main slide', 'W2DC'),	
													),
													array(
														'value' => 'contain',
														'label' => __('Full image inside main slide', 'W2DC'),	
													),
											),
										),
									),
								),
								'excerpts' => array(
									'type' => 'section',
									'title' => __('Description & Excerpt settings', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_description',
											'label' => __('Enable description field', 'W2DC'),
											'default' => get_option('w2dc_enable_description'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_summary',
											'label' => __('Enable summary field', 'W2DC'),
											'default' => get_option('w2dc_enable_summary'),
										),
										array(
											'type' => 'textbox',
											'name' => 'w2dc_excerpt_length',
											'label' => __('Excerpt max length', 'W2DC'),
											'description' => __('Insert the number of words you want to show in the listings excerpts', 'W2DC'),
											'default' => get_option('w2dc_excerpt_length'),
											'validation' => 'required|numeric',
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_cropped_content_as_excerpt',
											'label' => __('Use cropped content as excerpt', 'W2DC'),
											'description' => __('When excerpt field is empty - use cropped main content', 'W2DC'),
											'default' => get_option('w2dc_cropped_content_as_excerpt'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_strip_excerpt',
											'label' => __('Strip HTML from excerpt', 'W2DC'),
											'description' => __('Check the box if you want to strip HTML from the excerpt content only', 'W2DC'),
											'default' => get_option('w2dc_strip_excerpt'),
										),
									),
								),
							),
						),
						'pages_views' => array(
							'name' => 'pages_views',
							'title' => __('Pages & Views', 'W2DC'),
							'icon' => 'font-awesome:w2dc-fa-external-link ',
							'controls' => array(
								'excerpt_views' => array(
									'type' => 'section',
									'title' => __('Excerpt views', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'toggle',
											'name' => 'w2dc_views_switcher',
											'label' => __('Enable views switcher', 'W2DC'),
											'default' => get_option('w2dc_views_switcher'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_listings_count',
											'label' => __('Show listings number', 'W2DC'),
											'default' => get_option('w2dc_show_listings_count'),
										),
										array(
											'type' => 'radiobutton',
											'name' => 'w2dc_views_switcher_default',
											'label' => __('Listings view by default', 'W2DC'),
											'description' => __('Do not forget that selected view will be stored in cookies', 'W2DC'),
											'default' => array(get_option('w2dc_views_switcher_default')),
											'items' => array(
													array(
														'value' => 'list',
														'label' => __('List view', 'W2DC'),
													),
													array(
														'value' => 'grid',
														'label' => __('Grid view', 'W2DC'),
													),
											),
										),
										array(
											'type' => 'slider',
											'name' => 'w2dc_views_switcher_grid_columns',
											'label' => __('Number of columns for listings Grid View', 'W2DC'),
											'min' => 1,
											'max' => 4,
											'default' => get_option('w2dc_views_switcher_grid_columns'),
										),
										array(
											'type' => 'radiobutton',
											'name' => 'w2dc_grid_view_logo_ratio',
											'label' => __('Aspect ratio of logo in Grid View', 'W2DC'),
											'default' => array(get_option('w2dc_grid_view_logo_ratio')),
											'items' => array(
													array(
														'value' => '100',
														'label' => __('1:1 (square)', 'W2DC'),
													),
													array(
														'value' => '75',
														'label' => __('4:3', 'W2DC'),
													),
													array(
														'value' => '56.25',
														'label' => __('16:9', 'W2DC'),
													),
													array(
														'value' => '50',
														'label' => __('2:1', 'W2DC'),
													),
											),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_wrap_logo_list_view',
											'label' => __('Wrap logo image by text content in List View', 'W2DC'),
											'default' => get_option('w2dc_wrap_logo_list_view'),
										),
										array(
											'type' => 'slider',
											'name' => 'w2dc_listing_thumb_width',
											'label' => __('Listing thumbnail logo width (in pixels) in List View', 'W2DC'),
											'min' => '70',
											'max' => '640',
											'default' => '290',
										),
									),
								),
								'categories' => array(
									'type' => 'section',
									'title' => __('Categories settings', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_categories_index',
											'label' => __('Show categories list on index and excerpt pages?', 'W2DC'),
											'default' => get_option('w2dc_show_categories_index'),
										),
										array(
											'type' => 'slider',
											'name' => 'w2dc_categories_nesting_level',
											'label' => __('Categories nesting level', 'W2DC'),
											'min' => 1,
											'max' => 2,
											'default' => get_option('w2dc_categories_nesting_level'),
										),
										array(
											'type' => 'slider',
											'name' => 'w2dc_categories_columns',
											'label' => __('Categories columns number', 'W2DC'),
											'min' => 1,
											'max' => 4,
											'default' => get_option('w2dc_categories_columns'),
										),
										array(
											'type' => 'textbox',
											'name' => 'w2dc_subcategories_items',
											'label' => __('Show subcategories items number', 'W2DC'),
											'description' => __('Leave 0 to show all subcategories', 'W2DC'),
											'default' => get_option('w2dc_subcategories_items'),
											'validation' => 'numeric',
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_category_count',
											'label' => __('Show category listings count?', 'W2DC'),
											'default' => get_option('w2dc_show_category_count'),
										),
									),
								),
								'locations' => array(
									'type' => 'section',
									'title' => __('Locations settings', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_locations_index',
											'label' => __('Show locations list on index and excerpt pages?', 'W2DC'),
											'default' => get_option('w2dc_show_locations_index'),
										),
										array(
											'type' => 'slider',
											'name' => 'w2dc_locations_nesting_level',
											'label' => __('Locations nesting level', 'W2DC'),
											'min' => 1,
											'max' => 2,
											'default' => get_option('w2dc_locations_nesting_level'),
										),
										array(
											'type' => 'slider',
											'name' => 'w2dc_locations_columns',
											'label' => __('Locations columns number', 'W2DC'),
											'min' => 1,
											'max' => 4,
											'default' => get_option('w2dc_locations_columns'),
										),
										array(
											'type' => 'textbox',
											'name' => 'w2dc_sublocations_items',
											'label' => __('Show sublocations items number', 'W2DC'),
											'description' => __('Leave 0 to show all sublocations', 'W2DC'),
											'default' => get_option('w2dc_sublocations_items'),
											'validation' => 'numeric',
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_location_count',
											'label' => __('Show location listings count?', 'W2DC'),
											'default' => get_option('w2dc_show_locations_count'),
										),
									),
								),
								'sorting' => array(
									'type' => 'section',
									'title' => __('Sorting settings', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_orderby_links',
											'label' => __('Show order by links block', 'W2DC'),
											'default' => get_option('w2dc_show_orderby_links'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_orderby_date',
											'label' => __('Allow sorting by date', 'W2DC'),
											'default' => get_option('w2dc_orderby_date'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_orderby_title',
											'label' => __('Allow sorting by title', 'W2DC'),
											'default' => get_option('w2dc_orderby_title'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_orderby_distance',
											'label' => __('Allow sorting by distance when search by radius', 'W2DC'),
											'default' => get_option('w2dc_orderby_distance'),
										),
										array(
											'type' => 'select',
											'name' => 'w2dc_default_orderby',
											'label' => __('Default order by', 'W2DC'),
											'items' => $ordering_items,
											'default' => get_option('w2dc_default_orderby'),
										),
										array(
											'type' => 'select',
											'name' => 'w2dc_default_order',
											'label' => __('Default order direction', 'W2DC'),
											'items' => array(
												array(
													'value' => 'ASC',
													'label' => __('Ascending', 'W2DC'),
												),
												array(
													'value' => 'DESC',
													'label' => __('Descending', 'W2DC'),
												),
											),
											'default' => get_option('w2dc_default_order'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_orderby_exclude_null',
											'label' => __('Exclude listings with empty values from sorted results', 'W2DC'),
											'default' => get_option('w2dc_orderby_exclude_null'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_orderby_sticky_featured',
											'label' => __('Sticky and featured listings always will be on top', 'W2DC'),
											'description' => __('When switched off - sticky and featured listings will be on top only when listings were sorted by date.', 'W2DC'),
											'default' => get_option('w2dc_orderby_sticky_featured'),
										),
									),
								),
							),
						),
						'search' => array(
							'name' => 'search',
							'title' => __('Search settings', 'W2DC'),
							'icon' => 'font-awesome:w2dc-fa-search',
							'controls' => array(
								'search' => array(
									'type' => 'section',
									'title' => __('Search settings', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'toggle',
											'name' => 'w2dc_main_search',
											'label' => __('Display search block in main part of page?', 'W2DC'),
											'description' => __('Note, that search widget is independent from this setting and this widget renders on each page where main search block was hidden', 'W2DC'),
											'default' => get_option('w2dc_main_search'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_what_search',
											'label' => __('Show "What search" section?', 'W2DC'),
											'description' => __('This setting is actual for both: main search block and widget', 'W2DC'),
											'default' => get_option('w2dc_show_what_search'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_where_search',
											'label' => __('Show "Where search" section?', 'W2DC'),
											'description' => __('This setting is actual for both: main search block and widget', 'W2DC'),
											'default' => get_option('w2dc_show_where_search'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_keywords_search',
											'label' => __('Show keywords search?', 'W2DC'),
											'description' => __('This setting is actual for both: main search block and widget', 'W2DC'),
											'default' => get_option('w2dc_show_keywords_search'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_locations_search',
											'label' => __('Show locations search?', 'W2DC'),
											'description' => __('This setting is actual for both: main search block and widget', 'W2DC'),
											'default' => get_option('w2dc_show_locations_search'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_address_search',
											'label' => __('Show address search?', 'W2DC'),
											'description' => __('This setting is actual for both: main search block and widget', 'W2DC'),
											'default' => get_option('w2dc_show_address_search'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_location_count_in_search',
											'label' => __('Show listings counts in locations search dropboxes?', 'W2DC'),
											'default' => get_option('w2dc_show_location_count_in_search'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_categories_search',
											'label' => __('Show categories search?', 'W2DC'),
											'description' => __('This setting is actual for both: main search block and widget', 'W2DC'),
											'default' => get_option('w2dc_show_categories_search'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_category_count_in_search',
											'label' => __('Show listings counts in categories search dropboxes?', 'W2DC'),
											'default' => get_option('w2dc_show_category_count_in_search'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_show_radius_search',
											'label' => __('Show locations radius search?', 'W2DC'),
											'description' => __('This setting is actual for both: main search block and widget.', 'W2DC') . ' ' . sprintf(__('Check geolocation <a href="%s">response</a>.', 'W2DC'), admin_url('admin.php?page=w2dc_debug')),
											'default' => get_option('w2dc_show_radius_search'),
										),
										array(
											'type' => 'radiobutton',
											'name' => 'w2dc_miles_kilometers_in_search',
											'label' => __('Dimension in radius search', 'W2DC'),
											'description' => __('This setting is actual for both: main search block and widget', 'W2DC'),
											'items' => array(
												array(
													'value' => 'miles',
													'label' => __('miles', 'W2DC'),
												),
												array(
													'value' => 'kilometers',
													'label' => __('kilometers', 'W2DC'),
												),
											),
											'default' => array(get_option('w2dc_miles_kilometers_in_search')),
										),
										array(
											'type' => 'textbox',
											'name' => 'w2dc_radius_search_min',
											'label' => __('Minimum radius search', 'W2DC'),
											'default' => get_option('w2dc_radius_search_min'),
											'validation' => 'required|numeric',
										),
										array(
											'type' => 'textbox',
											'name' => 'w2dc_radius_search_max',
											'label' => __('Maximum radius search', 'W2DC'),
											'default' => get_option('w2dc_radius_search_max'),
											'validation' => 'required|numeric',
										),
										array(
											'type' => 'textbox',
											'name' => 'w2dc_radius_search_default',
											'label' => __('Default radius search', 'W2DC'),
											'description' => __('If you have problems with radius search: check your server google API key and enabled APIs. ', 'W2DC') . ' ' . sprintf(__('Check geolocation <a href="%s">response</a>.', 'W2DC'), admin_url('admin.php?page=w2dc_debug')),
											'default' => get_option('w2dc_radius_search_default'),
											'validation' => 'required|numeric',
										),
									),
								),
							),
						),
						'maps' => array(
							'name' => 'maps',
							'title' => __('Maps & Addresses', 'W2DC'),
							'icon' => 'font-awesome:w2dc-fa-map-marker',
							'controls' => array(
								'maps' => array(
									'type' => 'section',
									'title' => __('Maps settings', 'W2DC'),
									'fields' => array(
									 	array(
											'type' => 'toggle',
											'name' => 'w2dc_map_on_index',
											'label' => __('Show map on index page?', 'W2DC'),
											'default' => get_option('w2dc_map_on_index'),
										),
									 	array(
											'type' => 'toggle',
											'name' => 'w2dc_map_on_excerpt',
											'label' => __('Show map on excerpt page?', 'W2DC'),
											'default' => get_option('w2dc_map_on_excerpt'),
										),
										array(
											'type' => 'radiobutton',
											'name' => 'w2dc_map_markers_is_limit',
											'label' => __('How many map markers to display on the map', 'W2DC'),
											'items' => array(
												array(
													'value' => 1,
													'label' =>__('The only map markers of visible listings will be displayed', 'W2DC'),
												),
												array(
													'value' => 0,
													'label' =>__('Display all map markers (lots of markers on one page may slow down page loading)', 'W2DC'),
												),
											),
											'default' => array(
													get_option('w2dc_map_markers_is_limit')
											),
										),
									 	array(
											'type' => 'toggle',
											'name' => 'w2dc_show_directions',
											'label' => __('Show directions panel for individual listing map?', 'W2DC'),
											'default' => get_option('w2dc_show_directions'),
										),
										array(
											'type' => 'radiobutton',
											'name' => 'w2dc_directions_functionality',
											'label' => __('Directions functionality', 'W2DC'),
											'items' => array(
												array(
													'value' => 'builtin',
													'label' =>__('Built-in routing', 'W2DC'),
												),
												array(
													'value' => 'google',
													'label' =>__('Link to Google Maps', 'W2DC'),
												),
											),
											'default' => array(
													get_option('w2dc_directions_functionality')
											),
										),
									 	array(
											'type' => 'slider',
											'name' => 'w2dc_default_map_zoom',
											'label' => __('Default Google Maps zoom level (for submission page)', 'W2DC'),
									 		'min' => 1,
									 		'max' => 19,
											'default' => get_option('w2dc_default_map_zoom'),
										),
									 	array(
											'type' => 'select',
											'name' => 'w2dc_map_style',
											'label' => __('Google Maps style', 'W2DC'),
									 		'items' => $map_styles,
											'default' => array(get_option('w2dc_map_style')),
										),
										array(
											'type' => 'textbox',
											'name' => 'w2dc_default_map_height',
											'label' => __('Default map height (in pixels)', 'W2DC'),
											'default' => get_option('w2dc_default_map_height'),
											'validation' => 'required|numeric',
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_radius_search_circle',
											'label' => __('Show circle during radius search?', 'W2DC'),
											'default' => get_option('w2dc_enable_radius_search_circle'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_clusters',
											'label' => __('Enable clusters of map markers?', 'W2DC'),
											'default' => get_option('w2dc_enable_clusters'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_google_maps_required',
											'label' => __('Make map markers mandatory during submission of listings', 'W2DC'),
											'default' => get_option('w2dc_google_maps_required'),
										),
									),
								),
								'maps_controls' => array(
									'type' => 'section',
									'title' => __('Maps controls settings', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_draw_panel',
											'label' => __('Enable Draw Panel', 'W2DC'),
											'description' => __('Very important: MySQL version must be 5.6.1 and higher or MySQL server variable "thread stack" must be 256K and higher. Ask your hoster about it if "Draw Area" does not work.', 'W2DC'),
											'default' => get_option('w2dc_enable_draw_panel'),
										),
									 	array(
											'type' => 'toggle',
											'name' => 'w2dc_search_on_map',
											'label' => __('Show compact search form on the map?', 'W2DC'),
											'default' => get_option('w2dc_search_on_map'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_full_screen',
											'label' => __('Enable full screen button', 'W2DC'),
											'default' => get_option('w2dc_enable_full_screen'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_wheel_zoom',
											'label' => __('Enable zoom by mouse wheel', 'W2DC'),
											'description' => __('For desktops', 'W2DC'),
											'default' => get_option('w2dc_enable_wheel_zoom'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_dragging_touchscreens',
											'label' => __('Enable map dragging on touch screen devices', 'W2DC'),
											'default' => get_option('w2dc_enable_dragging_touchscreens'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_center_map_onclick',
											'label' => __('Center map on marker click', 'W2DC'),
											'default' => get_option('w2dc_center_map_onclick'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_hide_search_on_map_mobile',
											'label' => __('Hide compact search form on the map for mobile devices', 'W2DC'),
											'description' => __('This setting for all maps', 'W2DC'),
											'default' => get_option('w2dc_hide_search_on_map_mobile'),
										),
									),
								),
								'addresses' => array(
									'type' => 'section',
									'title' => __('Addresses settings', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'textbox',
											'name' => 'w2dc_default_geocoding_location',
											'label' => __('Default country/state for correct geocoding', 'W2DC'),
											'description' => __('This value needed when you build local diirectory, all your listings place in one local area - country or state. You do not want to set countries or states in the search, so this hidden string will be automatically added to the address for correct geocoding when you create/edit listings.', 'W2DC'),
											'default' => get_option('w2dc_default_geocoding_location'),
										),
										array(
											'type' => 'sorter',
											'name' => 'w2dc_addresses_order',
											'label' => __('Order of address lines', 'W2DC'),
									 		'items' => array(
									 			array('value' => 'location', 'label' => __('Selected location', 'W2DC')),
									 			array('value' => 'line_1', 'label' => __('Address Line 1', 'W2DC')),
									 			array('value' => 'line_2', 'label' => __('Address Line 2', 'W2DC')),
									 			array('value' => 'zip', 'label' => __('Zip code or postal index', 'W2DC')),
									 			array('value' => 'space1', 'label' => __('-- Space ( ) --', 'W2DC')),
									 			array('value' => 'space2', 'label' => __('-- Space ( ) --', 'W2DC')),
									 			array('value' => 'space3', 'label' => __('-- Space ( ) --', 'W2DC')),
									 			array('value' => 'comma1', 'label' => __('-- Comma (,) --', 'W2DC')),
									 			array('value' => 'comma2', 'label' => __('-- Comma (,) --', 'W2DC')),
									 			array('value' => 'comma3', 'label' => __('-- Comma (,) --', 'W2DC')),
									 			array('value' => 'break1', 'label' => __('-- Line Break --', 'W2DC')),
									 			array('value' => 'break2', 'label' => __('-- Line Break --', 'W2DC')),
									 		),
											'description' => __('Order address elements as you wish, commas and spaces help to build address line.'),
											'default' => get_option('w2dc_addresses_order'),
										),
										array(
											'type' => 'select',
											'name' => 'w2dc_address_autocomplete_code',
											'label' => __('Restriction of address fields for the default country (autocomplete and search)', 'W2DC'),
									 		'items' => $country_codes,
											'default' => get_option('w2dc_address_autocomplete_code'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_address_line_1',
											'label' => __('Enable address line 1 field', 'W2DC'),
											'default' => get_option('w2dc_enable_address_line_1'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_address_line_2',
											'label' => __('Enable address line 2 field', 'W2DC'),
											'default' => get_option('w2dc_enable_address_line_2'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_postal_index',
											'label' => __('Enable zip code', 'W2DC'),
											'default' => get_option('w2dc_enable_postal_index'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_additional_info',
											'label' => __('Enable additional info field', 'W2DC'),
											'default' => get_option('w2dc_enable_additional_info'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_manual_coords',
											'label' => __('Enable manual coordinates fields', 'W2DC'),
											'default' => get_option('w2dc_enable_manual_coords'),
										),
									),
								),
								'markers' => array(
									'type' => 'section',
									'title' => __('Map markers & InfoWindow settings', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'radiobutton',
											'name' => 'w2dc_map_markers_type',
											'label' => __('Type of Map Markers', 'W2DC'),
											'items' => array(
												array(
													'value' => 'icons',
													'label' =>__('Font Awesome icons (recommended)', 'W2DC'),
												),
												array(
													'value' => 'images',
													'label' =>__('PNG images', 'W2DC'),
												),
											),
											'default' => array(
													get_option('w2dc_map_markers_type')
											),
										),
										array(
											'type' => 'color',
											'name' => 'w2dc_default_marker_color',
											'label' => __('Default Map Marker color', 'W2DC'),
											'default' => get_option('w2dc_default_marker_color'),
											'description' => __('For Font Awesome icons.', 'W2DC'),
										),
										array(
											'type' => 'fontawesome',
											'name' => 'w2dc_default_marker_icon',
											'label' => __('Default Map Marker icon'),
											'description' => __('For Font Awesome icons.', 'W2DC'),
											'default' => array(
												get_option('w2dc_default_marker_icon')
											),
										),
										array(
											'type' => 'slider',
											'name' => 'w2dc_map_marker_width',
											'label' => __('Map marker width (in pixels)', 'W2DC'),
											'description' => __('For PNG images.', 'W2DC'),
											'default' => get_option('w2dc_map_marker_width'),
									 		'min' => 10,
									 		'max' => 64,
										),
									 	array(
											'type' => 'slider',
											'name' => 'w2dc_map_marker_height',
											'label' => __('Map marker height (in pixels)', 'W2DC'),
									 		'description' => __('For PNG images.', 'W2DC'),
											'default' => get_option('w2dc_map_marker_height'),
									 		'min' => 10,
									 		'max' => 64,
										),
									 	array(
											'type' => 'slider',
											'name' => 'w2dc_map_marker_anchor_x',
											'label' => __('Map marker anchor horizontal position (in pixels)', 'W2DC'),
									 		'description' => __('For PNG images.', 'W2DC'),
											'default' => get_option('w2dc_map_marker_anchor_x'),
									 		'min' => 0,
									 		'max' => 64,
										),
									 	array(
											'type' => 'slider',
											'name' => 'w2dc_map_marker_anchor_y',
											'label' => __('Map marker anchor vertical position (in pixels)', 'W2DC'),
									 		'description' => __('For PNG images.', 'W2DC'),
											'default' => get_option('w2dc_map_marker_anchor_y'),
									 		'min' => 0,
									 		'max' => 64,
										),
									 	array(
											'type' => 'slider',
											'name' => 'w2dc_map_infowindow_width',
											'label' => __('Map InfoWindow width (in pixels)', 'W2DC'),
											'default' => get_option('w2dc_map_infowindow_width'),
									 		'min' => 100,
									 		'max' => 600,
									 		'step' => 10,
										),
										array(
											'type' => 'slider',
											'name' => 'w2dc_map_infowindow_offset',
											'label' => __('Map InfoWindow vertical position above marker (in pixels)', 'W2DC'),
											'default' => get_option('w2dc_map_infowindow_offset'),
									 		'min' => 30,
									 		'max' => 120,
										),
										array(
											'type' => 'slider',
											'name' => 'w2dc_map_infowindow_logo_width',
											'label' => __('Map InfoWindow logo width (in pixels)', 'W2DC'),
											'default' => get_option('w2dc_map_infowindow_logo_width'),
									 		'min' => 40,
									 		'max' => 300,
											'step' => 10,
										),
									),
								),
							),
						),
						'notifications' => array(
							'name' => 'notifications',
							'title' => __('Email notifications', 'W2DC'),
							'icon' => 'font-awesome:w2dc-fa-envelope',
							'controls' => array(
								'notifications' => array(
									'type' => 'section',
									'title' => __('Email notifications', 'W2DC'),
									'fields' => array(
									 	array(
											'type' => 'textbox',
											'name' => 'w2dc_send_expiration_notification_days',
											'label' => __('Days before pre-expiration notification will be sent', 'W2DC'),
											'default' => get_option('w2dc_send_expiration_notification_days'),
										),
										array(
											'type' => 'textbox',
											'name' => 'w2dc_admin_notifications_email',
											'label' => __('This email will be used for notifications to admin and in "From" field. Required to send emails.', 'W2DC'),
											'default' => get_option('w2dc_admin_notifications_email'),
										),
									 	array(
											'type' => 'textarea',
											'name' => 'w2dc_preexpiration_notification',
											'label' => __('Pre-expiration notification', 'W2DC'),
											'default' => get_option('w2dc_preexpiration_notification'),
										),
									 	array(
											'type' => 'textarea',
											'name' => 'w2dc_expiration_notification',
											'label' => __('Expiration notification', 'W2DC'),
											'default' => get_option('w2dc_expiration_notification'),
										),
									),
								),
							),
						),
						'advanced' => array(
							'name' => 'advanced',
							'title' => __('Advanced settings', 'W2DC'),
							'icon' => 'font-awesome:w2dc-fa-gear',
							'controls' => array(
								'google_api' => array(
									'type' => 'section',
									'title' => __('Google API', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'textbox',
											'name' => 'w2dc_google_api_key',
											'label' => __('Google browser API key', 'W2DC'),
											'description' => sprintf(__('get your Google API key <a href="%s" target="_blank">here</a>, following APIs must be enabled in the console: Google Maps JavaScript API, Google Static Maps API and Google Maps Directions API.', 'W2DC'), 'https://code.google.com/apis/console'),
											'default' => get_option('w2dc_google_api_key'),
										),
										array(
											'type' => 'textbox',
											'name' => 'w2dc_google_api_key_server',
											'label' => __('Google server API key', 'W2DC'),
											'description' => sprintf(__('get your Google API key <a href="%s" target="_blank">here</a>, following APIs must be enabled in the console: Google Maps Geocoding API for radius search functionality.', 'W2DC'), 'https://code.google.com/apis/console') . ' ' . sprintf(__('Check geolocation <a href="%s">response</a>.', 'W2DC'), admin_url('admin.php?page=w2dc_debug')),
											'default' => get_option('w2dc_google_api_key_server'),
										),
									),
								),
								'js_css' => array(
									'type' => 'section',
									'title' => __('JavaScript & CSS', 'W2DC'),
									'description' => __('Do not touch these settings if you do not know what they mean. It may cause lots of problems.', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'toggle',
											'name' => 'w2dc_force_include_js_css',
											'label' => __('Include directory JS and CSS files on all pages', 'W2DC'),
											'default' => get_option('w2dc_force_include_js_css'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_images_lightbox',
											'label' => __('Include lightbox slideshow library', 'W2DC'),
											'description' =>  __('Some themes and 3rd party plugins include own Lighbox library - this may cause conflicts.', 'W2DC'),
											'default' => get_option('w2dc_images_lightbox'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_notinclude_jqueryui_css',
											'label' => __('Do not include jQuery UI CSS', 'W2DC'),
									 		'description' =>  __('Some themes and 3rd party plugins include own jQuery UI CSS - this may cause conflicts in styles.', 'W2DC'),
											'default' => get_option('w2dc_notinclude_jqueryui_css'),
										),
									),
								),
								'miscellaneous' => array(
									'type' => 'section',
									'title' => __('Miscellaneous', 'W2DC'),
									'fields' => array(
									 	array(
											'type' => 'toggle',
											'name' => 'w2dc_address_autocomplete',
											'label' => __('Enable autocomplete on addresses fields', 'W2DC'),
											'default' => get_option('w2dc_address_autocomplete'),
										),
									 	array(
											'type' => 'toggle',
											'name' => 'w2dc_address_geocode',
											'label' => __('Enable "Get my location" button on addresses fields', 'W2DC'),
											'default' => get_option('w2dc_address_geocode'),
										),
									),
								),
								'recaptcha' => array(
									'type' => 'section',
									'title' => __('reCaptcha settings', 'W2DC'),
									'fields' => array(
									 	array(
											'type' => 'toggle',
											'name' => 'w2dc_enable_recaptcha',
											'label' => __('Enable reCaptcha', 'W2DC'),
											'default' => get_option('w2dc_enable_recaptcha'),
										),
									 	array(
											'type' => 'textbox',
											'name' => 'w2dc_recaptcha_public_key',
											'label' => __('reCaptcha public key', 'W2DC'),
											'description' => sprintf(__('get your reCAPTCHA API Keys <a href="%s" target="_blank">here</a>', 'W2DC'), 'http://www.google.com/recaptcha'),
											'default' => get_option('w2dc_recaptcha_public_key'),
										),
									 	array(
											'type' => 'textbox',
											'name' => 'w2dc_recaptcha_private_key',
											'label' => __('reCaptcha private key', 'W2DC'),
											'default' => get_option('w2dc_recaptcha_private_key'),
										),
									),
								),
							),
						),
						'customization' => array(
							'name' => 'customization',
							'title' => __('Customization', 'W2DC'),
							'icon' => 'font-awesome:w2dc-fa-check',
							'controls' => array(
								'color_schemas' => array(
									'type' => 'section',
									'title' => __('Color palettes', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'toggle',
											'name' => 'w2dc_compare_palettes',
											'label' => __('Compare palettes at the frontend', 'W2DC'),
									 		'description' =>  __('Do not forget to switch off this setting when comparison will be completed.', 'W2DC'),
											'default' => get_option('w2dc_compare_palettes'),
										),
										array(
											'type' => 'select',
											'name' => 'w2dc_color_scheme',
											'label' => __('Color palette', 'W2DC'),
											'items' => array(
												array('value' => 'default', 'label' => __('Default', 'W2DC')),
												array('value' => 'orange', 'label' => __('Orange', 'W2DC')),
												array('value' => 'red', 'label' => __('Red', 'W2DC')),
												array('value' => 'yellow', 'label' => __('Yellow', 'W2DC')),
												array('value' => 'green', 'label' => __('Green', 'W2DC')),
												array('value' => 'gray', 'label' => __('Gray', 'W2DC')),
												array('value' => 'blue', 'label' => __('Blue', 'W2DC')),
											),
											'default' => array(get_option('w2dc_color_scheme')),
										),
										array(
											'type' => 'notebox',
											'description' => esc_attr__("Don't forget to clear cache of your browser and on server (when used) after customization changes were made.", 'W2DC'),
											'status' => 'warning',
										),
									),
								),
								'links_colors' => array(
									'type' => 'section',
									'title' => __('Links & buttons', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'color',
											'name' => 'w2dc_links_color',
											'label' => __('Links color', 'W2DC'),
											'default' => get_option('w2dc_links_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_links_color'
											),
										),
										array(
											'type' => 'color',
											'name' => 'w2dc_links_hover_color',
											'label' => __('Links hover color', 'W2DC'),
											'default' => get_option('w2dc_links_hover_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_links_hover_color'
											),
										),
										array(
											'type' => 'color',
											'name' => 'w2dc_button_1_color',
											'label' => __('Button primary color', 'W2DC'),
											'default' => get_option('w2dc_button_1_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_button_1_color'
											),
										),
										array(
											'type' => 'color',
											'name' => 'w2dc_button_2_color',
											'label' => __('Button secondary color', 'W2DC'),
											'default' => get_option('w2dc_button_2_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_button_2_color'
											),
										),
										array(
											'type' => 'color',
											'name' => 'w2dc_button_text_color',
											'label' => __('Button text color', 'W2DC'),
											'default' => get_option('w2dc_button_text_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_button_text_color'
											),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_button_gradient',
											'label' => __('Use gradient on buttons', 'W2DC'),
											'description' => __('This will remove all icons from buttons'),
											'default' => get_option('w2dc_button_gradient'),
										),
									),
								),
								'search_colors' => array(
									'type' => 'section',
									'title' => __('Search block', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'color',
											'name' => 'w2dc_search_1_color',
											'label' => __('Primary background gradient color', 'W2DC'),
											'default' => get_option('w2dc_search_1_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_search_1_color'
											),
										),
										array(
											'type' => 'color',
											'name' => 'w2dc_search_2_color',
											'label' => __('Secondary background gradient color', 'W2DC'),
											'default' => get_option('w2dc_search_2_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_search_2_color'
											),
										),
										array(
											'type' => 'color',
											'name' => 'w2dc_search_text_color',
											'label' => __('Search block text color', 'W2DC'),
											'default' => get_option('w2dc_search_text_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_search_text_color'
											),
										),
									),
								),
								'categories_colors' => array(
									'type' => 'section',
									'title' => __('Categories block', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'color',
											'name' => 'w2dc_categories_1_color',
											'label' => __('Primary categories background color', 'W2DC'),
											'default' => get_option('w2dc_categories_1_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_categories_1_color'
											),
										),
										array(
											'type' => 'color',
											'name' => 'w2dc_categories_2_color',
											'label' => __('Secondary categories background color', 'W2DC'),
											'default' => get_option('w2dc_categories_2_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_categories_2_color'
											),
										),
										array(
											'type' => 'color',
											'name' => 'w2dc_categories_text_color',
											'label' => __('Categories block text color', 'W2DC'),
											'default' => get_option('w2dc_categories_text_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_categories_text_color'
											),
										),
									),
								),
								'locations_colors' => array(
									'type' => 'section',
									'title' => __('Locations block', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'color',
											'name' => 'w2dc_locations_1_color',
											'label' => __('Primary locations background color', 'W2DC'),
											'default' => get_option('w2dc_locations_1_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_locations_1_color'
											),
										),
										array(
											'type' => 'color',
											'name' => 'w2dc_locations_2_color',
											'label' => __('Secondary locations background color', 'W2DC'),
											'default' => get_option('w2dc_locations_2_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_locations_2_color'
											),
										),
										array(
											'type' => 'color',
											'name' => 'w2dc_locations_text_color',
											'label' => __('Locations block text color', 'W2DC'),
											'default' => get_option('w2dc_locations_text_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_locations_text_color'
											),
										),
									),
								),
								'misc_colors' => array(
									'type' => 'section',
									'title' => __('Misc settings', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'color',
											'name' => 'w2dc_primary_color',
											'label' => __('Primary color', 'W2DC'),
											'description' =>  __('The color of categories, tags labels, map info window caption, pagination elements', 'W2DC'),
											'default' => get_option('w2dc_primary_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_primary_color'
											),
										),
										array(
											'type' => 'color',
											'name' => 'w2dc_featured_color',
											'label' => __('Featured listing highlight color', 'W2DC'),
											'default' => get_option('w2dc_featured_color'),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_featured_color'
											),
										),
										array(
											'type' => 'select',
											'name' => 'w2dc_logo_animation_effect',
											'label' => __('Thumbnail animation hover effect on excerpt pages', 'W2DC'),
											'items' => array(
													array(
															'value' => 0,
															'label' => __('No effect', 'W2DC')
													),
													array(
															'value' => 1,
															'label' => sprintf(__('Animation effect #%d', 'W2DC'), 1)
													),
													array(
															'value' => 2,
															'label' => sprintf(__('Animation effect #%d', 'W2DC'), 2)
													),
													array(
															'value' => 3,
															'label' => sprintf(__('Animation effect #%d', 'W2DC'), 3)
													),
													array(
															'value' => 4,
															'label' => sprintf(__('Animation effect #%d', 'W2DC'), 4)
													),
													array(
															'value' => 5,
															'label' => sprintf(__('Animation effect #%d', 'W2DC'), 5)
													),
													array(
															'value' => 6,
															'label' => sprintf(__('Animation effect #%d', 'W2DC'), 6)
													),
											),
											'default' => array(get_option('w2dc_logo_animation_effect')),
										),
										array(
											'type' => 'slider',
											'name' => 'w2dc_listings_bottom_margin',
											'label' => __('Bottom margin between listings (in pixels)', 'W2DC'),
											'min' => '0',
											'max' => '120',
											'default' => '30',
										),
										array(
											'type' => 'slider',
											'name' => 'w2dc_listing_title_font',
											'label' => __('Listing title font size (in pixels)', 'W2DC'),
											'min' => '7',
											'max' => '40',
											'default' => '20',
										),
										array(
											'type' => 'radioimage',
											'name' => 'w2dc_jquery_ui_schemas',
											'label' => __('jQuery UI Style', 'W2DC'),
									 		'description' =>  __('Controls the color of calendar, dialogs and slider UI widgets', 'W2DC'),
									 		'items' => array(
									 			array(
									 				'value' => 'blitzer',
									 				'label' => 'Blitzer',
									 				'img' => 'http://jqueryui.com/resources/images/themeGallery/theme_90_blitzer.png'
									 			),
									 			array(
									 				'value' => 'smoothness',
									 				'label' => 'Smoothness',
									 				'img' => 'http://jqueryui.com/resources/images/themeGallery/theme_90_smoothness.png'
									 			),
									 			array(
									 				'value' => 'redmond',
									 				'label' => 'Redmond',
									 				'img' => 'http://jqueryui.com/resources/images/themeGallery/theme_90_windoze.png'
									 			),
									 			array(
									 				'value' => 'ui-darkness',
									 				'label' => 'UI Darkness',
									 				'img' => 'http://jqueryui.com/resources/images/themeGallery/theme_90_ui_dark.png'
									 			),
									 			array(
									 				'value' => 'ui-lightness',
									 				'label' => 'UI Lightness',
									 				'img' => 'http://jqueryui.com/resources/images/themeGallery/theme_90_ui_light.png'
									 			),
									 			array(
									 				'value' => 'trontastic',
									 				'label' => 'Trontastic',
									 				'img' => 'http://jqueryui.com/resources/images/themeGallery/theme_90_trontastic.png'
									 			),
									 			array(
									 				'value' => 'start',
									 				'label' => 'Start',
									 				'img' => 'http://jqueryui.com/resources/images/themeGallery/theme_90_start_menu.png'
									 			),
									 			array(
									 				'value' => 'sunny',
									 				'label' => 'Sunny',
									 				'img' => 'http://jqueryui.com/resources/images/themeGallery/theme_90_sunny.png'
									 			),
									 			array(
									 				'value' => 'overcast',
									 				'label' => 'Overcast',
									 				'img' => 'http://jqueryui.com/resources/images/themeGallery/theme_90_overcast.png'
									 			),
									 			array(
									 				'value' => 'le-frog',
									 				'label' => 'Le Frog',
									 				'img' => 'http://jqueryui.com/resources/images/themeGallery/theme_90_le_frog.png'
									 			),
									 			array(
									 				'value' => 'hot-sneaks',
									 				'label' => 'Hot Sneaks',
									 				'img' => 'http://jqueryui.com/resources/images/themeGallery/theme_90_hot_sneaks.png'
									 			),
									 			array(
									 				'value' => 'excite-bike',
									 				'label' => 'Excite Bike',
									 				'img' => 'http://jqueryui.com/resources/images/themeGallery/theme_90_excite_bike.png'
									 			),
									 		),
											'default' => array(get_option('w2dc_jquery_ui_schemas')),
											'binding' => array(
												'field' => 'w2dc_color_scheme',
												'function' => 'w2dc_affect_setting_w2dc_jquery_ui_schemas'
											),
										),
									),
								),
							),
						),
						'social_sharing' => array(
							'name' => 'social_sharing',
							'title' => __('Social Sharing', 'W2DC'),
							'icon' => 'font-awesome:w2dc-fa-facebook ',
							'controls' => array(
								'social_sharing' => array(
									'type' => 'section',
									'title' => __('Listings Social Sharing Buttons', 'W2DC'),
									'fields' => array(
										array(
											'type' => 'radioimage',
											'name' => 'w2dc_share_buttons_style',
											'label' => __('Buttons style', 'W2DC'),
									 		'items' => array(
									 			array(
									 				'value' => 'arbenta',
									 				'label' =>__('Arbenta', 'W2DC'),
									 				'img' => W2DC_RESOURCES_URL . 'images/social/arbenta/facebook.png'
									 			),
									 			array(
									 				'value' => 'flat',
													'label' =>__('Flat', 'W2DC'),
									 				'img' => W2DC_RESOURCES_URL . 'images/social/flat/facebook.png'
									 			),
									 			array(
									 				'value' => 'somacro',
													'label' =>__('Somacro', 'W2DC'),
									 				'img' => W2DC_RESOURCES_URL . 'images/social/somacro/facebook.png'
									 			),
									 		),
											'default' => array(get_option('w2dc_share_buttons_style')),
										),
										array(
											'type' => 'sorter',
											'name' => 'w2dc_share_buttons',
											'label' => __('Include and order buttons', 'W2DC'),
									 		'items' => $w2dc_social_services,
											'default' => get_option('w2dc_share_buttons'),
										),
										array(
											'type' => 'toggle',
											'name' => 'w2dc_share_counter',
											'label' => __('Enable counter', 'W2DC'),
											'default' => get_option('w2dc_share_counter'),
										),
										array(
											'type' => 'radiobutton',
											'name' => 'w2dc_share_buttons_place',
											'label' => __('Buttons place', 'W2DC'),
											'items' => array(
												array(
													'value' => 'title',
													'label' =>__('After title', 'W2DC'),
												),
												array(
													'value' => 'before_content',
													'label' =>__('Before text content', 'W2DC'),
												),
												array(
													'value' => 'after_content',
													'label' =>__('After text content', 'W2DC'),
												),
											),
											'default' => array(
													get_option('w2dc_share_buttons_place')
											),
										),
										array(
											'type' => 'slider',
											'name' => 'w2dc_share_buttons_width',
											'label' => __('Social buttons width (in pixels)', 'W2DC'),
											'default' => get_option('w2dc_share_buttons_width'),
									 		'min' => 24,
									 		'max' => 64,
										),
									),
								),
							),
						),
					)
				),
				//'menu_page' => 'w2dc_settings',
				'use_auto_group_naming' => true,
				'use_util_menu' => false,
				'minimum_role' => 'edit_theme_options',
				'layout' => 'fixed',
				'page_title' => __('Directory settings', 'W2DC'),
				'menu_label' => __('Directory settings', 'W2DC'),
		);
		
		// adapted for WPML /////////////////////////////////////////////////////////////////////////
		global $sitepress;
		if (function_exists('wpml_object_id_filter') && $sitepress) {
			$theme_options['template']['menus']['advanced']['controls']['wpml'] = array(
				'type' => 'section',
				'title' => __('WPML Settings', 'W2DC'),
				'fields' => array(
					array(
						'type' => 'toggle',
						'name' => 'w2dc_map_language_from_wpml',
						'label' => __('Force WPML language on maps', 'W2GM'),
						'description' => __("Ignore the browser's language setting and force it to display information in a particular WPML language", 'W2GM'),
						'default' => get_option('w2dc_map_language_from_wpml'),
					),
				),
			);
		}
		
		$theme_options = apply_filters('w2dc_build_settings', $theme_options);

		$VP_W2DC_Option = new VP_W2DC_Option($theme_options);
	}

	public function save_option($opts, $old_opts, $status) {
		global $w2dc_wpml_dependent_options, $sitepress;

		if ($status) {
			foreach ($opts AS $option=>$value) {
				// adapted for WPML
				if (in_array($option, $w2dc_wpml_dependent_options)) {
					if (function_exists('wpml_object_id_filter') && $sitepress) {
						if ($sitepress->get_default_language() != ICL_LANGUAGE_CODE) {
							update_option($option.'_'.ICL_LANGUAGE_CODE, $value);
							continue;
						}
					}
				}
				update_option($option, $value);
			}
			
			w2dc_save_dynamic_css();
		}
	}
}

function w2dc_save_dynamic_css() {
	$upload_dir = wp_upload_dir();
	$filename = trailingslashit($upload_dir['basedir']) . 'w2dc-plugin.css';
		
	ob_start();
	include W2DC_PATH . '/classes/customization/dynamic_css.php';
	$dynamic_css = ob_get_contents();
	ob_get_clean();
		
	global $wp_filesystem;
	if (empty($wp_filesystem)) {
		require_once(ABSPATH .'/wp-admin/includes/file.php');
		WP_Filesystem();
	}
		
	if ($wp_filesystem) {
		$wp_filesystem->put_contents(
				$filename,
				$dynamic_css,
				FS_CHMOD_FILE // predefined mode settings for WP files
		);
	}
}

// adapted for WPML
function w2dc_get_wpml_dependent_option_name($option) {
	global $w2dc_wpml_dependent_options, $sitepress;

	if (in_array($option, $w2dc_wpml_dependent_options))
		if (function_exists('wpml_object_id_filter') && $sitepress)
			if ($sitepress->get_default_language() != ICL_LANGUAGE_CODE)
				if (get_option($option.'_'.ICL_LANGUAGE_CODE) !== false)
					return $option.'_'.ICL_LANGUAGE_CODE;

	return $option;
}
function w2dc_get_wpml_dependent_option($option) {
	return get_option(w2dc_get_wpml_dependent_option_name($option));
}
function w2dc_get_wpml_dependent_option_description() {
	global $sitepress;
	return ((function_exists('wpml_object_id_filter') && $sitepress) ? sprintf(__('%s This is multilingual option, each language may have own value.', 'W2DC'), '<br /><img src="'.W2DC_RESOURCES_URL . 'images/multilang.png" /><br />') : '');
}

?>