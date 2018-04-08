<?php 

class w2dc_directories_manager {
	public function __construct() {
		add_action('admin_menu', array($this, 'menu'));
	}

	public function menu() {
		add_submenu_page('w2dc_settings',
			__('Listings directories', 'W2DC'),
			__('Listings directories', 'W2DC'),
			'administrator',
			'w2dc_directories',
			array($this, 'w2dc_manage_directories_page')
		);
	}

	public function w2dc_manage_directories_page() {
		if (isset($_GET['action']) && $_GET['action'] == 'add') {
			$this->addOrEditDirectory();
		} elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['directory_id'])) {
			$this->addOrEditDirectory($_GET['directory_id']);
		} elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['directory_id'])) {
			$this->deleteDirectory($_GET['directory_id']);
		} else {
			$this->showDirectoriesTable();
		}
	}
	
	public function showDirectoriesTable() {
		global $w2dc_instance;
		
		$directories = $w2dc_instance->directories;

		$directories_table = new w2dc_manage_directories_table();
		$directories_table->prepareItems($directories);

		w2dc_renderTemplate('directories/directories_table.tpl.php', array('directories_table' => $directories_table));
	}
	
	public function addOrEditDirectory($directory_id = null) {
		global $w2dc_instance;

		$directories = $w2dc_instance->directories;
		
		if (!$directory = $directories->getDirectoryById($directory_id))
			$directory = new w2dc_directory();

		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_directories_nonce'], W2DC_PATH)) {
			$validation = new w2dc_form_validation();
			$validation->set_rules('name', __('Directory name', 'W2DC'), 'required');
			$validation->set_rules('single', __('Single form', 'W2DC'), 'required');
			$validation->set_rules('plural', __('Plural form', 'W2DC'), 'required');
			$validation->set_rules('listing_slug', __('Listing slug', 'W2DC'), 'alpha_dash');
			$validation->set_rules('category_slug', __('Category slug', 'W2DC'), 'alpha_dash');
			$validation->set_rules('location_slug', __('Location slug', 'W2DC'), 'alpha_dash');
			$validation->set_rules('tag_slug', __('Tag slug', 'W2DC'), 'alpha_dash');
			$validation->set_rules('categories', __('Assigned categories', 'W2DC'));
			$validation->set_rules('locations', __('Assigned locations', 'W2DC'));
			$validation->set_rules('levels', __('Levels', 'W2DC'));
			apply_filters('w2dc_directory_validation', $validation);
		
			if ($validation->run() && $this->checkSlugs($validation->result_array())) {
				if ($directory->id) {
					if ($directories->saveDirectoryFromArray($directory_id, $validation->result_array())) {
						w2dc_addMessage(__('Directory was updated successfully!', 'W2DC'));
					}
				} else {
					if ($directories->createDirectoryFromArray($validation->result_array())) {
						w2dc_addMessage(__('Directory was created succcessfully!', 'W2DC'));
					}
				}
				$this->showDirectoriesTable();
				//wp_redirect(admin_url('admin.php?page=w2dc_directories'));
				//die();
			} else {
				$directory->buildDirectoryFromArray($validation->result_array());
				w2dc_addMessage($validation->error_array(), 'error');
		
				w2dc_renderTemplate('directories/add_edit_directory.tpl.php', array('directory' => $directory, 'directory_id' => $directory_id));
			}
		} else {
			w2dc_renderTemplate('directories/add_edit_directory.tpl.php', array('directory' => $directory, 'directory_id' => $directory_id));
		}
	}
	
	public function deleteDirectory($directory_id) {
		global $w2dc_instance;

		$directories = $w2dc_instance->directories;
		if ($directory = $directories->getDirectoryById($directory_id)) {
			if (w2dc_getValue($_POST, 'submit') && ($new_directory_id = w2dc_getValue($_POST, 'new_directory')) && is_numeric($new_directory_id)) {
				if ($directories->deleteDirectory($directory_id, $new_directory_id))
					w2dc_addMessage(__('Directory was deleted successfully!', 'W2DC'));

				$this->showDirectoriesTable();
				//wp_redirect(admin_url('admin.php?page=w2dc_directories'));
				//die();
			} else {
				$question = sprintf(__('Are you sure you want delete "%s" directory?', 'W2DC'), $directory->name);
				$question .= '<br /><br />' . __('Existing listings will be moved to directory:', 'W2DC');
				foreach ($w2dc_instance->directories->directories_array AS $directory) {
					if ($directory->id != $directory_id)
						$question .= '<br />' . '<label><input type="radio" name="new_directory" value="' . $directory->id . '" ' . checked($directory->id, $w2dc_instance->directories->getDefaultDirectory()->id, false) . ' />' . $directory->name . '</label>';
				}
				
				w2dc_renderTemplate('delete_question.tpl.php', array('heading' => __('Delete directory', 'W2DC'), 'question' => $question, 'item_name' => $directory->name));
			}
		} else 
			$this->showLevelsTable();
	}
	
	public function checkSlugs($validation_results) {
		global $w2dc_instance;
		
		$slugs_to_check = array(
				$validation_results['listing_slug'],
				$validation_results['category_slug'],
				$validation_results['location_slug'],
				$validation_results['tag_slug'],
		);
		foreach ($w2dc_instance->index_pages_all AS $page) {
			if (in_array($page['slug'], $slugs_to_check)) {
				w2dc_addMessage(__('One or several slugs equal to the slug of directory page! This may cause problems.', 'W2DC'), 'error');
				return false;
			}
		}
		return true;
	}
}

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class w2dc_manage_directories_table extends WP_List_Table {

	public function __construct() {
		parent::__construct(array(
				'singular' => __('directory', 'W2DC'),
				'plural' => __('directories', 'W2DC'),
				'ajax' => false
		));
	}

	public function get_columns($directories = array()) {
		$columns = array(
				'id' => __('ID', 'W2DC'),
				'directory_name' => __('Name', 'W2DC'),
				'shortcode' => __('Shortcode', 'W2DC'),
				'listing_slug' => __('Listing slug', 'W2DC'),
				'category_slug' => __('Category slug', 'W2DC'),
				'location_slug' => __('Location slug', 'W2DC'),
				'tag_slug' => __('Tag slug', 'W2DC'),
		);
		$columns = apply_filters('w2dc_directory_table_header', $columns, $directories);

		return $columns;
	}
	
	public function getItems($directories) {
		$items_array = array();
		$first_directory = $directories->getDefaultDirectory();
		foreach ($directories->directories_array as $id=>$directory) {
			if ($id == $first_directory->id) {
				$shortcode = '[webdirectory]';
			} else {
				$shortcode = '[webdirectory id="' . $directory->id . '"]';
			}
			$items_array[$id] = array(
					'id' => $directory->id,
					'directory_name' => $directory->name,
					'shortcode' => $shortcode,
					'listing_slug' => $directory->listing_slug,
					'category_slug' => $directory->category_slug,
					'location_slug' => $directory->location_slug,
					'tag_slug' => $directory->tag_slug,
			);

			$items_array[$id] = apply_filters('w2dc_directory_table_row', $items_array[$id], $directory);
		}
		return $items_array;
	}

	public function prepareItems($directories) {
		$this->_column_headers = array($this->get_columns($directories), array(), array());
		
		$this->items = $this->getItems($directories);
	}
	
	public function column_directory_name($item) {
		global $w2dc_instance;

		$actions = array(
				'edit' => sprintf('<a href="?page=%s&action=%s&directory_id=%d">' . __('Edit', 'W2DC') . '</a>', $_GET['page'], 'edit', $item['id']),
				'delete' => sprintf('<a href="?page=%s&action=%s&directory_id=%d">' . __('Delete', 'W2DC') . '</a>', $_GET['page'], 'delete', $item['id']),
		);
		
		if ($item['id'] == $w2dc_instance->directories->getDefaultDirectory()->id) {
			unset($actions['delete']);
		}
		
		return sprintf('%1$s %2$s', sprintf('<a href="?page=%s&action=%s&directory_id=%d">' . $item['directory_name'] . '</a>', $_GET['page'], 'edit', $item['id']), $this->row_actions($actions));
	}

	public function column_default($item, $column_name) {
		switch($column_name) {
			default:
				return $item[$column_name];
		}
	}
	
	function no_items() {
		__('No directories found.', 'W2DC');
	}
}

?>