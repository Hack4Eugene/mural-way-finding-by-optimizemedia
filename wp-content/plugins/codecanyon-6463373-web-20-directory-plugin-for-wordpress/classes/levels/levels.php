<?php 

class w2dc_levels {
	public $levels_array = array();

	public function __construct() {
		$this->getLevelsFromDB();
	}
	
	public function saveOrder($order_input) {
		global $wpdb;

		if ($order_ids = explode(',', trim($order_input))) {
			$i = 1;
			foreach ($order_ids AS $id) {
				$wpdb->update($wpdb->w2dc_levels, array('order_num' => $i), array('id' => $id));
				$i++;
			}
		}
		$this->getLevelsFromDB();
		return true;
	}
	
	public function getLevelsFromDB() {
		global $wpdb;
		$this->levels_array = array();

		$array = $wpdb->get_results("SELECT * FROM {$wpdb->w2dc_levels} ORDER BY order_num", ARRAY_A);
		foreach ($array AS $row) {
			$level = new w2dc_level;
			$level->buildLevelFromArray($row);
			$this->levels_array[$row['id']] = $level;
		}
	}
	
	public function getLevelById($level_id) {
		if (isset($this->levels_array[$level_id]))
			return $this->levels_array[$level_id];
	}
	
	public function getDefaultLevel() {
		$array_keys = array_keys($this->levels_array);
		$first_id = array_shift($array_keys);
		return $this->getLevelById($first_id);
	}

	public function createLevelFromArray($array) {
		global $wpdb, $w2dc_instance;
		
		$insert_update_args = array(
				'name' => w2dc_getValue($array, 'name'),
				'description' => w2dc_getValue($array, 'description'),
				'active_years' => w2dc_getValue($array, 'active_years', 0),
				'active_months' => w2dc_getValue($array, 'active_months', 0),
				'active_days' => w2dc_getValue($array, 'active_days', 0),
				'raiseup_enabled' => w2dc_getValue($array, 'raiseup_enabled'),
				'sticky' => w2dc_getValue($array, 'sticky'),
				'listings_own_page' => w2dc_getValue($array, 'listings_own_page'),
				'nofollow' => w2dc_getValue($array, 'nofollow'),
				'featured' => w2dc_getValue($array, 'featured'),
				'categories_number' => w2dc_getValue($array, 'categories_number', 0),
				'locations_number' => w2dc_getValue($array, 'locations_number', 1),
				'unlimited_categories' => w2dc_getValue($array, 'unlimited_categories'),
				'google_map' => w2dc_getValue($array, 'google_map'),
				'logo_enabled' => w2dc_getValue($array, 'logo_enabled'),
				'images_number' => w2dc_getValue($array, 'images_number'),
				'videos_number' => w2dc_getValue($array, 'videos_number'),
				'categories' => serialize(w2dc_getValue($array, 'categories')),
				'content_fields' => serialize(w2dc_getValue($array, 'content_fields')),
				'locations_number' => w2dc_getValue($array, 'locations_number', 1),
				'google_map_markers' => w2dc_getValue($array, 'google_map_markers', 1),
		);
		$insert_update_args = apply_filters('w2dc_level_create_edit_args', $insert_update_args, $array);

		if ($wpdb->insert($wpdb->w2dc_levels, $insert_update_args)) {
			$new_level_id = $wpdb->insert_id;
			
			do_action('w2dc_update_level', $new_level_id, $array);
			
			$this->getLevelsFromDB();
			$levels = $w2dc_instance->levels;
			$results = array();
			foreach ($levels->levels_array AS $level) {
				$results[$level->id]['disabled'] = false;
				$results[$level->id]['raiseup'] = false;
			}
			$level = $this->getLevelById($new_level_id);
			$level->saveUpgradeMeta($results);
			return true;
		}
	}
	
