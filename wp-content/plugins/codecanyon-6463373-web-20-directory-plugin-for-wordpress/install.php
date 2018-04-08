<?php 

function w2dc_install_directory() {
	global $wpdb;
	
	if (!get_option('w2dc_installed_directory')) {
		$wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->w2dc_content_fields_groups} (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`name` varchar(255) NOT NULL,
					`on_tab` tinyint(1) NOT NULL DEFAULT '0',
					`hide_anonymous` tinyint(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_content_fields_groups} WHERE name = 'Contact Information'")) {
			$wpdb->query("INSERT INTO {$wpdb->w2dc_content_fields_groups} (`name`, `on_tab`, `hide_anonymous`) VALUES ('Contact Information', 0, 0)");
			do_action('Web 2.0 Directory', 'The name of content fields group #1', 'Contact Information');
		}

		$wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->w2dc_content_fields} (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`is_core_field` tinyint(1) NOT NULL DEFAULT '0',
					`order_num` tinyint(1) NOT NULL,
					`name` varchar(255) NOT NULL,
					`slug` varchar(255) NOT NULL,
					`description` text NOT NULL,
					`type` varchar(255) NOT NULL,
					`icon_image` varchar(255) NOT NULL,
					`is_required` tinyint(1) NOT NULL DEFAULT '0',
					`is_configuration_page` tinyint(1) NOT NULL DEFAULT '0',
					`is_search_configuration_page` tinyint(1) NOT NULL DEFAULT '0',
					`is_ordered` tinyint(1) NOT NULL DEFAULT '0',
					`is_hide_name` tinyint(1) NOT NULL DEFAULT '0',
					`on_exerpt_page` tinyint(1) NOT NULL DEFAULT '0',
					`on_listing_page` tinyint(1) NOT NULL DEFAULT '0',
					`on_search_form` tinyint(1) NOT NULL DEFAULT '0',
					`on_map` tinyint(1) NOT NULL DEFAULT '0',
					`advanced_search_form` tinyint(1) NOT NULL,
					`categories` text NOT NULL,
					`options` text NOT NULL,
					`search_options` text NOT NULL,
					`group_id` int(11) NOT NULL DEFAULT '0',
					PRIMARY KEY (`id`),
					KEY `group_id` (`group_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_content_fields} WHERE slug = 'summary'")) {
			$wpdb->query("INSERT INTO {$wpdb->w2dc_content_fields} (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_ordered`, `is_hide_name`, `on_exerpt_page`, `on_listing_page`, `on_search_form`, `on_map`, `advanced_search_form`, `categories`, `options`, `search_options`, `group_id`) VALUES(1, 1, 'Summary', 'summary', '', 'excerpt', '', 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '', '', '0');");
			do_action('Web 2.0 Directory', 'The name of content field #1', 'Summary');
		}
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_content_fields} WHERE slug = 'address'")) {
			$wpdb->query("INSERT INTO {$wpdb->w2dc_content_fields} (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_ordered`, `is_hide_name`, `on_exerpt_page`, `on_listing_page`, `on_search_form`, `on_map`, `advanced_search_form`, `categories`, `options`, `search_options`, `group_id`) VALUES(1, 2, 'Address', 'address', '', 'address', 'w2dc-fa-map-marker', 0, 0, 0, 0, 0, 1, 1, 0, 1, 0, '', '', '', '0');");
			do_action('Web 2.0 Directory', 'The name of content field #2', 'Address');
		}
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_content_fields} WHERE slug = 'content'")) {
			$wpdb->query("INSERT INTO {$wpdb->w2dc_content_fields} (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_ordered`, `is_hide_name`, `on_exerpt_page`, `on_listing_page`, `on_search_form`, `on_map`, `advanced_search_form`, `categories`, `options`, `search_options`, `group_id`) VALUES(1, 3, 'Description', 'content', '', 'content', '', 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, '', '', '', '0');");
			do_action('Web 2.0 Directory', 'The name of content field #3', 'Description');
		}
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_content_fields} WHERE slug = 'categories_list'")) {
			$wpdb->query("INSERT INTO {$wpdb->w2dc_content_fields} (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_ordered`, `is_hide_name`, `on_exerpt_page`, `on_listing_page`, `on_search_form`, `on_map`, `advanced_search_form`, `categories`, `options`, `search_options`, `group_id`) VALUES(1, 4, 'Categories', 'categories_list', '', 'categories', '', 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, '', '', '', '0');");
			do_action('Web 2.0 Directory', 'The name of content field #4', 'Categories');
		}
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_content_fields} WHERE slug = 'listing_tags'")) {
			$wpdb->query("INSERT INTO {$wpdb->w2dc_content_fields} (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_ordered`, `is_hide_name`, `on_exerpt_page`, `on_listing_page`, `on_search_form`, `on_map`, `advanced_search_form`, `categories`, `options`, `search_options`, `group_id`) VALUES(1, 5, 'Listing Tags', 'listing_tags', '', 'tags', '', 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, '', '', '', '0');");
			do_action('Web 2.0 Directory', 'The name of content field #5', 'Listing Tags');
		}
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_content_fields} WHERE slug = 'phone'")) {
			$wpdb->query("INSERT INTO {$wpdb->w2dc_content_fields} (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_ordered`, `is_hide_name`, `on_exerpt_page`, `on_listing_page`, `on_search_form`, `on_map`, `advanced_search_form`, `categories`, `options`, `search_options`, `group_id`) VALUES(0, 6, 'Phone', 'phone', '', 'string', 'w2dc-fa-phone', 0, 1, 0, 0, 0, 1, 1, 0, 1, 0, '', 'a:3:{s:10:\"max_length\";s:2:\"25\";s:5:\"regex\";s:0:\"\";s:8:\"is_phone\";i:1;}', '', '1');");
			do_action('Web 2.0 Directory', 'The name of content field #6', 'Phone');
		}
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_content_fields} WHERE slug = 'website'")) {
			$wpdb->query("INSERT INTO {$wpdb->w2dc_content_fields} (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_ordered`, `is_hide_name`, `on_exerpt_page`, `on_listing_page`, `on_search_form`, `on_map`, `advanced_search_form`, `categories`, `options`, `search_options`, `group_id`) VALUES(0, 7, 'Website', 'website', '', 'website', 'w2dc-fa-globe', 0, 1, 0, 0, 0, 0, 1, 0, 1, 0, '', 'a:5:{s:8:\"is_blank\";i:1;s:11:\"is_nofollow\";i:1;s:13:\"use_link_text\";i:1;s:17:\"default_link_text\";s:13:\"view our site\";s:21:\"use_default_link_text\";i:0;}', '', '1');");
			do_action('Web 2.0 Directory', 'The name of content field #7', 'Website');
		}
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_content_fields} WHERE slug = 'email'")) {
			$wpdb->query("INSERT INTO {$wpdb->w2dc_content_fields} (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_ordered`, `is_hide_name`, `on_exerpt_page`, `on_listing_page`, `on_search_form`, `on_map`, `advanced_search_form`, `categories`, `options`, `search_options`, `group_id`) VALUES(0, 8, 'Email', 'email', '', 'email', 'w2dc-fa-envelope-o', 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, '', '', '', '1');");
			do_action('Web 2.0 Directory', 'The name of content field #8', 'Email');
		}

		$wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->w2dc_directories} (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`name` varchar(255) NOT NULL,
					`single` varchar(255) NOT NULL,
					`plural` varchar(255) NOT NULL,
					`listing_slug` varchar(255) NOT NULL,
					`category_slug` varchar(255) NOT NULL,
					`location_slug` varchar(255) NOT NULL,
					`tag_slug` varchar(255) NOT NULL,
					`categories` text NOT NULL,
					`locations` text NOT NULL,
					`levels` text NOT NULL,
					PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
		
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_directories} WHERE name = 'Listings'"))
			$wpdb->query("INSERT INTO {$wpdb->w2dc_directories} (`name`, `single`, `plural`, `listing_slug`, `category_slug`, `location_slug`, `tag_slug`, `categories`, `locations`, `levels`) VALUES ('Listings', 'listing', 'listings', 'business-listing', 'business-category', 'business-place', 'business-tag', '', '', '')");

		$wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->w2dc_levels} (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`order_num` tinyint(1) NOT NULL,
					`name` varchar(255) NOT NULL,
					`description` text NOT NULL,
					`active_years` tinyint(1) NOT NULL,
					`active_months` tinyint(1) NOT NULL,
					`active_days` tinyint(1) NOT NULL,
					`raiseup_enabled` tinyint(1) NOT NULL,
					`sticky` tinyint(1) NOT NULL,
					`featured` tinyint(1) NOT NULL,
					`nofollow` tinyint(1) NOT NULL DEFAULT '0',
					`listings_own_page` tinyint(1) NOT NULL DEFAULT '1',
					`categories_number` tinyint(1) NOT NULL,
					`unlimited_categories` tinyint(1) NOT NULL,
					`locations_number` tinyint(1) NOT NULL,
					`google_map` tinyint(1) NOT NULL,
					`google_map_markers` tinyint(1) NOT NULL,
					`logo_enabled` tinyint(1) NOT NULL,
					`images_number` tinyint(1) NOT NULL,
					`videos_number` tinyint(1) NOT NULL,
					`categories` text NOT NULL,
					`locations` text NOT NULL,
					`content_fields` text NOT NULL,
					`upgrade_meta` text NOT NULL,
					PRIMARY KEY (`id`),
					KEY `order_num` (`order_num`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
		
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_levels} WHERE name = 'Standard'"))
			$wpdb->query("INSERT INTO {$wpdb->w2dc_levels} (`order_num`, `name`, `description`, `active_years`, `active_months`, `active_days`, `raiseup_enabled`, `sticky`, `featured`, `nofollow`, `listings_own_page`, `categories_number`, `unlimited_categories`, `locations_number`, `google_map`, `google_map_markers`, `logo_enabled`, `images_number`, `videos_number`, `categories`, `content_fields`, `upgrade_meta`) VALUES (1, 'Standard', '', 0, 0, 0, 1, 0, 0, 0, 1, 0, 1, 3, 1, 1, 1, 6, 3, '', '', '', '')");

		$wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->w2dc_levels_relationships} (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`post_id` int(11) NOT NULL,
					`level_id` int(11) NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `post_id` (`post_id`,`level_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

		$wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->w2dc_locations_levels} (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`name` varchar(255) NOT NULL,
					`in_widget` tinyint(1) NOT NULL,
					`in_address_line` tinyint(1) NOT NULL,
					PRIMARY KEY (`id`),
					KEY `in_select_widget` (`in_widget`,`in_address_line`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_locations_levels} WHERE name = 'Country'"))
			$wpdb->query("INSERT INTO {$wpdb->w2dc_locations_levels} (`name`, `in_widget`, `in_address_line`) VALUES ('Country', 1, 1);");
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_locations_levels} WHERE name = 'State'"))
			$wpdb->query("INSERT INTO {$wpdb->w2dc_locations_levels} (`name`, `in_widget`, `in_address_line`) VALUES ('State', 1, 1);");
		if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_locations_levels} WHERE name = 'City'"))
			$wpdb->query("INSERT INTO {$wpdb->w2dc_locations_levels} (`name`, `in_widget`, `in_address_line`) VALUES ('City', 1, 1);");

		$wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->w2dc_locations_relationships} (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`post_id` int(11) NOT NULL,
					`location_id` int(11) NOT NULL,
					`address_line_1` varchar(255) NOT NULL,
					`address_line_2` varchar(255) NOT NULL,
					`zip_or_postal_index` varchar(25) NOT NULL,
					`additional_info` text NOT NULL,
					`manual_coords` tinyint(1) NOT NULL,
					`map_coords_1` float(10,6) NOT NULL,
					`map_coords_2` float(10,6) NOT NULL,
					`map_icon_file` varchar(255) NOT NULL,
					PRIMARY KEY (`id`),
					KEY `location_id` (`location_id`),
					KEY `post_id` (`post_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	
		if (!is_array(get_terms(W2DC_LOCATIONS_TAX)) || !count(get_terms(W2DC_LOCATIONS_TAX))) {
			if (($parent_term = wp_insert_term('USA', W2DC_LOCATIONS_TAX)) && !is_a($parent_term, 'WP_Error')) {
				wp_insert_term('Alabama', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Alaska', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Arkansas', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Arizona', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('California', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Colorado', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Connecticut', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Delaware', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('District of Columbia', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Florida', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Georgia', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Hawaii', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Idaho', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Illinois', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Indiana', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Iowa', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Kansas', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Kentucky', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Louisiana', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Maine', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Maryland', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Massachusetts', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Michigan', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Minnesota', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Mississippi', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Missouri', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Montana', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Nebraska', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Nevada', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('New Hampshire', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('New Jersey', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('New Mexico', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('New York', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('North Carolina', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('North Dakota', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Ohio', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Oklahoma', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Oregon', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Pennsylvania', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Rhode Island', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('South Carolina', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('South Dakota', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Tennessee', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Texas', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Utah', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Vermont', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Virginia', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Washington state', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('West Virginina', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Wisconsin', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
				wp_insert_term('Wyoming', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			}
		}

		add_option('w2dc_enable_recaptcha');
		add_option('w2dc_recaptcha_public_key');
		add_option('w2dc_recaptcha_private_key');
		add_option('w2dc_show_categories_index', 1);
		add_option('w2dc_show_category_count', 1);
		add_option('w2dc_listings_number_index', 6);
		add_option('w2dc_listings_number_excerpt', 6);
		add_option('w2dc_map_on_index', 1);
		add_option('w2dc_map_on_excerpt', 1);
		add_option('w2dc_directory_title', 'Web 2.0 Directory');
		add_option('w2dc_categories_nesting_level', 1);
		add_option('w2dc_show_directions', 1);
		add_option('w2dc_send_expiration_notification_days', 1);
		add_option('w2dc_preexpiration_notification', 'Your listing "[listing]" will expiry in [days] days.');
		add_option('w2dc_expiration_notification', 'Your listing "[listing]" had expired. You can renew it here [link]');
		add_option('w2dc_show_what_search', 1);
		add_option('w2dc_show_where_search', 1);
		add_option('w2dc_listings_on_index', 1);
		add_option('w2dc_listing_contact_form', 1);
		add_option('w2dc_favourites_list', 1);
		add_option('w2dc_print_button', 1);
		add_option('w2dc_pdf_button', 1);
		add_option('w2dc_default_map_zoom', 11);
		add_option('w2dc_categories_icons');
		add_option('w2dc_change_expiration_date', 0);
		add_option('w2dc_categories_columns', 2);
		add_option('w2dc_map_style', 'default');
		add_option('w2dc_main_search', 1);
		add_option('w2dc_hide_comments_number_on_index', 0);
		add_option('w2dc_hide_listings_creation_date', 1);
		add_option('w2dc_hide_author_link', 1);
		add_option('w2dc_enable_radius_search_circle', 0);
		add_option('w2dc_enable_clusters', 0);
		add_option('w2dc_show_location_count_in_search', 1);
		add_option('w2dc_color_scheme', 'default');
		add_option('w2dc_force_include_js_css', 0);
		add_option('w2dc_images_lightbox', 1);
		add_option('w2dc_listing_contact_form_7', '');
		add_option('w2dc_show_keywords_search', 1);
		add_option('w2dc_show_locations_search', 1);
		add_option('w2dc_show_address_search', 1);
		add_option('w2dc_subcategories_items', 0);
		add_option('w2dc_default_geocoding_location', '');
		add_option('w2dc_show_orderby_links', 1);
		add_option('w2dc_orderby_date', 1);
		add_option('w2dc_orderby_title', 1);
		add_option('w2dc_default_orderby', 'post_date');
		add_option('w2dc_default_order', 'DESC');
		add_option('w2dc_notinclude_jqueryui_css', 0);
		add_option('w2dc_logo_animation_effect', 6);
		add_option('w2dc_views_switcher', 1);
		add_option('w2dc_views_switcher_default', 'list');
		add_option('w2dc_views_switcher_grid_columns', 2);
		add_option('w2dc_wrap_logo_list_view', 0);
		add_option('w2dc_show_category_count_in_search', 1);
		add_option('w2dc_miles_kilometers_in_search', 'miles');
		add_option('w2dc_radius_search_min', 0);
		add_option('w2dc_radius_search_max', 10);
		add_option('w2dc_show_categories_search', 1);
		add_option('w2dc_show_radius_search', 1);
		add_option('w2dc_radius_search_default', 0);
		add_option('w2dc_orderby_distance', 1);
		add_option('w2dc_compare_palettes', 0);
		add_option('w2dc_links_color', '#2393ba');
		add_option('w2dc_links_hover_color', '#2a6496');
		add_option('w2dc_button_1_color', '#2393ba');
		add_option('w2dc_button_2_color', '#1f82a5');
		add_option('w2dc_button_text_color', '#FFFFFF');
		add_option('w2dc_search_1_color', '#bafefe');
		add_option('w2dc_search_2_color', '#47c6c6');
		add_option('w2dc_search_text_color', '#FFFFFF');
		add_option('w2dc_categories_1_color', '#CEE6F3');
		add_option('w2dc_categories_2_color', '#DEEEF7');
		add_option('w2dc_categories_text_color', '#2393ba');
		add_option('w2dc_locations_1_color', '#CEE6F3');
		add_option('w2dc_locations_2_color', '#DEEEF7');
		add_option('w2dc_locations_text_color', '#2393ba');
		add_option('w2dc_primary_color', '#2393ba');
		add_option('w2dc_featured_color', '#e1ffff');
		add_option('w2dc_listing_thumb_width', 300);
		add_option('w2dc_grid_view_logo_ratio', 56.25);
		add_option('w2dc_listings_bottom_margin', 40);
		add_option('w2dc_listing_title_font', 25);
		add_option('w2dc_default_map_height', 450);
		add_option('w2dc_jquery_ui_schemas', 'redmond');
		add_option('w2dc_addresses_order', array("line_1", "comma1", "line_2", "comma2", "location", "space1", "zip"));
		add_option('w2dc_orderby_exclude_null', 0);
		add_option('w2dc_map_marker_width', 48);
		add_option('w2dc_map_marker_height', 48);
		add_option('w2dc_map_marker_anchor_x', 24);
		add_option('w2dc_map_marker_anchor_y', 48);
		add_option('w2dc_map_infowindow_width', 350);
		add_option('w2dc_map_infowindow_offset', 50);
		add_option('w2dc_map_infowindow_logo_width', 110);
		add_option('w2dc_enable_nologo', 1);
		add_option('w2dc_nologo_url', W2DC_URL . 'resources/images/nologo.png');
		add_option('w2dc_excerpt_length', 25);
		add_option('w2dc_cropped_content_as_excerpt', 1);
		add_option('w2dc_strip_excerpt', 1);
		add_option('w2dc_orderby_sticky_featured', 0);
		add_option('w2dc_button_gradient', 0);
		add_option('w2dc_enable_description', 1);
		add_option('w2dc_enable_excerpt', 1);
		add_option('w2dc_share_buttons_style', 'arbenta');
		add_option('w2dc_share_buttons', array());
		add_option('w2dc_share_counter', 0);
		add_option('w2dc_share_buttons_place', 'title');
		add_option('w2dc_share_buttons_width', 40);
		add_option('w2dc_100_single_logo_width', 1);
		add_option('w2dc_single_logo_width', 270);
		add_option('w2dc_enable_address_line_1', 1);
		add_option('w2dc_enable_address_line_2', 1);
		add_option('w2dc_enable_postal_index', 1);
		add_option('w2dc_enable_additional_info', 1);
		add_option('w2dc_enable_manual_coords', 1);
		add_option('w2dc_big_slide_bg_mode', 'cover');
		add_option('w2dc_exclude_logo_from_listing', 0);
		add_option('w2dc_enable_lighbox_gallery', 1);
		add_option('w2dc_directions_functionality', 'builtin');
		add_option('w2dc_address_autocomplete', 1);
		add_option('w2dc_address_geocode', 1);
		add_option('w2dc_listings_comments_mode', 'wp_settings');
		add_option('w2dc_listings_tabs_order', array("addresses-tab", "comments-tab", "videos-tab", "contact-tab"));
		add_option('w2dc_permalinks_structure', 'postname');
		add_option('w2dc_google_api_key', '');
		add_option('w2dc_google_api_key_server', '');
		add_option('w2dc_locations_1_color', '#CEE6F3');
		add_option('w2dc_locations_2_color', '#DEEEF7');
		add_option('w2dc_locations_text_color', '#2393ba');
		add_option('w2dc_show_locations_index', 0);
		add_option('w2dc_locations_nesting_level', 1);
		add_option('w2dc_locations_columns', 2);
		add_option('w2dc_sublocations_items', 0);
		add_option('w2dc_show_location_count', 1);
		add_option('w2dc_locations_icons');
		add_option('w2dc_enable_breadcrumbs', 1);
		add_option('w2dc_hide_home_link_breadcrumb', 0);
		add_option('w2dc_breadcrumbs_mode', 'title');
		add_option('w2dc_auto_slides_gallery', 0);
		add_option('w2dc_auto_slides_gallery_delay', 3000);
		add_option('w2dc_ajax_load', 1);
		add_option('w2dc_ajax_initial_load', 0);
		add_option('w2dc_show_more_button', 1);
		add_option('w2dc_map_markers_type', 'icons');
		add_option('w2dc_default_marker_color', '#2393ba');
		add_option('w2dc_default_marker_icon', '');
		add_option('w2dc_search_on_map', 0);
		add_option('w2dc_enable_stats', 1);
		add_option('w2dc_enable_draw_panel', 0);
		add_option('w2dc_map_markers_is_limit', 1);
		add_option('w2dc_address_autocomplete_code', "0");
		add_option('w2dc_show_listings_count', 1);
		add_option('w2dc_custom_contact_email', 0);
		add_option('w2dc_admin_notifications_email', get_option('admin_email'));
	
		add_option('w2dc_installed_directory', true);
		add_option('w2dc_installed_directory_version', W2DC_VERSION);
	} elseif (get_option('w2dc_installed_directory_version') != W2DC_VERSION) {
		$upgrades_list = array(
				'1.0.6',
				'1.0.7',
				'1.1.0',
				'1.1.2',
				'1.1.4',
				'1.1.5',
				'1.1.7',
				'1.2.0',
				'1.3.0',
				'1.3.2',
				'1.4.0',
				'1.4.2',
				'1.5.0',
				'1.5.4',
				'1.5.5',
				'1.5.7',
				'1.5.8',
				'1.6.0',
				'1.6.2',
				'1.7.0',
				'1.8.0',
				'1.8.1',
				'1.8.2',
				'1.8.3',
				'1.8.4',
				'1.8.6',
				'1.9.0',
				'1.9.1',
				'1.9.5',
				'1.9.6',
				'1.9.7',
				'1.9.9',
				'1.10.0',
				'1.11.0',
				'1.11.3',
				'1.11.4',
				'1.11.5',
				'1.11.6',
				'1.11.7',
				'1.12.0',
				'1.12.4',
				'1.12.5',
				'1.12.7',
				'1.12.8',
				'1.14.0',
				'1.14.2',
				'1.14.3',
		);

		$old_version = get_option('w2dc_installed_directory_version');
		foreach ($upgrades_list AS $upgrade_version) {
			if (!$old_version || version_compare($old_version, $upgrade_version, '<')) {
				$upgrade_function_name = 'w2dc_upgrade_to_' . str_replace('.', '_', $upgrade_version);
				if (function_exists($upgrade_function_name))
					$upgrade_function_name();
				do_action('w2dc_version_upgrade', $upgrade_version);
			}
		}

		w2dc_save_dynamic_css();

		update_option('w2dc_installed_directory_version', W2DC_VERSION);
		
		echo '<script>location.reload();</script>';
	}
	
	global $w2dc_instance;
	$w2dc_instance->loadClasses();
}

function w2dc_upgrade_to_1_0_6() {
	add_option('w2dc_show_what_search', 1);
	add_option('w2dc_show_where_search', 1);
}

function w2dc_upgrade_to_1_0_7() {
	add_option('w2dc-content_width', 60);
}

function w2dc_upgrade_to_1_1_0() {
	delete_option('w2dc_is_home_page');
	delete_option('w2dc-content_width');
}

function w2dc_upgrade_to_1_1_2() {
	add_option('w2dc_listings_on_index', 1);
	add_option('w2dc_listing_contact_form', 1);
}

function w2dc_upgrade_to_1_1_4() {
	add_option('w2dc_favourites_list', 1);
	add_option('w2dc_print_button', 1);
	add_option('w2dc_pdf_button', 1);
}

function w2dc_upgrade_to_1_1_5() {
	add_option('w2dc_default_map_zoom', 11);
}

function w2dc_upgrade_to_1_1_7() {
	add_option('w2dc_categories_icons');
}

function w2dc_upgrade_to_1_2_0() {
	add_option('w2dc_change_expiration_date', 0);
}

function w2dc_upgrade_to_1_3_0() {
	add_option('w2dc_categories_columns', 2);
	add_option('w2dc_map_style', 'default');
}

function w2dc_upgrade_to_1_3_2() {
	add_option('w2dc_main_search', 1);
	add_option('w2dc_hide_comments_number_on_index', 0);
	add_option('w2dc_hide_listings_creation_date', 1);
}

function w2dc_upgrade_to_1_4_0() {
	global $wpdb;

	$wpdb->query("ALTER TABLE {$wpdb->w2dc_content_fields} ADD `on_map` TINYINT( 1 ) NOT NULL AFTER `on_search_form`");

	add_option('w2dc_enable_radius_search_circle', 0);
	add_option('w2dc_enable_clusters', 0);
	add_option('w2dc_show_location_count_in_search', 1);
}

function w2dc_upgrade_to_1_4_2() {
	add_option('w2dc_color_scheme', 'default');
}

function w2dc_upgrade_to_1_5_0() {
	add_option('w2dc_images_lightbox', 1);
}

function w2dc_upgrade_to_1_5_4() {
	add_option('w2dc_listing_contact_form_7', '');
}

function w2dc_upgrade_to_1_5_5() {
	if (($widgets_array = get_option('widget_w2dc_search_widget')) && is_array($widgets_array)) {
		foreach ($widgets_array AS &$widget) {
			if (isset($widget['title'])) {
				$widget['visibility'] = 1;
				$widget['search_visibility'] = 1;
			}
		}
		update_option('widget_w2dc_search_widget', $widgets_array);
	}

	if (($widgets_array = get_option('widget_w2dc_categories_widget')) && is_array($widgets_array)) {
		foreach ($widgets_array AS &$widget) {
			if (isset($widget['title']))
				$widget['visibility'] = 1;
		}
		update_option('widget_w2dc_categories_widget', $widgets_array);
	}

	if (($widgets_array = get_option('widget_w2dc_listings_widget')) && is_array($widgets_array)) {
		foreach ($widgets_array AS &$widget) {
			if (isset($widget['title']))
				$widget['visibility'] = 1;
		}
		update_option('widget_w2dc_listings_widget', $widgets_array);
	}
	
	if (($widgets_array = get_option('widget_w2dc_social_widget')) && is_array($widgets_array)) {
		foreach ($widgets_array AS &$widget) {
			if (isset($widget['title']))
				$widget['visibility'] = 1;
		}
		update_option('widget_w2dc_social_widget', $widgets_array);
	}
}

function w2dc_upgrade_to_1_5_7() {
	add_option('w2dc_show_keywords_search', 1);
	add_option('w2dc_show_locations_search', 1);
	add_option('w2dc_show_address_search', 1);

	add_option('w2dc_subcategories_items', 0);
	
	if (($widgets_array = get_option('widget_w2dc_categories_widget')) && is_array($widgets_array)) {
		foreach ($widgets_array AS &$widget) {
			if (isset($widget['title']))
				$widget['subcats'] = 0;
		}
		update_option('widget_w2dc_categories_widget', $widgets_array);
	}
}

function w2dc_upgrade_to_1_5_8() {
	add_option('w2dc_default_geocoding_location', '');
	add_option('w2dc_show_orderby_links', 1);
	add_option('w2dc_orderby_date', 1);
	add_option('w2dc_orderby_title', 1);
	add_option('w2dc_default_orderby', 'post_date');
	add_option('w2dc_default_order', 'DESC');
}

function w2dc_upgrade_to_1_6_0() {
	global $wpdb;
	
	$wpdb->query("ALTER TABLE {$wpdb->w2dc_levels} ADD `upgrade_meta` TEXT NOT NULL"); 
}

function w2dc_upgrade_to_1_6_2() {
	$args = array(
		'post_type' => W2DC_POST_TYPE,
		'post_status' => 'any',
		'nopaging' => true
	);
	$query = new WP_Query($args);
	while ($query->have_posts()) {
		$query->the_post();

		$listing = new w2dc_listing;
		$listing->loadListingFromPost(get_post());

		if ($listing->logo_image)
			update_post_meta($listing->post->ID, '_thumbnail_id', $listing->logo_image);
	}
	// this is reset is really required after the loop ends
	wp_reset_postdata();

	global $w2dc_instance, $wpdb;
	foreach ($w2dc_instance->content_fields->content_fields_array AS $content_field) {
		if ($content_field->type == 'select' || $content_field->type == 'checkbox' || $content_field->type == 'radio') {
			foreach ($content_field->selection_items AS $key=>$item)
				$wpdb->update($wpdb->postmeta, array('meta_value' => $key), array('meta_key' => '_content_field_' . $content_field->id, 'meta_value' => $item));
		}
	}
	
	add_option('w2dc_enable_automatic_translations', 0);
}

function w2dc_upgrade_to_1_7_0() {
	if (get_option('w2dc_miles_kilometers_in_search', null) === null) {
		add_option('w2dc_show_category_count_in_search', 1);
		add_option('w2dc_miles_kilometers_in_search', 'miles');
		add_option('w2dc_radius_search_min', 0);
		add_option('w2dc_radius_search_max', 10);
		add_option('w2dc_show_categories_search', 1);
		add_option('w2dc_show_radius_search', 1);
		add_option('w2dc_radius_search_default', 0);
		add_option('w2dc_orderby_distance', 1);
	}
}

function w2dc_upgrade_to_1_8_0() {
	global $wpdb;

	add_option('w2dc_compare_palettes', 0);
	add_option('w2dc_links_color', '#2393ba');
	add_option('w2dc_links_hover_color', '#2a6496');
	add_option('w2dc_button_1_color', '#2393ba');
	add_option('w2dc_button_2_color', '#1f82a5');
	add_option('w2dc_button_text_color', '#FFFFFF');
	add_option('w2dc_search_1_color', '#bafefe');
	add_option('w2dc_search_2_color', '#47c6c6');
	add_option('w2dc_search_text_color', '#FFFFFF');
	add_option('w2dc_categories_1_color', '#CEE6F3');
	add_option('w2dc_categories_2_color', '#DEEEF7');
	add_option('w2dc_categories_text_color', '#2393ba');
	add_option('w2dc_primary_color', '#2393ba');
	add_option('w2dc_featured_color', '#e1ffff');
	add_option('w2dc_listing_thumb_width', 300);
	add_option('w2dc_listings_bottom_margin', 40);
	add_option('w2dc_listing_title_font', 25);
	add_option('w2dc_default_map_height', 450);
	add_option('w2dc_jquery_ui_schemas', 'redmond');
	add_option('w2dc_addresses_order', array("line_1", "comma1", "line_2", "comma2", "location", "space1", "zip"));
	add_option('w2dc_orderby_exclude_null', 0);
	delete_option('w2dc_images_on_tab');
	delete_option('w2dc_listings_own_page');

	add_option('w2dc_enable_nologo', 1);
	add_option('w2dc_nologo_url', W2DC_RESOURCES_URL . 'images/nologo.png');

	add_option('w2dc_map_marker_width', 48);
	add_option('w2dc_map_marker_height', 48);
	add_option('w2dc_map_marker_anchor_x', 24);
	add_option('w2dc_map_marker_anchor_y', 48);
	add_option('w2dc_map_infowindow_width', 350);
	add_option('w2dc_map_infowindow_offset', 50);
	add_option('w2dc_map_infowindow_logo_width', 110);
	
	add_option('w2dc_excerpt_length', 25);
	add_option('w2dc_cropped_content_as_excerpt', 1);
	add_option('w2dc_strip_excerpt', 1);

	$wpdb->query("ALTER TABLE {$wpdb->w2dc_locations_relationships} ADD `additional_info` TEXT NOT NULL AFTER `zip_or_postal_index`");
	$wpdb->query("ALTER TABLE {$wpdb->w2dc_levels} ADD `content_fields` TEXT NOT NULL AFTER `categories`");
	$wpdb->query("ALTER TABLE {$wpdb->w2dc_levels} ADD `nofollow` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `featured`");
	$wpdb->query("ALTER TABLE {$wpdb->w2dc_levels} ADD `listings_own_page` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `nofollow`");
	$wpdb->query("ALTER TABLE {$wpdb->w2dc_levels} DROP `logo_size`");

	$levels_row = $wpdb->get_row("SELECT * FROM {$wpdb->w2dc_levels}");
	if (!isset($levels_row->upgrade_meta))
		$wpdb->query("ALTER TABLE {$wpdb->w2dc_levels} ADD `upgrade_meta` TEXT NOT NULL");

	$wpdb->query("UPDATE {$wpdb->w2dc_content_fields} SET `is_search_configuration_page`=1 WHERE `type` IN ('select','checkbox','radio')");
	
	w2dc_renewMapIcons();
}
function w2dc_renewMapIcons() {
	$old_folders = scandir(W2DC_MAP_ICONS_PATH . 'icons');
	$old_files = array();
	foreach ($old_folders AS $old_folder) {
		if ($old_folder != '.' && $old_folder != '..' && $old_folder != '_new') {
			$old_folders_files = scandir(W2DC_MAP_ICONS_PATH . 'icons/'.$old_folder);
			foreach ($old_folders_files AS $file) {
				if ($file != '.' && $file != '..') {
					$old_files[$old_folder][] = $file;
				}
			}
				
		}
	}

	$new_files = scandir(W2DC_MAP_ICONS_PATH . 'icons/_new');
	unset($new_files[array_search('.', $new_files)]);
	unset($new_files[array_search('..', $new_files)]);

	$result = array();
	foreach ($old_files AS $old_folder_name=>$old_folder) {
		foreach ($old_folder AS $old_file) {
			$found = false;
			foreach ($new_files AS $new_file) {
				if (strtolower($old_file) == strtolower($new_file)) {
					$result[] = array('old' => $old_folder_name.'/'.$old_file, 'new' => '_new/'.$new_file);
					$found = true;
					continue 2;
				}
			}
			if (!$found)
				$result[] = array('old' => $old_folder_name.'/'.$old_file, 'new' => '_new/'.$new_files[array_rand($new_files)]);
		}
	}

	global $wpdb;
	foreach ($result AS $rewrite)
		$wpdb->update($wpdb->w2dc_locations_relationships, array('map_icon_file' => $rewrite['new']), array('map_icon_file' => $rewrite['old']));

	add_option('w2dc_map_markers_update_backup', $result);
}

function w2dc_upgrade_to_1_8_1() {
	add_option('w2dc_orderby_sticky_featured', 0);
}

function w2dc_upgrade_to_1_8_2() {
	add_option('w2dc_button_gradient', 0);
}

function w2dc_upgrade_to_1_8_3() {
	global $wpdb;

	$wpdb->query("ALTER TABLE {$wpdb->w2dc_content_fields} ADD `group_id` INT NOT NULL DEFAULT '0', ADD INDEX ( `group_id` )");
	$wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->w2dc_content_fields_groups} (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`name` varchar(255) NOT NULL,
					`on_tab` tinyint(1) NOT NULL DEFAULT '0',
					`hide_anonymous` tinyint(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	
	add_option('w2dc_enable_description', 1);
	add_option('w2dc_enable_excerpt', 1);

	add_option('w2dc_share_buttons_style', 'arbenta');
	add_option('w2dc_share_buttons', array());
	add_option('w2dc_share_counter', 0);
	add_option('w2dc_share_buttons_place', 'title');
	add_option('w2dc_share_buttons_width', 40);

	add_option('w2dc_100_single_logo_width', 1);
	add_option('w2dc_single_logo_width', 270);
}

function w2dc_upgrade_to_1_8_4() {
	add_option('w2dc_notinclude_jqueryui_css', 0);
	add_option('w2dc_logo_animation_effect', 6);
	add_option('w2dc_views_switcher', 1);
	add_option('w2dc_views_switcher_default', 'list');
	add_option('w2dc_views_switcher_grid_columns', 2);
	add_option('w2dc_wrap_logo_list_view', 0);
}

function w2dc_upgrade_to_1_8_6() {
	add_option('w2dc_enable_address_line_1', 1);
	add_option('w2dc_enable_address_line_2', 1);
	add_option('w2dc_enable_postal_index', 1);
	add_option('w2dc_enable_additional_info', 1);
	add_option('w2dc_enable_manual_coords', 1);

	add_option('w2dc_big_slide_bg_mode', 'cover');
}

function w2dc_upgrade_to_1_9_0() {
	global $wpdb;

	add_option('w2dc_directions_functionality', 'builtin');
	add_option('w2dc_address_autocomplete', 1);
	add_option('w2dc_address_geocode', 1);
	add_option('w2dc_listings_comments_mode', 'wp_settings');
	add_option('w2dc_listings_tabs_order', array("addresses-tab", "comments-tab", "videos-tab", "contact-tab"));

	add_option('w2dc_listing_slug', 'business-listing');
	add_option('w2dc_location_slug', 'business-place');
	add_option('w2dc_permalinks_structure', 'postname');

	add_option('w2dc_google_api_key', '');
	
	add_option('w2dc_locations_1_color', '#CEE6F3');
	add_option('w2dc_locations_2_color', '#DEEEF7');
	add_option('w2dc_locations_text_color', '#2393ba');

	add_option('w2dc_show_locations_index', 0);
	add_option('w2dc_locations_nesting_level', 1);
	add_option('w2dc_locations_columns', 2);
	add_option('w2dc_sublocations_items', 0);
	add_option('w2dc_show_location_count', 1);
	
	add_option('w2dc_locations_icons');
	
	if (($widgets_array = get_option('widget_w2dc_categories_widget')) && is_array($widgets_array)) {
		foreach ($widgets_array AS &$widget) {
			if (isset($widget['title']))
				$widget['parent'] = 0;
		}
		update_option('widget_w2dc_categories_widget', $widgets_array);
	}
	
	$wpdb->query("UPDATE {$wpdb->w2dc_content_fields} SET `is_configuration_page`=1 WHERE `type`='hours'");

	add_option('w2dc_enable_breadcrumbs', 1);
	add_option('w2dc_hide_home_link_breadcrumb', 0);
	add_option('w2dc_breadcrumbs_mode', 'title');
}

function w2dc_upgrade_to_1_9_1() {
	add_option('w2dc_grid_view_logo_ratio', 56.25);
}

function w2dc_upgrade_to_1_9_5() {
	global $wpdb;

	add_option('w2dc_google_api_key', get_option('w2dc_google_maps_api_key'));

	add_option('w2dc_enable_lighbox_gallery', 1);
	
	$wpdb->query("UPDATE {$wpdb->w2dc_content_fields} SET `is_configuration_page`=1 WHERE `type`='string' OR `type`='textarea'");
}

function w2dc_upgrade_to_1_9_6() {
	add_option('w2dc_exclude_logo_from_listing', 0);
}

function w2dc_upgrade_to_1_9_9() {
	add_option('w2dc_auto_slides_gallery', 0);
	add_option('w2dc_auto_slides_gallery_delay', 3000);
}

function w2dc_upgrade_to_1_10_0() {
	add_option('w2dc_ajax_load', 1);
	add_option('w2dc_ajax_initial_load', 0);
	add_option('w2dc_show_more_button', 1);
}

function w2dc_upgrade_to_1_11_0() {
	add_option('w2dc_map_markers_type', 'images');
	add_option('w2dc_default_marker_color', '#2393ba');
	add_option('w2dc_default_marker_icon', '');
	add_option('w2dc_search_on_map', 0);
}

function w2dc_upgrade_to_1_11_3() {
	add_option('w2dc_enable_stats', 1);

	// these settings were missed in standard installation of previous version
	add_option('w2dc_map_marker_width', 48);
	add_option('w2dc_map_marker_height', 48);
	add_option('w2dc_map_marker_anchor_x', 24);
	add_option('w2dc_map_marker_anchor_y', 48);
}

function w2dc_upgrade_to_1_11_4() {
	add_option('w2dc_google_api_key_server', '');
}

function w2dc_upgrade_to_1_11_5() {
	global $wpdb;

	$wpdb->query("UPDATE {$wpdb->w2dc_content_fields} SET `slug`='categories_list' WHERE `slug`='categories'");
}

function w2dc_upgrade_to_1_11_6() {
	if (($widgets_array = get_option('widget_w2dc_search_widget')) && is_array($widgets_array)) {
		foreach ($widgets_array AS &$widget) {
			if (isset($widget['title'])) {
				$widget['uid'] = '';
			}
		}
		update_option('widget_w2dc_search_widget', $widgets_array);
	}
	
	add_option('w2dc_hide_author_link', 1);
	
	add_option('w2dc_enable_full_screen', 1);
	add_option('w2dc_enable_wheel_zoom', 1);
	add_option('w2dc_enable_dragging_touchscreens', 1);
	add_option('w2dc_center_map_onclick', 0);
	add_option('w2dc_hide_search_on_map_mobile', 0);
}

function w2dc_upgrade_to_1_11_7() {
	add_option('w2dc_map_language_from_wpml', 0);
}

function w2dc_upgrade_to_1_12_0() {
	add_option('w2dc_enable_draw_panel', 0);
}

function w2dc_upgrade_to_1_12_4() {
	if (($widgets_array = get_option('widget_w2dc_categories_widget')) && is_array($widgets_array)) {
		foreach ($widgets_array AS &$widget) {
			if (isset($widget['title'])) {
				$widget['related_subcats'] = 0;
			}
		}
		update_option('widget_w2dc_categories_widget', $widgets_array);
	}

	if (($widgets_array = get_option('widget_w2dc_locations_widget')) && is_array($widgets_array)) {
		foreach ($widgets_array AS &$widget) {
			if (isset($widget['title'])) {
				$widget['related_sublocations'] = 0;
			}
		}
		update_option('widget_w2dc_locations_widget', $widgets_array);
	}
	
	add_option('w2dc_google_maps_required', 0);
}

function w2dc_upgrade_to_1_12_5() {
	add_option('w2dc_map_markers_is_limit', 1);
}

function w2dc_upgrade_to_1_12_7() {
	add_option('w2dc_address_autocomplete_code', "0");
	add_option('w2dc_show_listings_count', 1);
	add_option('w2dc_custom_contact_email', 0);
	
	global $wpdb;
	$results = $wpdb->get_results("SELECT * FROM {$wpdb->w2dc_locations_relationships}", ARRAY_A);
	foreach ($results AS $row) {
		foreach ($row AS $key=>$value) {
			if ($key != 'post_id' && $key != 'id') {
				$wpdb->insert($wpdb->postmeta, array(
						'post_id' => $row['post_id'],
						'meta_key' => '_'.$key,
						'meta_value' => $value
				));
			}
		}
	}
}

function w2dc_upgrade_to_1_12_8() {
	wp_clear_scheduled_hook('sheduled_events');

	if (!get_option('w2dc_admin_notifications_email'))
		add_option('w2dc_admin_notifications_email', get_option('admin_email'));
}

function w2dc_upgrade_to_1_14_0() {
	global $wpdb;

	add_option('w2dc_force_include_js_css', 0);
	
	$wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->w2dc_directories} (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(255) NOT NULL,
				`single` varchar(255) NOT NULL,
				`plural` varchar(255) NOT NULL,
				`listing_slug` varchar(255) NOT NULL,
				`category_slug` varchar(255) NOT NULL,
				`location_slug` varchar(255) NOT NULL,
				`tag_slug` varchar(255) NOT NULL,
				`categories` text NOT NULL,
				`locations` text NOT NULL,
				`levels` text NOT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
		
	if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_directories} WHERE name = 'Listings'")) {
		$listing_slug = get_option('w2dc_listing_slug');
		$category_slug = get_option('w2dc_category_slug');
		$location_slug = get_option('w2dc_location_slug');
		$tag_slug = get_option('w2dc_tag_slug');

		$wpdb->query("INSERT INTO {$wpdb->w2dc_directories} (`name`, `single`, `plural`, `listing_slug`, `category_slug`, `location_slug`, `tag_slug`, `categories`, `locations`, `levels`) VALUES ('Listings', 'listing', 'listings', $listing_slug, $category_slug, $location_slug, $tag_slug, '', '', '')");
	}
}

function w2dc_upgrade_to_1_14_2() {
	global $wpdb;

	if (!$wpdb->get_var("SELECT id FROM {$wpdb->w2dc_directories} WHERE name = 'Listings'"))
		$wpdb->query("INSERT INTO {$wpdb->w2dc_directories} (`name`, `single`, `plural`, `listing_slug`, `category_slug`, `location_slug`, `tag_slug`, `categories`, `locations`, `levels`) VALUES ('Listings', 'listing', 'listings', 'business-listing', 'business-category', 'business-place', 'business-tag', '', '', '')");
}

function w2dc_upgrade_to_1_14_3() {
	global $wpdb;

	if (($widgets_array = get_option('widget_w2dc_search_widget')) && is_array($widgets_array)) {
		foreach ($widgets_array AS &$widget) {
			if (isset($widget['title'])) {
				$widget['directory'] = 0;
			}
		}
		update_option('widget_w2dc_search_widget', $widgets_array);
	}

	if (($widgets_array = get_option('widget_w2dc_categories_widget')) && is_array($widgets_array)) {
		foreach ($widgets_array AS &$widget) {
			if (isset($widget['title'])) {
				$widget['directory'] = 0;
			}
		}
		update_option('widget_w2dc_categories_widget', $widgets_array);
	}

	if (($widgets_array = get_option('widget_w2dc_locations_widget')) && is_array($widgets_array)) {
		foreach ($widgets_array AS &$widget) {
			if (isset($widget['title'])) {
				$widget['directory'] = 0;
			}
		}
		update_option('widget_w2dc_locations_widget', $widgets_array);
	}
	
	$wpdb->query("ALTER TABLE {$wpdb->w2dc_levels} ADD `locations` TEXT NOT NULL AFTER `categories`");
}

?>