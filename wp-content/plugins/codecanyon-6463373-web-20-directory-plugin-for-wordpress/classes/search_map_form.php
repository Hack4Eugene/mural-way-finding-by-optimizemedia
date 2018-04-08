<?php

class w2dc_search_map_form extends w2dc_search_form {
	public $directories = array();
	public $exact_categories = array();
	public $exact_locations = array();

	public function __construct($uid = null, $controller = 'listings_controller', $directories = null) {
		global $w2dc_instance;
		
		$this->uid = $uid;
		$this->controller = $controller;
		
		if ($directories) {
			$this->directories = $directories;
			foreach ($directories AS $directory_id) {
				if ($directory = $w2dc_instance->directories->getDirectoryById($directory_id)) {
					if ($directory->categories)
						$this->exact_categories = array_merge($this->exact_categories, $directory->categories);
					if ($directory->locations)
						$this->exact_locations = array_merge($this->exact_locations, $directory->locations);
				}
			}
		}
	}

	public function display($columns = 2, $advanced_open = false) {
		global $w2dc_instance;

		// random ID needed because there may be more than 1 search form on one page
		$random_id = w2dc_generateRandomVal();
		
		if ($this->directories && ($directory_id = array_shift($this->directories)) && ($directory = $w2dc_instance->directories->getDirectoryById($directory_id))) {
			$search_url = $directory->url;
		} else {
			$search_url = ($w2dc_instance->index_page_url) ? w2dc_directoryUrl() : home_url('/');
		}

		w2dc_renderTemplate('search_map_form.tpl.php', array('random_id' => $random_id, 'exact_categories' => $this->exact_categories, 'exact_locations' => $this->exact_locations, 'search_url' => $search_url, 'hash' => $this->uid, 'controller' => $this->controller));
	}
}
?>