	public function saveLevelFromArray($level_id, $array) {
		global $wpdb;

		// update listings from eternal active period to numeric 
		$old_level = $this->getLevelById($level_id);
		if ($old_level->eternal_active_period && (w2dc_getValue($array, 'active_years') || w2dc_getValue($array, 'active_months') || w2dc_getValue($array, 'active_days'))) {
			$expiration_date = w2dc_sumDates(time(), w2dc_getValue($array, 'active_days'), w2dc_getValue($array, 'active_months'), w2dc_getValue($array, 'active_years'));
			$postids = $this->getPostIdsByLevelId($level_id);
			foreach ($postids AS $post_id) {
				delete_post_meta($post_id, '_expiration_date');
				update_post_meta($post_id, '_expiration_date', $expiration_date);
			}
		} elseif (!$old_level->eternal_active_period && w2dc_getValue($array, 'active_years') == 0 && w2dc_getValue($array, 'active_months') == 0 && w2dc_getValue($array, 'active_days') == 0) {
			$postids = $this->getPostIdsByLevelId($level_id);
			foreach ($postids AS $post_id)
				delete_post_meta($post_id, '_expiration_date');
		}
		
		$insert_update_args = array(
				'name' => w2dc_getValue($array, 'name'),
				'description' => w2dc_getValue($array, 'description'),
				'active_years' => w2dc_getValue($array, 'active_years'),
				'active_months' => w2dc_getValue($array, 'active_months'),
				'active_days' => w2dc_getValue($array, 'active_days'),
				'sticky' => w2dc_getValue($array, 'sticky'),
				'listings_own_page' => w2dc_getValue($array, 'listings_own_page'),
				'nofollow' => w2dc_getValue($array, 'nofollow'),
				'raiseup_enabled' => w2dc_getValue($array, 'raiseup_enabled'),
				'featured' => w2dc_getValue($array, 'featured'),
				'categories_number' => w2dc_getValue($array, 'categories_number'),
				'locations_number' => w2dc_getValue($array, 'locations_number', 1),
				'unlimited_categories' => w2dc_getValue($array, 'unlimited_categories'),
				'google_map' => w2dc_getValue($array, 'google_map'),
				'logo_enabled' => w2dc_getValue($array, 'logo_enabled'),
				'images_number' => w2dc_getValue($array, 'images_number'),
				'videos_number' => w2dc_getValue($array, 'videos_number'),
				'categories' => serialize(w2dc_getValue($array, 'categories')),
				'locations' => serialize(w2dc_getValue($array, 'locations')),
				'content_fields' => serialize(w2dc_getValue($array, 'content_fields')),
				'locations_number' => w2dc_getValue($array, 'locations_number', 1),
				'google_map_markers' => w2dc_getValue($array, 'google_map_markers', 1),
		);
		$insert_update_args = apply_filters('w2dc_level_create_edit_args', $insert_update_args, $array);
	
		if ($wpdb->update($wpdb->w2dc_levels, $insert_update_args, array('id' => $level_id), null, array('%d')) !== false) {
			do_action('w2dc_update_level', $level_id, $array);
			
			$this->getLevelsFromDB();
			return true;
		}
	}
	
	public function deleteLevel($level_id) {
		global $wpdb;
		
		$postids = $this->getPostIdsByLevelId($level_id);
		foreach ($postids AS $post_id)
			wp_delete_post($post_id, true);
	
		$wpdb->delete($wpdb->w2dc_levels, array('id' => $level_id));

		// Renew levels' upgrade meta
		/* $this->getLevelsFromDB();
		$results = array();
		foreach ($this->levels_array AS $level1) {
			foreach ($this->levels_array AS $level2) {
				$results[$level1->id][$level2->id]['disabled'] = $level1->upgrade_meta[$level2->id]['disabled'];
				$results[$level1->id][$level2->id]['raiseup'] = $level1->upgrade_meta[$level2->id]['raiseup'];
			}
			$level1->saveUpgradeMeta($results[$level1->id]);
		} */

		$this->getLevelsFromDB();
		return true;
	}
	
	public function getPostIdsByLevelId($level_id) {
		global $wpdb;

		return $postids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->w2dc_levels_relationships} WHERE level_id=%d", $level_id));
	}
}

class w2dc_level {
	public $id;
	public $order_num;
	public $name;
	public $description;
	public $active_years = 0;
	public $active_months = 0;
	public $active_days = 0;
	public $eternal_active_period;
	public $featured = 0;
	public $listings_own_page = 1;
	public $nofollow = 0;
	public $raiseup_enabled = 0;
	public $sticky = 0;
	public $categories_number = 0;
	public $unlimited_categories = 1;
	public $locations_number = 1;
	public $google_map = 1;
	public $google_map_markers = 1;
	public $logo_enabled;
	public $images_number = 1;
	public $videos_number = 1;
	public $categories = array();
	public $locations = array();
	public $content_fields = array();
	public $upgrade_meta = array();

