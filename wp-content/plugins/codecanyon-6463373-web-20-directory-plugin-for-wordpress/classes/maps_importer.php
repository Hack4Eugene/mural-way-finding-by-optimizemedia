<?php

class w2dc_maps_importer {
	public $special_fields = array(
			'_expiration_date',
			'_clicks_data',
			'_total_clicks',
			'_attached_image',
			'_thumbnail_id',
	);
	
	public function __construct() {
		add_action('admin_menu', array($this, 'menu'));
	}
	
	public function menu() {
		if (defined('W2GM_VERSION'))
			add_submenu_page('w2dc_settings',
				__('Import From Web 2.0 Google Maps', 'W2GM'),
				__('Import From Web 2.0 Google Maps', 'W2GM'),
				'administrator',
				'w2dc_maps_import',
				array($this, 'w2dc_maps_import')
			);
	}

	public function w2dc_maps_import() {
		if (isset($_POST['submit']))
			$this->import();
		else
			w2dc_renderTemplate('maps_importer.tpl.php');
	}
	
	public function import_tax($old_tax, $new_tax, $parent, &$new_terms_ids) {
		$terms = get_terms($old_tax, array('parent' => $parent, 'hide_empty' => false));
		foreach ($terms AS $term) {
			if ($_term = get_term_by('name', $term->name, $new_tax)) {
				$new_terms_ids[$term->term_id] = $_term->term_id;
			} else {
				$new_parent = (isset($new_terms_ids[$term->parent]) ? $new_terms_ids[$term->parent] : 0);
				$result = wp_insert_term($term->name, $new_tax, array('parent' => $new_parent, 'slug' => $term->slug, 'description' => $term->description));
				if (!is_wp_error($result)) {
					$new_terms_ids[$term->term_id] = $result['term_id'];
				}
			}
			$this->import_tax($old_tax, $new_tax, $term->term_id, $new_terms_ids);
		}
	}

