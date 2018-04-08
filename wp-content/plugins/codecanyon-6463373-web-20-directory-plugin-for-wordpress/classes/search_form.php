<?php

class w2dc_search_form {
	public $uid;
	public $controller;
	public $args = array();
	public $search_fields_array = array();
	public $search_fields_array_advanced = array();
	public $search_fields_array_all = array();
	public $is_advanced_search_panel = false;
	public $advanced_open = false;
	
	public function __construct($uid = null, $controller = 'listings_controller', $args = array()) {
		global $w2dc_instance;

		$this->uid = $uid;
		$this->controller = $controller;
		
		$this->args = array_merge(array(
				'custom_home' => 0,
				'directory' => 0,
				'show_what_search' => get_option('w2dc_show_what_search'),
				'show_categories_search' => get_option('w2dc_show_categories_search'),
				'show_keywords_search' => get_option('w2dc_show_keywords_search'),
				'show_radius_search' => get_option('w2dc_show_radius_search'),
				'radius' => get_option('w2dc_radius_search_default'),
				'show_where_search' => get_option('w2dc_show_where_search'),
				'show_locations_search' => get_option('w2dc_show_locations_search'),
				'show_address_search' => get_option('w2dc_show_address_search'),
				'exact_categories' => array(),
				'exact_locations' => array(),
		), $args);

		if ($this->args['custom_home']) {
			if ($w2dc_instance->current_directory->categories)
				$this->args['exact_categories'] = $w2dc_instance->current_directory->categories;
			if ($w2dc_instance->current_directory->locations)
				$this->args['exact_locations'] = $w2dc_instance->current_directory->locations;
		} elseif ($this->args['directory'] && ($directory = $w2dc_instance->directories->getDirectoryById($this->args['directory']))) {
			if ($directory->categories)
				$this->args['exact_categories'] = $directory->categories;
			if ($directory->locations)
				$this->args['exact_locations'] = $directory->locations;
		}
		
		if (isset($this->args['exact_categories']) && !is_array($this->args['exact_categories']))
			if ($categories = array_filter(explode(',', $this->args['exact_categories']), 'trim'))
				$this->args['exact_categories'] = $categories;

		if (isset($this->args['exact_locations']) && !is_array($this->args['exact_locations']))
			if ($locations = array_filter(explode(',', $this->args['exact_locations']), 'trim'))
				$this->args['exact_locations'] = $locations;

		if ((isset($this->args['search_fields']) && $this->args['search_fields'] && $this->args['search_fields'] != -1) || (isset($this->args['search_fields_advanced']) && $this->args['search_fields_advanced'] && $this->args['search_fields_advanced'] != -1)) {
			$search_fields_ids = explode(',', $this->args['search_fields']);
			$search_fields_ids_advanced = explode(',', $this->args['search_fields_advanced']);
			$search_fields_ids_all = array_filter(array_merge($search_fields_ids, $search_fields_ids_advanced));
			
			foreach ($search_fields_ids_all AS $id) {
				if ($search_field = $w2dc_instance->search_fields->getSearchFieldById($id)) {
					if (in_array($id, $search_fields_ids))
						$this->search_fields_array[$id] = $search_field;
					elseif (in_array($id, $search_fields_ids_advanced))
						$this->search_fields_array_advanced[$id] = $search_field;
				}
			}
		} else {
			foreach ($w2dc_instance->search_fields->search_fields_array AS $id=>$search_field)
				if ($search_field->content_field->advanced_search_form && (!isset($this->args['search_fields_advanced']) || $this->args['search_fields_advanced'] != -1)) {
					$this->search_fields_array_advanced[$id] = $search_field;
				} elseif (!isset($this->args['search_fields']) || $this->args['search_fields'] != -1) {
					$this->search_fields_array[$id] = $search_field;
				}
		}
		
		$this->search_fields_array_all = $this->search_fields_array + $this->search_fields_array_advanced;
		
		foreach ($this->search_fields_array_all AS $search_field) {
			$search_field->resetValue();
		}
		
		if ($this->search_fields_array_advanced)
			$this->is_advanced_search_panel = true;

		if ((isset($_REQUEST['use_advanced']) && ($_REQUEST['use_advanced'] == 1)) || (isset($this->args['advanced_open']) && $this->args['advanced_open']))
			$this->advanced_open = true;
	}
	