	public function buildLevelFromArray($array) {
		$this->id = w2dc_getValue($array, 'id');
		$this->order_num = w2dc_getValue($array, 'order_num');
		$this->name = w2dc_getValue($array, 'name');
		$this->description = w2dc_getValue($array, 'description');
		$this->active_years = w2dc_getValue($array, 'active_years');
		$this->active_months = w2dc_getValue($array, 'active_months');
		$this->active_days = w2dc_getValue($array, 'active_days');
		if ($this->active_years == 0 && $this->active_months == 0 && $this->active_days == 0)
			$this->eternal_active_period = 1;
		else 
			$this->eternal_active_period = 0;
		
		$this->featured = w2dc_getValue($array, 'featured');
		$this->sticky = w2dc_getValue($array, 'sticky');
		$this->listings_own_page = w2dc_getValue($array, 'listings_own_page');
		$this->nofollow = w2dc_getValue($array, 'nofollow');
		$this->raiseup_enabled = w2dc_getValue($array, 'raiseup_enabled');
		$this->categories_number = w2dc_getValue($array, 'categories_number');
		$this->unlimited_categories = w2dc_getValue($array, 'unlimited_categories');
		$this->locations_number = w2dc_getValue($array, 'locations_number');
		$this->google_map = w2dc_getValue($array, 'google_map');
		$this->google_map_markers = w2dc_getValue($array, 'google_map_markers');
		$this->logo_enabled = w2dc_getValue($array, 'logo_enabled');
		$this->images_number = w2dc_getValue($array, 'images_number');
		$this->videos_number = w2dc_getValue($array, 'videos_number');
		$this->categories = w2dc_getValue($array, 'categories');
		$this->locations = w2dc_getValue($array, 'locations');
		$this->content_fields = w2dc_getValue($array, 'content_fields');
		$this->upgrade_meta = (w2dc_getValue($array, 'upgrade_meta')) ? unserialize(w2dc_getValue($array, 'upgrade_meta')) : array();
		
		$this->convertCategories();
		$this->convertLocations();
		$this->convertContentFields();
		
		apply_filters('w2dc_levels_loading', $this, $array);
	}
	
	public function convertCategories() {
		if ($this->categories) {
			$unserialized_categories = maybe_unserialize($this->categories);
			if (count($unserialized_categories) > 1 || $unserialized_categories != array(''))
				$this->categories = $unserialized_categories;
			else
				$this->categories = array();
		} else
			$this->categories = array();
		return $this->categories;
	}

	public function convertLocations() {
		if ($this->locations) {
			$unserialized_locations = maybe_unserialize($this->locations);
			if (count($unserialized_locations) > 1 || $unserialized_locations != array(''))
				$this->locations = $unserialized_locations;
			else
				$this->locations = array();
		} else
			$this->locations = array();
		return $this->locations;
	}

	public function convertContentFields() {
		if ($this->content_fields) {
			$unserialized_content_fields = maybe_unserialize($this->content_fields);
			if (count($unserialized_content_fields) > 1 || $unserialized_content_fields != array(''))
				$this->content_fields = $unserialized_content_fields;
			else
				$this->content_fields = array();
		} else
			$this->content_fields = array();
		return $this->content_fields;
	}
	
	public function getActivePeriodString() {
		if ($this->eternal_active_period)
			return __('Never expire', 'W2DC');
		else {
			$string_arr = array();
			if ($this->active_days > 0)
				$string_arr[] = $this->active_days . ' ' . _n('day', 'days', $this->active_days, 'W2DC');
			if ($this->active_months > 0)
				$string_arr[] = $this->active_months . ' ' . _n('month', 'months', $this->active_months, 'W2DC');
			if ($this->active_years > 0)
				$string_arr[] = $this->active_years . ' ' . _n('year', 'years', $this->active_years, 'W2DC');
			return implode(', ', $string_arr);
		}
	}
	
	public function saveUpgradeMeta($meta) {
		global $wpdb;
		
		$this->upgrade_meta = $meta;
		
		$this->upgrade_meta = apply_filters('w2dc_level_upgrade_meta', $this->upgrade_meta, $this);

		return $wpdb->update($wpdb->w2dc_levels, array('upgrade_meta' => serialize($this->upgrade_meta)), array('id' => $this->id));
	}
	