	public function import() {
		global $wpdb, $w2dc_instance, $w2gm_instance;
		
		ob_implicit_flush(true);

		w2gm_renderTemplate('admin_header.tpl.php');
		
		echo "<h2>";
		_e('Import From Web 2.0 Google Maps plugin', 'W2DC');
		echo "</h2>";
		
		echo __('Import started', 'W2DC');
		echo "<br />";

		$new_categories_ids = array();
		if (isset($_POST['import_categories']) && $_POST['import_categories']) {
			echo __('Importing categories...', 'W2DC');
			echo "<br />";

			$this->import_tax(W2GM_CATEGORIES_TAX, W2DC_CATEGORIES_TAX, 0, $new_categories_ids);
			
			if ($markers_images = get_option('w2gm_categories_marker_images')) {
				$new_markers_images = array();
				foreach ($markers_images AS $category_id=>$icon) {
					if (isset($new_categories_ids[$category_id]))
						$new_markers_images[$new_categories_ids[$category_id]] = str_replace('w2gm-', 'w2dc-', $icon);
				}
				update_option('w2dc_categories_marker_images', $new_markers_icons);
			}

			if ($markers_icons = get_option('w2gm_categories_marker_icons')) {
				$new_markers_icons = array();
				foreach ($markers_icons AS $category_id=>$icon) {
					if (isset($new_categories_ids[$category_id]))
						$new_markers_icons[$new_categories_ids[$category_id]] = str_replace('w2gm-', 'w2dc-', $icon);
				}
				update_option('w2dc_categories_marker_icons', $new_markers_icons);
			}

			if ($markers_colors = get_option('w2gm_categories_marker_colors')) {
				$new_markers_colors = array();
				foreach ($markers_colors AS $category_id=>$color) {
					if (isset($new_categories_ids[$category_id]))
						$new_markers_colors[$new_categories_ids[$category_id]] = str_replace('w2gm-', 'w2dc-', $color);
				}
				update_option('w2dc_categories_marker_colors', $new_markers_colors);
			}
		}

		$new_locations_ids = array();
		$new_locations_levels_ids = array();
		if (isset($_POST['import_locations']) && $_POST['import_locations']) {
			echo __('Importing locations...', 'W2DC');
			echo "<br />";

			$this->import_tax(W2GM_LOCATIONS_TAX, W2DC_LOCATIONS_TAX, 0, $new_locations_ids);
		}

		$new_tags_ids = array();
		if (isset($_POST['import_tags']) && $_POST['import_tags']) {
			echo __('Importing tags...', 'W2DC');
			echo "<br />";

			$this->import_tax(W2GM_TAGS_TAX, W2DC_TAGS_TAX, 0, $new_tags_ids);
		}
		
		$new_content_fields_groups_ids = array();
		$new_content_fields_ids = array();
		if (isset($_POST['import_fields']) && $_POST['import_fields']) {
			foreach ($w2gm_instance->content_fields->content_fields_groups_array AS $fields_group) {
				$wpdb->query("
						INSERT INTO {$wpdb->w2dc_content_fields_groups} (`name`, `on_tab`, `hide_anonymous`)
						VALUES (
						'".esc_sql($fields_group->name)."',
						".esc_sql($fields_group->on_tab).",
						".esc_sql($fields_group->hide_anonymous)."
				);");
				$new_content_fields_groups_ids[$fields_group->id] = $wpdb->insert_id;

				// adapted for WPML
				global $sitepress;
				if (function_exists('wpml_object_id_filter') && $sitepress) {
					do_action('wpml_register_single_string', 'Web 2.0 Directory', 'The name of content fields group #' . $wpdb->insert_id, $fields_group->name);
				}
			}
			foreach ($w2gm_instance->content_fields->content_fields_array AS $content_field) {
				if (!$content_field->is_core_field) {
					$wpdb->query("
							INSERT INTO {$wpdb->w2dc_content_fields} (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_hide_name`, `on_listing_page`, `on_search_form`, `on_map`, `advanced_search_form`, `categories`, `options`, `search_options`, `group_id`)
							VALUES(
							0,
							".esc_sql($content_field->order_num).",
							'".esc_sql($content_field->name)."',
							'".esc_sql($content_field->slug)."',
							'".esc_sql($content_field->description)."',
							'".esc_sql($content_field->type)."',
							'".esc_sql(str_replace('w2gm-', 'w2dc-', $content_field->icon_image))."',
							".esc_sql($content_field->is_required).",
							".esc_sql($content_field->isConfigurationPage()).",
							".esc_sql($content_field->isSearchConfigurationPage()).",
							".esc_sql($content_field->is_hide_name).",
							".esc_sql($content_field->on_listing_page).",
							".esc_sql($content_field->on_search_form).",
							".esc_sql($content_field->on_map).",
							".esc_sql($content_field->advanced_search_form).",
							'".esc_sql($content_field->categories ? serialize($content_field->categories) : '')."',
							'".esc_sql($content_field->options ? serialize($content_field->options) : '')."',
							'".esc_sql($content_field->search_options ? serialize($content_field->search_options) : '')."',
							".esc_sql($content_field->group_id ? $new_content_fields_groups_ids[$content_field->group_id] : 0)."
					);");
					$new_content_fields_ids[$content_field->id] = $wpdb->insert_id;

					// adapted for WPML
					global $sitepress;
					if (function_exists('wpml_object_id_filter') && $sitepress) {
						do_action('wpml_register_single_string', 'Web 2.0 Directory', 'The name of content field #' . $wpdb->insert_id, $content_field->name);
						do_action('wpml_register_single_string', 'Web 2.0 Directory', 'The description of content field #' . $wpdb->insert_id, $content_field->description);
					}
				}
			}
			
			$w2gm_instance->content_fields->getContentFieldsFromDB();
			
			// adapted for WPML
			global $sitepress;
			if (function_exists('wpml_object_id_filter') && $sitepress) {
				if (function_exists('icl_st_is_registered_string')) {
					$languages = $sitepress->get_active_languages();
					foreach ($languages AS $lang_code=>$lang) {
						foreach ($w2dc_instance->content_fields->content_fields_array AS $content_field) {
							if (array_search($content_field->id, $new_content_fields_ids) !== false) {
								$old_content_field_id = array_search($content_field->id, $new_content_fields_ids);
								$old_string_id = icl_st_is_registered_string('Web 2.0 Google Maps', 'The name of content field #' . $old_content_field_id);
								$value = icl_get_string_by_id($old_string_id, $lang_code);
								$status = icl_get_relative_translation_status($old_string_id, null);
								if ($value && ($new_string_id = icl_st_is_registered_string('Web 2.0 Directory', 'The name of content field #' . $content_field->id)))
									icl_add_string_translation($new_string_id, $lang_code, $value, $status);
								
								$old_string_id = icl_st_is_registered_string('Web 2.0 Google Maps', 'The description of content field #' . $old_content_field_id);
								$value = icl_get_string_by_id($old_string_id, $lang_code);
								$status = icl_get_relative_translation_status($old_string_id, null);
								if ($value && ($new_string_id = icl_st_is_registered_string('Web 2.0 Directory', 'The description of content field #' . $content_field->id)))
									icl_add_string_translation($new_string_id, $lang_code, $value, $status);
							}
						}
						foreach ($w2dc_instance->content_fields->content_fields_groups_array AS $content_fields_group) {
							if (array_search($content_fields_group->id, $new_content_fields_groups_ids) !== false) {
								$old_content_field_group_id = array_search($content_fields_group->id, $new_content_fields_groups_ids);
								$old_string_id = icl_st_is_registered_string('Web 2.0 Google Maps', 'The name of content fields group #' . $old_content_field_group_id);
								$value = icl_get_string_by_id($old_string_id, $lang_code);
								$status = icl_get_relative_translation_status($old_string_id, null);
								if ($value && ($new_string_id = icl_st_is_registered_string('Web 2.0 Directory', 'The name of content fields group #' . $content_fields_group->id)))
									icl_add_string_translation($new_string_id, $lang_code, $value, $status);
							}
						}
					}
				}
			}
		}

		$level = null;
		if (isset($_POST['import_level']) && $_POST['import_level']) {
			$level = $w2dc_instance->levels->levels_array[$_POST['import_level']];
		}

		$new_listings_ids = array();
		if (isset($_POST['import_listings']) && $_POST['import_listings']) {
			echo __('Importing listings...', 'W2DC');
			echo "<br />";

			$args = array(
					'post_type' => W2GM_POST_TYPE,
					'post_status' => array('publish', 'draft', 'pending'),
					'posts_per_page' => -1,
			);
			$posts = get_posts($args);
			foreach ($posts AS $post) {
				printf(__('Importing of listing %s', 'W2DC'), $post->post_title);
				echo "<br />";

				$post = get_object_vars($post);
				$original_id = $post['ID'];
				unset($post['ID']);
				unset($post['guid']);
				$post['post_type'] = W2DC_POST_TYPE;
				$new_post_id = wp_insert_post($post);
				$new_listings_ids[$original_id] = $new_post_id;
				add_post_meta($new_post_id, '_order_date', time());
				if (get_post_meta($original_id, '_expiration_date', true))
					add_post_meta($new_post_id, '_expiration_date', get_post_meta($original_id, '_expiration_date', true));
				elseif ($level && !$level->eternal_active_period) {
					$expiration_date = w2dc_sumDates(time(), $level->active_days, $level->active_months, $level->active_years);
					add_post_meta($new_post_id, '_expiration_date', $expiration_date);
				}

				if ($level)
					$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->w2dc_levels_relationships} (post_id, level_id) VALUES(%d, %d)", $new_post_id, $level->id));
				
				// adapted for WPML
				global $sitepress;
				if (function_exists('wpml_object_id_filter') && $sitepress) {
					$old_trid = $sitepress->get_element_trid($original_id, 'post_'.W2GM_POST_TYPE);
					$lang_details = $sitepress->get_element_language_details($original_id, 'post_'.W2GM_POST_TYPE);
					if ($original_id == ($original_trid_element = $sitepress->get_original_element_id_by_trid($old_trid))) {
						// This is original translation
						$new_trid = $sitepress->get_element_trid($new_post_id, 'post_'.W2DC_POST_TYPE);
						$sitepress->set_element_language_details($new_post_id, 'post_'.W2DC_POST_TYPE, $new_trid, $lang_details->language_code, $lang_details->source_language_code);
						// Assign existing child translations
						if ($translations = $sitepress->get_element_translations($old_trid)) {
							foreach ($translations AS $lang_code=>$translation) {
								if (!$translation->original && isset($new_listings_ids[$translation->element_id])) {
									$lang_details = $sitepress->get_element_language_details($translation->element_id, 'post_'.W2GM_POST_TYPE);
									$sitepress->set_element_language_details($new_listings_ids[$translation->element_id], 'post_'.W2DC_POST_TYPE, $new_trid, $lang_details->language_code, $lang_details->source_language_code);
								}
							}
						}
					} else {
						// This is not original translation
						if (isset($new_listings_ids[$original_trid_element])) {
							// Original translation already had been copied
							$new_trid = $sitepress->get_element_trid($new_listings_ids[$original_trid_element], 'post_'.W2DC_POST_TYPE);
							$sitepress->set_element_language_details($new_post_id, 'post_'.W2DC_POST_TYPE, $new_trid, $lang_details->language_code, $lang_details->source_language_code);
						} else {
							// Original translation hadn't been copied yet - nothing to do
						}
					}
				}

				if (isset($_POST['import_categories']) && $_POST['import_categories']) {
					$old_post_categories_ids = wp_get_post_terms($original_id, W2GM_CATEGORIES_TAX, array('fields' => 'ids'));
					$new_post_categories_ids = array();
					foreach ($old_post_categories_ids AS $category_id) {
						$new_post_categories_ids[] = (isset($new_categories_ids[$category_id]) ? $new_categories_ids[$category_id] : 0);
					}
					wp_set_object_terms($new_post_id, $new_post_categories_ids, W2DC_CATEGORIES_TAX);
				}
				
				if (isset($_POST['import_locations']) && $_POST['import_locations']) {
					$old_post_locations_ids = wp_get_post_terms($original_id, W2GM_LOCATIONS_TAX, array('fields' => 'ids'));
					$new_post_locations_ids = array();
					foreach ($old_post_locations_ids AS $location_id) {
						$new_post_locations_ids[] = (isset($new_locations_ids[$location_id]) ? $new_locations_ids[$location_id] : 0);
					}
					wp_set_object_terms($new_post_id, $new_post_locations_ids, W2DC_LOCATIONS_TAX);
				}
				
				if (isset($_POST['import_tags']) && $_POST['import_tags']) {
					$old_post_tags_ids = wp_get_post_terms($original_id, W2GM_TAGS_TAX, array('fields' => 'ids'));
					$new_post_tags_ids = array();
					foreach ($old_post_tags_ids AS $tags) {
						$new_post_tags_ids[] = (isset($new_tags_ids[$tags]) ? $new_tags_ids[$tags] : 0);
					}
					wp_set_object_terms($new_post_id, $new_post_tags_ids, W2DC_TAGS_TAX);
				}

				// Copy Locations
				$listing = new w2gm_listing();
				if ($listing->loadListingFromPost($original_id)) {
					foreach ($listing->locations AS $location) {
						$insert_values = array(
								'post_id' => $new_post_id,
								'location_id' => (isset($new_locations_ids[$location->selected_location]) ? $new_locations_ids[$location->selected_location] : 0),
								'address_line_1' => $location->address_line_1,
								'address_line_2' => $location->address_line_2,
								'zip_or_postal_index' => $location->zip_or_postal_index,
								'additional_info' => $location->additional_info,
						);
						$insert_values['manual_coords'] = $location->manual_coords;
						$insert_values['map_coords_1'] = $location->map_coords_1;
						$insert_values['map_coords_2'] = $location->map_coords_2;
						$insert_values['map_icon_file'] = str_replace('w2gm-', 'w2dc-', $location->map_icon_file);
						$keys = array_keys($insert_values);
						array_walk($keys, create_function('&$val', '$val = "`".$val."`";'));
						array_walk($insert_values, create_function('&$val', '$val = "\'".$val."\'";'));
				
						$wpdb->query("INSERT INTO {$wpdb->w2dc_locations_relationships} (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $insert_values) . ")");
					}
				}

				// Copy Comments with their meta
				$new_comments_ids = array();
				$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_id = $original_id");
				foreach ($comments AS $com) {
					$wpdb->query("INSERT INTO $wpdb->comments (comment_content) VALUES ('')");
					$new_comment_id = $wpdb->insert_id;
					$new_comments_ids[$com->comment_ID] = $new_comment_id;
					$query = "UPDATE $wpdb->comments SET ";
					foreach ($com as $key=>$val) {
						if ($key == 'comment_post_ID' )
							$query .= $key.' = "'.$new_post_id.'", ';
						elseif ($key != 'comment_ID')
							$query .= $key.' = "'.str_replace('"','\"',$val).'", ';
					}
					$query = substr($query,0,strlen($query)-2); # lop off the extra trailing comma
					$query .= " WHERE comment_ID=$new_comment_id";
					$wpdb->query($query);
				}
				foreach ($new_comments_ids AS $old_comment_id=>$new_comment_id) {
					$wpdb->update($wpdb->comments, array('comment_parent' => $new_comment_id), array('comment_post_id' => $new_post_id, 'comment_parent' => $old_comment_id));
				}
				foreach ($new_comments_ids AS $old_comment_id=>$new_comment_id) {
					$meta = $wpdb->get_results("SELECT * FROM $wpdb->commentmeta WHERE comment_id = ".$old_comment_id);
					foreach ($meta AS $mt) {
						$query = "INSERT INTO $wpdb->commentmeta (comment_id, meta_key, meta_value) VALUES ('".$new_comment_id."', '".$mt->meta_key."', '".str_replace("'","\'",$mt->meta_value)."')";
						$wpdb->query($query);
					}
				}

				// Copy Attachments with their meta
				$new_attachment_ids = array();
				$attachments = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_parent = $original_id AND post_type = 'attachment'");
				foreach ($attachments AS $att) {
					$wpdb->query("INSERT INTO $wpdb->posts (post_title) VALUES ('')");
					$new_attachment_id = $wpdb->insert_id;
					$new_attachment_ids[$att->ID] = $new_attachment_id;
					$query = "UPDATE $wpdb->posts SET ";
					foreach ($att as $key=>$val) {
						if ($key == 'post_name')
							$query .= $key.' = "'.str_replace('"','\"',$val).'-2", ';
						elseif ($key == 'post_parent' )
							$query .= $key.' = "'.$new_post_id.'", ';
						elseif ($key != 'ID')
							$query .= $key.' = "'.str_replace('"','\"',$val).'", ';
					}
					$query = substr($query,0,strlen($query)-2); # lop off the extra trailing comma
					$query .= " WHERE ID=$new_attachment_id";
					$wpdb->query($query);
				}
				foreach ($new_attachment_ids AS $old_id=>$new_id) {
					$meta = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id = ".$old_id);
					foreach ($meta AS $mt) {
						$query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES ('".$new_id."', '".$mt->meta_key."', '".str_replace("'","\'",$mt->meta_value)."')";
						$wpdb->query($query);
					}
				}
				
				if (isset($_POST['import_fields']) && $_POST['import_fields']) {
					$custom_fields = get_post_custom($original_id);
						
					foreach ($custom_fields as $key=>$value) {
						if (!in_array($key, $this->special_fields)) {
							if (strpos($key, '_content_field_') !== false && preg_match("/_content_field_([0-9]+)/", $key, $matches) && isset($matches[1]) && isset($new_content_fields_ids[$matches[1]])) {
								$key = str_replace($matches[1], $new_content_fields_ids[$matches[1]], $key);
							}
								
							if (is_array($value) && count($value) > 1) {
								foreach ($value as $i=>$v) {
									add_post_meta($new_post_id, str_replace('w2gm', 'w2dc', $key), maybe_unserialize($v));
								}
							} else {
								add_post_meta($new_post_id, str_replace('w2gm', 'w2dc', $key), maybe_unserialize($value[0]));
							}
						} elseif ($key == '_attached_image' || $key == '_thumbnail_id') {
							if (is_array($value) && count($value) > 1) {
								foreach ($value as $i=>$v) {
									add_post_meta($new_post_id, $key, (isset($new_attachment_ids[$v]) ? $new_attachment_ids[$v] : $v));
								}
							} else {
								add_post_meta($new_post_id, $key, (isset($new_attachment_ids[$value[0]]) ? $new_attachment_ids[$value[0]] : $value[0]));
							}
						}
					}
				}
			}
		}

		if (isset($_POST['import_settings']) && $_POST['import_settings']) {
			echo __('Importing settings...', 'W2GM');
			echo "<br />";
			
			update_option('w2dc_fsubmit_addon', get_option('w2gm_fsubmit_addon'));
			if (get_option('w2gm_fsubmit_addon')) {
				update_option('w2dc_fsubmit_default_status', get_option('w2gm_fsubmit_default_status'));
				update_option('w2dc_fsubmit_login_mode', get_option('w2gm_fsubmit_login_mode'));
				update_option('w2dc_fsubmit_edit_status', get_option('w2gm_fsubmit_edit_status'));
				update_option('w2dc_fsubmit_button', get_option('w2gm_fsubmit_button'));
				update_option('w2dc_hide_admin_bar', get_option('w2gm_hide_admin_bar'));
				update_option('w2dc_newuser_notification', get_option('w2gm_newuser_notification'));
				update_option('w2dc_newlisting_admin_notification', get_option('w2gm_newlisting_admin_notification'));
				update_option('w2dc_allow_edit_profile', get_option('w2gm_allow_edit_profile'));
				update_option('w2dc_enable_frontend_translations', get_option('w2gm_enable_frontend_translations'));
				update_option('w2dc_enable_tags', get_option('w2gm_enable_tags'));
				$this->import_wpml_dependent_option('w2dc_tospage', 'w2gm_tospage');
				$this->import_wpml_dependent_option('w2dc_submit_login_page', 'w2gm_submit_login_page');
				$this->import_wpml_dependent_option('w2dc_dashboard_login_page', 'w2gm_dashboard_login_page');
				add_option('w2dc_installed_fsubmit', 1);
			}
			update_option('w2dc_ratings_addon', get_option('w2gm_ratings_addon'));
			if (get_option('w2gm_ratings_addon')) {
				update_option('w2dc_only_registered_users', get_option('w2gm_only_registered_users'));
				update_option('w2dc_rating_on_map', get_option('w2gm_rating_on_map'));
				update_option('w2dc_manage_ratings', get_option('w2gm_manage_ratings'));
				add_option('w2dc_installed_ratings', 1);
			}
			/* if ($level) {
				update_option('w2dc_eternal_active_period', $level->eternal_active_period);
				update_option('w2dc_active_period_days', $level->active_days);
				update_option('w2dc_active_period_months', $level->active_months);
				update_option('w2dc_active_period_years', $level->active_years);
				update_option('w2dc_unlimited_categories', $level->unlimited_categories);
				update_option('w2dc_categories_number', $level->categories_number);
				update_option('w2dc_locations_number', $level->locations_number);
				update_option('w2dc_images_number', $level->images_number);
				update_option('w2dc_videos_number', $level->videos_number);
				update_option('w2dc_enable_users_markers', $level->google_map_markers);
			} */
			update_option('w2dc_change_expiration_date', get_option('w2gm_change_expiration_date'));
			update_option('w2dc_show_directions', get_option('w2gm_show_directions'));
			update_option('w2dc_listing_contact_form', get_option('w2gm_listing_contact_form'));
			$this->import_wpml_dependent_option('w2dc_listing_contact_form_7', 'w2gm_listing_contact_form_7');
			update_option('w2dc_listings_comments_mode', get_option('w2gm_listings_comments_mode'));
			update_option('w2dc_directions_functionality', get_option('w2gm_directions_functionality'));
			update_option('w2dc_listings_tabs_order', get_option('w2gm_listings_tabs_order'));
			update_option('w2dc_enable_stats', get_option('w2gm_enable_stats'));
			update_option('w2dc_enable_lighbox_gallery', get_option('w2gm_enable_lighbox_gallery'));
			update_option('w2dc_auto_slides_gallery', get_option('w2gm_auto_slides_gallery'));
			update_option('w2gm_auto_slides_gallery_delay', get_option('w2gm_auto_slides_gallery_delay'));
			update_option('w2dc_enable_nologo', get_option('w2gm_enable_nologo'));
			update_option('w2dc_nologo_url', get_option('w2gm_nologo_url'));
			update_option('w2dc_100_single_logo_width', get_option('w2gm_100_single_logo_width'));
			update_option('w2dc_single_logo_width', get_option('w2gm_single_logo_width'));
			update_option('w2dc_big_slide_bg_mode', get_option('w2gm_big_slide_bg_mode'));
			update_option('w2dc_enable_description', get_option('w2gm_enable_description'));
			update_option('w2dc_enable_summary', get_option('w2gm_enable_summary'));
			update_option('w2dc_excerpt_length', get_option('w2gm_excerpt_length'));
			update_option('w2dc_cropped_content_as_excerpt', get_option('w2gm_cropped_content_as_excerpt'));
			update_option('w2dc_strip_excerpt', get_option('w2gm_strip_excerpt'));
			update_option('w2dc_show_what_search', get_option('w2gm_show_what_search'));
			update_option('w2dc_show_where_search', get_option('w2gm_show_where_search'));
			update_option('w2dc_show_keywords_search', get_option('w2gm_show_keywords_search'));
			update_option('w2dc_show_locations_search', get_option('w2gm_show_locations_search'));
			update_option('w2dc_show_address_search', get_option('w2gm_show_address_search'));
			update_option('w2dc_show_location_count_in_search', get_option('w2gm_show_location_count_in_search'));
			update_option('w2dc_show_categories_search', get_option('w2gm_show_categories_search'));
			update_option('w2dc_show_category_count_in_search', get_option('w2gm_show_category_count_in_search'));
			update_option('w2dc_show_radius_search', get_option('w2gm_show_radius_search'));
			update_option('w2dc_miles_kilometers_in_search', get_option('w2gm_miles_kilometers_in_search'));
			update_option('w2dc_radius_search_min', get_option('w2gm_radius_search_min'));
			update_option('w2dc_radius_search_max', get_option('w2gm_radius_search_max'));
			update_option('w2dc_radius_search_default', get_option('w2gm_radius_search_default'));
			update_option('w2dc_ajax_load', get_option('w2gm_ajax_loading'));
			update_option('w2dc_search_on_map', get_option('w2gm_search_on_map'));
			update_option('w2dc_default_map_zoom', get_option('w2gm_start_zoom'));
			update_option('w2dc_map_style', get_option('w2gm_map_style'));
			update_option('w2dc_default_map_height', get_option('w2gm_default_map_height'));
			update_option('w2dc_enable_radius_search_circle', get_option('w2gm_enable_radius_search_circle'));
			update_option('w2dc_enable_clusters', get_option('w2gm_enable_clusters'));
			update_option('w2dc_default_geocoding_location', get_option('w2gm_default_geocoding_location'));
			update_option('w2dc_addresses_order', get_option('w2gm_addresses_order'));
			update_option('w2dc_enable_address_line_1', get_option('w2gm_enable_address_line_1'));
			update_option('w2dc_enable_address_line_2', get_option('w2gm_enable_address_line_2'));
			update_option('w2dc_enable_postal_index', get_option('w2gm_enable_postal_index'));
			update_option('w2dc_enable_additional_info', get_option('w2gm_enable_additional_info'));
			update_option('w2dc_enable_manual_coords', get_option('w2gm_enable_manual_coords'));
			update_option('w2dc_map_markers_type', get_option('w2gm_map_markers_type'));
			update_option('w2dc_default_marker_color', get_option('w2gm_default_marker_color'));
			update_option('w2dc_default_marker_icon', get_option('w2gm_default_marker_icon'));
			update_option('w2dc_map_marker_width', get_option('w2gm_map_marker_width'));
			update_option('w2dc_map_marker_height', get_option('w2gm_map_marker_height'));
			update_option('w2dc_map_marker_anchor_x', get_option('w2gm_map_marker_anchor_x'));
			update_option('w2dc_map_marker_anchor_y', get_option('w2gm_map_marker_anchor_y'));
			update_option('w2dc_map_infowindow_width', get_option('w2gm_map_infowindow_width'));
			update_option('w2dc_map_infowindow_offset', get_option('w2gm_map_infowindow_offset'));
			update_option('w2dc_map_infowindow_logo_width', get_option('w2gm_map_infowindow_logo_width'));
			update_option('w2dc_send_expiration_notification_days', get_option('w2gm_send_expiration_notification_days'));
			update_option('w2dc_preexpiration_notification', get_option('w2gm_preexpiration_notification'));
			update_option('w2dc_expiration_notification', get_option('w2gm_expiration_notification'));
			update_option('w2dc_google_api_key', get_option('w2gm_google_api_key'));
			update_option('w2dc_google_api_key_server', get_option('w2gm_google_api_key_server'));
			update_option('w2dc_images_lightbox', get_option('w2gm_images_lightbox'));
			update_option('w2dc_notinclude_jqueryui_css', get_option('w2gm_notinclude_jqueryui_css'));
			update_option('w2dc_address_autocomplete', get_option('w2gm_address_autocomplete'));
			update_option('w2dc_address_geocode', get_option('w2gm_address_geocode'));
			update_option('w2dc_enable_recaptcha', get_option('w2gm_enable_recaptcha'));
			update_option('w2dc_recaptcha_public_key', get_option('w2gm_recaptcha_public_key'));
			update_option('w2dc_recaptcha_private_key', get_option('w2gm_recaptcha_private_key'));
			update_option('w2dc_compare_palettes', get_option('w2gm_compare_palettes'));
			update_option('w2dc_color_scheme', get_option('w2gm_color_scheme'));
			update_option('w2dc_links_color', get_option('w2gm_links_color'));
			update_option('w2dc_links_hover_color', get_option('w2gm_links_hover_color'));
			update_option('w2dc_button_1_color', get_option('w2gm_button_1_color'));
			update_option('w2dc_button_2_color', get_option('w2gm_button_2_color'));
			update_option('w2dc_button_text_color', get_option('w2gm_button_text_color'));
			update_option('w2dc_button_gradient', get_option('w2gm_button_gradient'));
			update_option('w2dc_search_1_color', get_option('w2gm_search_1_color'));
			update_option('w2dc_search_2_color', get_option('w2gm_search_2_color'));
			update_option('w2dc_search_text_color', get_option('w2gm_search_text_color'));
			update_option('w2dc_primary_color', get_option('w2gm_primary_color'));
			update_option('w2dc_listing_title_font', get_option('w2gm_listing_title_font'));
			update_option('w2dc_jquery_ui_schemas', get_option('w2gm_jquery_ui_schemas'));

			update_option('w2dc_enable_full_screen', get_option('w2gm_enable_full_screen'));
			update_option('w2dc_enable_wheel_zoom', get_option('w2gm_enable_wheel_zoom'));
			update_option('w2dc_enable_dragging_touchscreens', get_option('w2gm_enable_dragging_touchscreens'));
			update_option('w2dc_center_map_onclick', get_option('w2gm_center_map_onclick'));
			update_option('w2dc_hide_search_on_map_mobile', get_option('w2gm_hide_search_on_map_mobile'));
			update_option('w2dc_hide_author_link', get_option('w2gm_hide_author_link'));
			update_option('w2dc_map_language_from_wpml', get_option('w2gm_map_language_from_wpml'));
			update_option('w2dc_hide_listings_creation_date', get_option('w2gm_hide_listings_creation_date'));
			update_option('w2dc_enable_draw_panel', get_option('w2gm_enable_draw_panel'));
		}
		
		w2dc_save_dynamic_css();

		echo __('Import completed', 'W2GM');
		echo "<br />";
	}
	
	// adapted for WPML
	public function import_wpml_dependent_option($w2dc_option, $w2gm_option) {
		update_option($w2dc_option, get_option($w2gm_option));
		
		global $sitepress;
		if (function_exists('wpml_object_id_filter') && $sitepress) {
			$languages = $sitepress->get_active_languages();
			foreach ($languages AS $lang_code=>$lang)
				if (get_option($w2gm_option.'_'.$lang_code) !== false)
					update_option($w2dc_option.'_'.$lang_code, get_option($w2gm_option.'_'.$lang_code));
		}
	}
}

?>