	public function outputHiddenFields() {
		global $w2dc_instance, $wp_rewrite;

		$hidden_fields = array();

		if (!$wp_rewrite->using_permalinks() && $w2dc_instance->index_page_id && (get_option('show_on_front') != 'page' || get_option('page_on_front') != $w2dc_instance->index_page_id))
			$hidden_fields['page_id'] = $w2dc_instance->index_page_id;
		if ($w2dc_instance->index_page_id)
			$hidden_fields['w2dc_action'] = "search";
		else
			$hidden_fields['s'] = "search";
		if ($this->uid)
			$hidden_fields['hash'] = $this->uid;
		if ($this->controller)
			$hidden_fields['controller'] = $this->controller;
		
		$hidden_fields['include_categories_children'] = 1;

		// adapted for WPML
		global $sitepress;
		if (function_exists('wpml_object_id_filter') && $sitepress)
			if ($sitepress->get_option('language_negotiation_type') == 3)
				$hidden_fields['lang'] =  $sitepress->get_current_language();

		if ((!$this->args['show_what_search'] || !$this->args['show_categories_search']) && isset($this->args['category']) && $this->args['category'])
			$hidden_fields['categories'] = $this->args['category'];
		if ((!$this->args['show_what_search'] || !$this->args['show_keywords_search']) && isset($this->args['what_search']) && $this->args['what_search'])
			$hidden_fields['what_search'] = $this->args['what_search'];
		if ((!$this->args['show_where_search'] || !$this->args['show_locations_search']) && isset($this->args['location']) && $this->args['location'])
			$hidden_fields['location_id'] = $this->args['location'];
		if ((!$this->args['show_where_search'] || !$this->args['show_address_search']) && isset($this->args['address']) && $this->args['address'])
			$hidden_fields['address'] = $this->args['address'];
		if ((!$this->args['show_where_search'] || !$this->args['show_radius_search']) && isset($this->args['radius']) && $this->args['radius'])
			$hidden_fields['radius'] = $this->args['radius'];

		// output search params of fields, those are not on the search form
		foreach ($this->args AS $arg_name=>$arg_value) {
			if (strpos($arg_name, 'field_') === 0) {
				$is_visible_content_field = false;
				foreach ($this->search_fields_array_all AS $search_field) {
					if ($search_field->isParamOfThisField($arg_name)) {
						$is_visible_content_field = true;
						break;
					}
				}

				if (!$is_visible_content_field)
					$hidden_fields[$arg_name] = $arg_value;
			}
		}
		
		foreach ($hidden_fields AS $name=>$value) {
			if (is_array($value)) {
				foreach ($value AS $val)
					echo '<input type="hidden" name="' . esc_attr($name) . '[]" value="' . esc_attr($val) . '" />';
			} else
				echo '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" />';
		}
	}

	public function display($columns = 2, $advanced_open = false) {
		global $w2dc_instance;

		// random ID needed because there may be more than 1 search form on one page
		$random_id = w2dc_generateRandomVal();

		if ($this->args['directory'] && ($directory = $w2dc_instance->directories->getDirectoryById($this->args['directory']))) {
			$search_url = $directory->url;
		} else {
			$search_url = ($w2dc_instance->index_page_url) ? w2dc_directoryUrl() : home_url('/');
		}

		w2dc_renderTemplate('search_form.tpl.php',
			array(
				'random_id' => $random_id,
				'columns' => $columns,
				'is_advanced_search_panel' => $this->is_advanced_search_panel,
				'advanced_open' => $this->advanced_open,
				'search_url' => $search_url,
				'args' => $this->args,
				'search_form' => $this
			)
		);
	}
}
?>