	public function isUpgradable() {
		global $w2dc_instance;

		if (count($w2dc_instance->levels->levels_array) > 1) {
			foreach ($this->upgrade_meta AS $id=>$meta) {
				if (($id != $this->id) && (!isset($meta['disabled']) || !$meta['disabled']))
					return true;
			}
		}
		return false;
	}
}

// adapted for WPML
add_action('init', 'w2dc_levels_names_into_strings');
function w2dc_levels_names_into_strings() {
	global $w2dc_instance, $sitepress;

	if (function_exists('wpml_object_id_filter') && $sitepress) {
		foreach ($w2dc_instance->levels->levels_array AS &$level) {
			$level->name = apply_filters('wpml_translate_single_string', $level->name, 'Web 2.0 Directory', 'The name of level #' . $level->id);
			$level->description = apply_filters('wpml_translate_single_string', $level->description, 'Web 2.0 Directory', 'The description of level #' . $level->id);
		}
	}
}

add_filter('w2dc_level_create_edit_args', 'w2dc_filter_level_settings', 10, 2);
function w2dc_filter_level_settings($insert_update_args, $array) {
	global $sitepress;

	if (function_exists('wpml_object_id_filter') && $sitepress) {
		if ($sitepress->get_default_language() != ICL_LANGUAGE_CODE) {
			if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['level_id'])) {
				$level_id = $_GET['level_id'];
				if ($name_string_id = icl_st_is_registered_string('Web 2.0 Directory', 'The name of level #' . $level_id))
					icl_add_string_translation($name_string_id, ICL_LANGUAGE_CODE, $insert_update_args['name'], ICL_TM_COMPLETE);
				if ($description_string_id = icl_st_is_registered_string('Web 2.0 Directory', 'The description of level #' . $level_id))
					icl_add_string_translation($description_string_id, ICL_LANGUAGE_CODE, $insert_update_args['description'], ICL_TM_COMPLETE);
				unset($insert_update_args['name']);
				unset($insert_update_args['description']);
				
				unset($insert_update_args['categories']);
				unset($insert_update_args['locations']);
			} else { 
				$insert_update_args['categories'] = '';
				$insert_update_args['locations'] = '';
			}
		}
	}
	return $insert_update_args;
}

add_action('w2dc_update_level', 'w2dc_save_level_categories_locations', 10, 2);
function w2dc_save_level_categories_locations($level_id, $array) {
	global $sitepress;

	if (function_exists('wpml_object_id_filter') && $sitepress) {
		if ($sitepress->get_default_language() != ICL_LANGUAGE_CODE) {
			update_option('w2dc_wpml_level_categories_'.$level_id.'_'.ICL_LANGUAGE_CODE, w2dc_getValue($array, 'categories'));
			update_option('w2dc_wpml_level_locations_'.$level_id.'_'.ICL_LANGUAGE_CODE, w2dc_getValue($array, 'locations'));
		}
		
		if ($sitepress->get_default_language() == ICL_LANGUAGE_CODE) {
			do_action('wpml_register_single_string', 'Web 2.0 Directory', 'The name of level #' . $level_id, w2dc_getValue($array, 'name'));
			do_action('wpml_register_single_string', 'Web 2.0 Directory', 'The description of level #' . $level_id, w2dc_getValue($array, 'description'));
		}
	}
}
	
add_action('init', 'w2dc_load_levels_categories_locations');
function w2dc_load_levels_categories_locations() {
	global $w2dc_instance, $sitepress;

	if (function_exists('wpml_object_id_filter') && $sitepress) {
		if ($sitepress->get_default_language() != ICL_LANGUAGE_CODE) {
			foreach ($w2dc_instance->levels->levels_array AS &$level) {
				$_categories = get_option('w2dc_wpml_level_categories_'.$level->id.'_'.ICL_LANGUAGE_CODE);
				if ($_categories && (count($_categories) > 1 || $_categories != array('')))
					$level->categories = $_categories;
				else
					$level->categories = array();
				$_locations = get_option('w2dc_wpml_level_locations_'.$level->id.'_'.ICL_LANGUAGE_CODE);
				if ($_locations && (count($_locations) > 1 || $_locations != array('')))
					$level->locations = $_locations;
				else
					$level->locations = array();
			}
		}
	}
}

?>