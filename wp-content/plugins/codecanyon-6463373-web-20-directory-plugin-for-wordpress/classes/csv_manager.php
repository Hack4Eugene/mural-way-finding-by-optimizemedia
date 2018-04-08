<?php

class w2dc_csv_manager {
	public $menu_page_hook;
	
	private $test_mode = false;
	
	private $log = array('errors' => array(), 'messages' => array());
	private $header_columns = array();
	private $rows = array();
	private $collated_fields = array();
	
	private $csv_file_name;
	private $images_dir;
	private $import_type = 'create';
	private $columns_separator;
	private $values_separator;
	private $if_term_not_found;
	private $selected_user;
	private $do_geocode;
	private $is_claimable;
	
	public $collation_fields;
	
	public function __construct() {
		// Export
		if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'w2dc_csv_import' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'export_settings' && isset($_REQUEST['csv_export'])) {
			add_action('admin_init', array($this, 'csvExport'));
		}
		// Export Images
		if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'w2dc_csv_import' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'export_settings' && isset($_REQUEST['export_images'])) {
			add_action('admin_init', array($this, 'exportImages'));
		}

		add_action('admin_menu', array($this, 'menu'));
	}
	
	public function menu() {
		$this->menu_page_hook = add_submenu_page('w2dc_settings',
			__('CSV Import/Export', 'W2DC'),
			__('CSV Import/Export', 'W2DC'),
			'administrator',
			'w2dc_csv_import',
			array($this, 'w2dc_csv_import')
		);
	}
	
	private function buildCollationColumns() {
		global $w2dc_instance;
		
		$this->collation_fields = array(
				'post_id' => __('Post ID* (existing listing)', 'W2DC'),
				'title' => __('Title*', 'W2DC'),
				'level_id' => __('Level ID*', 'W2DC'),
				'directory_id' => __('Directory ID', 'W2DC'),
				'user' => __('Author', 'W2DC'),
				'categories_list' => __('Categories', 'W2DC'),
				'listing_tags' => __('Tags', 'W2DC'),
				'content' => __('Description', 'W2DC'),
				'excerpt' => __('Summary', 'W2DC'),
				'locations_list' => __('Locations (existing or new)', 'W2DC'),
				'address_line_1' => __('Address line 1', 'W2DC'),
				'address_line_2' => __('Address line 2', 'W2DC'),
				'zip' => __('Zip code or postal index', 'W2DC'),
				'latitude' => __('Latitude', 'W2DC'),
				'longitude' => __('Longitude', 'W2DC'),
				'map_icon_file' => __('Map icon file', 'W2DC'),
				'images' => __('Images files', 'W2DC'),
				'videos' => __('YouTube or Vimeo videos', 'W2DC'),
				'expiration_date' => __('Listing expiration date', 'W2DC'),
				'contact_email' => __('Listing contact email', 'W2DC'),
				'claimable' => __('Make listing claimable', 'W2DC'),
		);
		
		$this->collation_fields = apply_filters('w2dc_csv_collation_fields_list', $this->collation_fields);
		
		foreach ($w2dc_instance->content_fields->content_fields_array AS $field)
			if (!$field->is_core_field)
				$this->collation_fields[$field->slug] = $field->name;
	}
	
	public function w2dc_csv_import() {
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'import_settings') {
			// 2nd Step
			$this->csvCollateColumns();
		} elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'import_collate' && isset($_REQUEST['csv_file_name'])) {
			// 3rd Step
			$this->csvImport();
		} elseif (!isset($_REQUEST['action'])) {
			// 1st Step
			$this->csvImportSettings();
		}
	}
	
	// 1st Step
	public function csvImportSettings($vars = array()) {

		w2dc_renderTemplate('csv_manager/import_settings.tpl.php', $vars);
	}

	// 2nd Step
	public function csvCollateColumns() {
		$this->buildCollationColumns();
		$users = get_users(array('orderby' => 'ID'));

		if ((w2dc_getValue($_POST, 'submit') || w2dc_getValue($_POST, 'goback')) && wp_verify_nonce($_POST['w2dc_csv_import_nonce'], W2DC_PATH)) {
			$errors = false;

			$validation = new w2dc_form_validation();
			$validation->set_rules('import_type', __('Import type', 'W2DC'), 'required');
			$validation->set_rules('columns_separator', __('Columns separator', 'W2DC'), 'required');
			$validation->set_rules('values_separator', __('categories separator', 'W2DC'), 'required');

			// GoBack button places on import results page
			if (w2dc_getValue($_POST, 'goback')) {
				$validation->set_rules('csv_file_name', __('CSV file name', 'W2DC'), 'required');
				$validation->set_rules('images_dir', __('Images directory', 'W2DC'));
				$validation->set_rules('if_term_not_found', __('Category not found', 'W2DC'), 'required');
				$validation->set_rules('listings_author', __('Listings author', 'W2DC'), 'required|numeric');
				$validation->set_rules('do_geocode', __('Geocode imported listings', 'W2DC'));
				if (get_option('w2dc_fsubmit_addon') && get_option('w2dc_claim_functionality'))
					$validation->set_rules('is_claimable', __('Configure imported listings as claimable', 'W2DC'));
				$validation->set_rules('fields[]', __('Listings fields', 'W2DC'));
			}

			if ($validation->run()) {
				$this->import_type = $validation->result_array('import_type');
				$this->columns_separator = $validation->result_array('columns_separator');
				$this->values_separator = $validation->result_array('values_separator');
				
				// GoBack button places on import results page
				if (w2dc_getValue($_POST, 'goback')) {
					$this->csv_file_name = $validation->result_array('csv_file_name');
					$this->images_dir = $validation->result_array('images_dir');
					$this->if_term_not_found = $validation->result_array('if_term_not_found');
					$this->selected_user = $validation->result_array('listings_author');
					$this->do_geocode = $validation->result_array('do_geocode');
					if (get_option('w2dc_fsubmit_addon') && get_option('w2dc_claim_functionality'))
						$this->is_claimable = $validation->result_array('is_claimable');
					$this->collated_fields = $validation->result_array('fields[]');
				}

				// GoBack button places on import results page
				if (w2dc_getValue($_POST, 'goback')) {
					$csv_file_name = $this->csv_file_name;

					if (!is_file($csv_file_name)) {
						w2dc_addMessage(esc_attr__("CSV temp file doesn't exist", 'W2DC'));
						return $this->csvImportSettings($validation->result_array());
					}

					if ($this->images_dir && !is_dir($this->images_dir)) {
						w2dc_addMessage(esc_attr__("Images temp directory doesn't exist", 'W2DC'));
						return $this->csvImportSettings($validation->result_array());
					}
				} else {
					$csv_file = $_FILES['csv_file'];

					if ($csv_file['error'] || !is_uploaded_file($csv_file['tmp_name'])) {
						w2dc_addMessage(__('There was a problem trying to upload CSV file', 'W2DC'), 'error');
						return $this->csvImportSettings($validation->result_array());
					}
	
					if (strtolower(pathinfo($csv_file['name'], PATHINFO_EXTENSION)) != 'csv' && $csv_file['type'] != 'text/csv') {
						w2dc_addMessage(__('This is not CSV file', 'W2DC'), 'error');
						return $this->csvImportSettings($validation->result_array());
					}
					
					if (function_exists('mb_detect_encoding') && !mb_detect_encoding(file_get_contents($csv_file['tmp_name']), 'UTF-8', true)) {
						w2dc_addMessage(__("CSV file must be in UTF-8", 'W2DC'), 'error');
						return $this->csvImportSettings($validation->result_array());
					}
					
					$upload_dir = wp_upload_dir();
					$csv_file_name = $upload_dir['path'] . '/' . $csv_file["name"];
					move_uploaded_file($csv_file['tmp_name'], $csv_file_name);

					if ($_FILES['images_file']['tmp_name']) {
						$images_file = $_FILES['images_file'];
						
						if ($images_file['error'] || !is_uploaded_file($images_file['tmp_name'])) {
							w2dc_addMessage(__('There was a problem trying to upload ZIP images file', 'W2DC'), 'error');
							return $this->csvImportSettings($validation->result_array());
						}
	
						if (!$this->extractImages($images_file['tmp_name'])) {
							w2dc_addMessage(__('There was a problem trying to unpack ZIP images file', 'W2DC'), 'error');
							return $this->csvImportSettings($validation->result_array());
						}
					}
				}
				
				$this->extractCsv($csv_file_name);

				if ($this->log['errors']) {
					foreach ($this->log['errors'] AS $message)
						w2dc_addMessage($message, 'error');

					return $this->csvImportSettings($validation->result_array());
				}
				
				if ($this->import_type == 'create') {
					unset($this->collation_fields['post_id']);
				}

				w2dc_renderTemplate('csv_manager/collate_columns.tpl.php', array(
						'collation_fields' => $this->collation_fields,
						'collated_fields' => $this->collated_fields,
						'headers' => $this->header_columns,
						'rows' => $this->rows,
						'import_type' => $this->import_type,
						'columns_separator' => $this->columns_separator,
						'values_separator' => $this->values_separator,
						'csv_file_name' => $csv_file_name,
						'images_dir' => $this->images_dir,
						'users' => $users,
						'if_term_not_found' => $this->if_term_not_found,
						'listings_author' => $this->selected_user,
						'do_geocode' => $this->do_geocode,
						'is_claimable' => $this->is_claimable,
				));
			} else {
				w2dc_addMessage($validation->error_array(), 'error');
				
				return $this->csvImportSettings($validation->result_array());
			}
		} else
			return $this->csvImportSettings();
	}
	
	// 3rd Step
	public function csvImport() {
		$this->buildCollationColumns();

		if ((w2dc_getValue($_POST, 'submit') || w2dc_getValue($_POST, 'tsubmit')) && wp_verify_nonce($_POST['w2dc_csv_import_nonce'], W2DC_PATH)) {
			if (w2dc_getValue($_POST, 'tsubmit'))
				$this->test_mode = true;

			$errors = false;

			$validation = new w2dc_form_validation();
			$validation->set_rules('import_type', __('Import type', 'W2DC'), 'required');
			$validation->set_rules('csv_file_name', __('CSV file name', 'W2DC'), 'required');
			$validation->set_rules('images_dir', __('Images directory', 'W2DC'));
			$validation->set_rules('columns_separator', __('Columns separator', 'W2DC'), 'required');
			$validation->set_rules('values_separator', __('categories separator', 'W2DC'), 'required');
			$validation->set_rules('if_term_not_found', __('Category not found', 'W2DC'), 'required');
			$validation->set_rules('listings_author', __('Listings author', 'W2DC'), 'required|numeric');
			$validation->set_rules('do_geocode', __('Geocode imported listings', 'W2DC'), 'is_checked');
			if (get_option('w2dc_fsubmit_addon') && get_option('w2dc_claim_functionality'))
				$validation->set_rules('is_claimable', __('Configure imported listings as claimable', 'W2DC'), 'is_checked');
			$validation->set_rules('fields[]', __('Listings fields', 'W2DC'));
				
			if ($validation->run()) {
				$this->import_type = $validation->result_array('import_type');
				$this->csv_file_name = $validation->result_array('csv_file_name');
				$this->images_dir = $validation->result_array('images_dir');
				$this->columns_separator = $validation->result_array('columns_separator');
				$this->values_separator = $validation->result_array('values_separator');
				$this->if_term_not_found = $validation->result_array('if_term_not_found');
				$this->selected_user = $validation->result_array('listings_author');
				$this->do_geocode = $validation->result_array('do_geocode');
				if (get_option('w2dc_fsubmit_addon') && get_option('w2dc_claim_functionality'))
					$this->is_claimable = $validation->result_array('is_claimable');
				$this->collated_fields = $validation->result_array('fields[]');
				
				if (!is_file($this->csv_file_name))
					$this->log['errors'][] = esc_attr__("CSV temp file doesn't exist", 'W2DC');

				if ($this->images_dir && !is_dir($this->images_dir))
					$this->log['errors'][] = esc_attr__("Images temp directory doesn't exist", 'W2DC');
				
				if ($this->import_type == 'update' && !in_array('post_id', $this->collated_fields))
					$this->log['errors'][] = esc_attr__("Post ID field wasn't collated", 'W2DC');
				
				if ($this->import_type == 'create' && !in_array('title', $this->collated_fields))
					$this->log['errors'][] = esc_attr__("Title field wasn't collated", 'W2DC');
				
				if ($this->import_type == 'create' && !in_array('level_id', $this->collated_fields))
					$this->log['errors'][] = esc_attr__("Level ID field wasn't collated", 'W2DC');
		
				if ($this->import_type == 'create' && $this->selected_user != 0 && !get_userdata($this->selected_user))
					$this->log['errors'][] = esc_attr__("There isn't author user you selected", 'W2DC');
				if ($this->import_type == 'create' && $this->selected_user == 0 && !in_array('user', $this->collated_fields))
					$this->log['errors'][] = esc_attr__("Author field wasn't collated and default author wasn't selected", 'W2DC');

				$this->extractCsv($this->csv_file_name);
				
				ob_implicit_flush(true);
				w2dc_renderTemplate('admin_header.tpl.php');
				
				echo "<h2>" . __('CSV Import', 'W2DC') . "</h2>";
				echo "<h3>" . __('Import results', 'W2DC') . "</h3>";

				if (!$this->log['errors']) {
					$this->processCSV();
	
					if (!$this->test_mode) {
						unlink($this->csv_file_name);
						if ($this->images_dir)
							$this->removeImagesDir($this->images_dir);
					}
				} else {
					foreach ($this->log['errors'] AS $error) {
						echo '<p>'.$error.'</p>';
					}
				}
				
				w2dc_renderTemplate('csv_manager/import_results.tpl.php', array(
						'log' => $this->log,
						'test_mode' => $this->test_mode,
						'fields' => $this->collated_fields,
						'import_type' => $this->import_type,
						'columns_separator' => $this->columns_separator,
						'values_separator' => $this->values_separator,
						'csv_file_name' => $this->csv_file_name,
						'images_dir' => $this->images_dir,
						'if_term_not_found' => $this->if_term_not_found,
						'listings_author' => $this->selected_user,
						'do_geocode' => $this->do_geocode,
						'is_claimable' => $this->is_claimable,
				));
			} else {
				w2dc_addMessage($validation->error_array(), 'error');
				
				return $this->csvImportSettings($validation->result_array());
			}
		}
	}
	
	private function extractCsv($csv_file) {
		ini_set('auto_detect_line_endings', true);

		if ($fp = fopen($csv_file, 'r')) {
			$n = 0;
			while (($line_columns = @fgetcsv($fp, 0, $this->columns_separator)) !== FALSE) {
				if ($line_columns) {
					if (!$this->header_columns) {
						$this->header_columns = $line_columns;
						foreach ($this->header_columns as &$column)
							$column = trim($column);
					} else {
						if (count($line_columns) > count($this->header_columns))
							$this->log['errors'][] = sprintf(__('Line %d has too many columns', 'W2DC'), $n+1);
						elseif (count($line_columns) < count($this->header_columns))
							$this->log['errors'][] = sprintf(__('Line %d has less columns than header line', 'W2DC'), $n+1);
						else
							$this->rows[] = $line_columns;
					}
				}
				$n++;
			}
			@fclose($fp);
		} else {
			$this->log['errors'][] = esc_attr__("Can't open CSV file", 'W2DC');
			return false;
		}
	}
	
	private function extractImages($zip_file) {
		$dir = trailingslashit(get_temp_dir() . 'w2dc_' . time());
		
		require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
		
		$zip = new PclZip($zip_file);
		if ($files = $zip->extract(PCLZIP_OPT_PATH, $dir, PCLZIP_OPT_REMOVE_ALL_PATH)) {
			$this->images_dir = $dir;
			return true;
		}

		return false;
	}
	
	private function removeImagesDir($dir) {
		if (!isset($GLOBALS['wp_filesystem']) || !is_object($GLOBALS['wp_filesystem'])) {
			WP_Filesystem();
		}

		$wp_file = new WP_Filesystem_Direct($dir);
		return $wp_file->rmdir($dir, true);
	}

	private function processCSV() {
		global $wpdb, $w2dc_instance;
		
		printf(__('Import started, number of available rows in file: %d', 'W2DC'), count($this->rows));
		echo "<br />";
		if ($this->test_mode) {
			_e('Test mode enabled', 'W2DC');
			echo "<br />";
		}

		$users_logins = array();
		$users_emails = array();
		$users_ids = array();
		$users = get_users();
		foreach ($users AS $user) {
			$users_logins[] = $user->user_login;
			$users_emails[] = $user->user_email;
			$users_ids[] = $user->ID;
		}
		
		$directories = $w2dc_instance->directories->directories_array;
		$directories_ids = array_keys($directories);

		$levels = $w2dc_instance->levels->levels_array;
		$levels_ids = array_keys($levels);

		$total_rejected_lines = 0;
		foreach ($this->rows as $line=>$row) {
			$n = $line+1;
			printf(__('Importing line %d...', 'W2DC'), $n);
			echo "<br />";
			$error_on_line = false;
			$listing_data = array();
			foreach ($this->collated_fields as $i=>$field) {
				$value = trim($row[$i]);
				
				if ($field == 'post_id' && $this->import_type == 'update') {
					if (($post = get_post($value)) && ($listing = w2dc_getListing($post))) {
						$listing_data['existing_listing'] = $listing;
						$listing_data['post_id'] = $value;
					} else {
						$error = sprintf(__('line %d: ', 'W2DC') . esc_attr__("Listing with ID \"%d\" doesn't exist", 'W2DC'), $n, $value);
						$error_on_line = $this->setErrorOnLine($error);
					}
				}

				if ($field == 'title') {
					$listing_data['title'] = $value;
					printf(__('Listing title: %s', 'W2DC'), $value);
					echo "<br />";
				} elseif ($field == 'user') {
					if (!$this->selected_user) {
						if ((($key = array_search($value, $users_logins)) !== FALSE) || (($key = array_search($value, $users_emails)) !== FALSE) || (($key = array_search($value, $users_ids))) !== FALSE)
							$listing_data['user_id'] = $users_ids[$key];
						else {
							$error = sprintf(__('line %d: ', 'W2DC') . esc_attr__("User \"%s\" doesn't exist", 'W2DC'), $n, $value);
							$error_on_line = $this->setErrorOnLine($error);
						}
					} else 
						$listing_data['user_id'] = $this->selected_user;
				} elseif ($field == 'level_id') {
					if (in_array($value, $levels_ids))
						$listing_data['level_id'] = $value;
					else {
						$error = sprintf(__('line %d: ', 'W2DC') . __('Wrong level ID', 'W2DC'), $n);
						$error_on_line = $this->setErrorOnLine($error);
					}
				} elseif ($field == 'directory_id') {
					if (in_array($value, $directories_ids))
						$listing_data['directory_id'] = $value;
					else {
						$error = sprintf(__('line %d: ', 'W2DC') . __('Wrong directory ID', 'W2DC'), $n);
						$error_on_line = $this->setErrorOnLine($error);
					}
				} elseif ($field == 'content') {
					$listing_data['content'] = $value;
				} elseif ($field == 'excerpt') {
					$listing_data['excerpt'] = $value;
				} elseif ($field == 'categories_list') {
					$listing_data['categories'] = array_filter(array_map('trim', explode($this->values_separator, $value)));
				} elseif ($field == 'listing_tags') {
					$listing_data['tags'] = array_filter(array_map('trim', explode($this->values_separator, $value)));
				} elseif ($field == 'locations_list') {
					$listing_data['locations'] = array_map('trim', explode($this->values_separator, $value));
				} elseif ($field == 'address_line_1') {
					$listing_data['address_line_1'] = array_map('trim', explode($this->values_separator, $value));
				} elseif ($field == 'address_line_2') {
					$listing_data['address_line_2'] = array_map('trim', explode($this->values_separator, $value));
				} elseif ($field == 'zip') {
					$listing_data['zip'] = array_map('trim', explode($this->values_separator, $value));
				} elseif ($field == 'latitude') {
					$listing_data['latitude'] = array_map('trim', explode($this->values_separator, $value));
				} elseif ($field == 'longitude') {
					$listing_data['longitude'] = array_map('trim', explode($this->values_separator, $value));
				} elseif ($field == 'map_icon_file') {
					$listing_data['map_icon_file'] = array_map('trim', explode($this->values_separator, $value));
				} elseif ($field == 'videos') {
					$listing_data['videos'] = array_filter(array_map('trim', explode($this->values_separator, $value)));
				} elseif ($field == 'images') {
					if ($this->images_dir) {
						$listing_data['images'] = array_filter(array_map('trim', explode($this->values_separator, $value)));
					} else {
						$error = sprintf(__('line %d: ', 'W2DC') . esc_attr__("Images column was specified, but ZIP archive wasn't upload", 'W2DC'), $n);
						$error_on_line = $this->setErrorOnLine($error);
					}
				} elseif ($content_field = $w2dc_instance->content_fields->getContentFieldBySlug($field)) {
					if (is_a($content_field, 'w2dc_content_field_checkbox')) {
						if ($value = array_map('trim', explode($this->values_separator, $value)))
							if (count($value) == 1)
								$value = array_shift($value);
					}

					if ($value) {
						$errors = array();
						$listing_data['content_fields'][$field] = $content_field->validateCsvValues($value, $errors);
						foreach ($errors AS $_error) {
							$error = sprintf(__('line %d: ', 'W2DC') . $_error, $n);
							$error_on_line = $this->setErrorOnLine($error);
						}
					}
				} elseif ($field == 'expiration_date') {
					if (!($timestamp = strtotime($value))) {
						$error = sprintf(__('line %d: ', 'W2DC') . esc_attr__("Expiration date value is incorrect", 'W2DC'), $n);
						$error_on_line = $this->setErrorOnLine($error);
					} else
						$listing_data['expiration_date'] = $timestamp;
				} elseif ($field == 'contact_email') {
					if (!is_email($value)) {
						$error = sprintf(__('line %d: ', 'W2DC') . esc_attr__("Contact email is incorrect", 'W2DC'), $n);
						$error_on_line = $this->setErrorOnLine($error);
					} else
						$listing_data['contact_email'] = $value;
				} elseif (get_option('w2dc_fsubmit_addon') && get_option('w2dc_claim_functionality') && (($field == 'claimable' && $value) || $this->is_claimable)) {
					$listing_data['claimable'] = true;
				}
				
				$listing_data = apply_filters('w2dc_csv_process_fields', $listing_data, $field, $value);
			}

			if (!$error_on_line) {
				if (!$this->test_mode) {
					if ($this->import_type == 'create') {
						$listing_data_level = $levels[$listing_data['level_id']];
	
						$new_post_args = array(
								'post_title' => $listing_data['title'],
								'post_type' => W2DC_POST_TYPE,
								'post_author' => (isset($listing_data['user_id'])) ? $listing_data['user_id'] : $this->selected_user,
								'post_status' => 'publish',
								'post_content' => (isset($listing_data['content']) ? $listing_data['content'] : ''),
								'post_excerpt' => (isset($listing_data['excerpt']) ? $listing_data['excerpt'] : ''),
						);
						$new_post_id = wp_insert_post($new_post_args);
						
						if (isset($listing_data['directory_id'])) {
							$directory_id = $listing_data['directory_id'];
						} else {
							$directory_id = $w2dc_instance->directories->getDefaultDirectory()->id;
						}
						add_post_meta($new_post_id, '_directory_id', $directory_id);
						
						$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->w2dc_levels_relationships} (post_id, level_id) VALUES(%d, %d) ON DUPLICATE KEY UPDATE level_id=%d", $new_post_id, $listing_data_level->id, $listing_data_level->id));
						
						add_post_meta($new_post_id, '_listing_created', true);
						add_post_meta($new_post_id, '_order_date', time());
						add_post_meta($new_post_id, '_listing_status', 'active');
						
						if (!$listing_data_level->eternal_active_period) {
							$expiration_date = w2dc_sumDates(time(), $listing_data_level->active_days, $listing_data_level->active_months, $listing_data_level->active_years);
							add_post_meta($new_post_id, '_expiration_date', $expiration_date);
						}
						
						if (isset($listing_data['locations'])) {
							$this->processDirectoryLocations($listing_data);
						}
	
						if (isset($listing_data['locations_ids']) || isset($listing_data['address_line_1'])) {
							$this->processlistingLocations($new_post_id, $listing_data, $listing_data_level);
						}
	
						if (isset($listing_data['categories'])) {
							$this->processCategories($new_post_id, $listing_data);
						}
		
						if (isset($listing_data['tags'])) {
							$this->processTags($new_post_id, $listing_data);
						}
						
						if (isset($listing_data['content_fields'])) {
							$this->processContentFields($new_post_id, $listing_data);
						}
						
						if (isset($listing_data['images'])) {
							$this->processImages($new_post_id, $listing_data);
						}
						
						if (isset($listing_data['videos'])) {
							$this->processVideos($new_post_id, $listing_data);
						}
						
						if (isset($listing_data['expiration_date'])) {
							update_post_meta($new_post_id, '_expiration_date', $listing_data['expiration_date']);
						}
	
						if (isset($listing_data['contact_email'])) {
							add_post_meta($new_post_id, '_contact_email', $listing_data['contact_email']);
						}
	
						if (isset($listing_data['claimable'])) {
							add_post_meta($new_post_id, '_is_claimable', true);
						}
						
						do_action('w2dc_csv_create_listing', $new_post_id, $listing_data);
					} elseif ($this->import_type == 'update') {
// -------------------- Update existing listing by ID ------------------------------------------------------------------------------------------------------------------
						$existing_post_id = $listing_data['post_id'];
						
						if (isset($listing_data['directory_id'])) {
							$directory_id = $listing_data['directory_id'];
							update_post_meta($existing_post_id, '_directory_id', $directory_id);
						}
						
						if (isset($listing_data['level_id'])) {
							$listing_data_level = $levels[$listing_data['level_id']];

							$wpdb->query($wpdb->prepare("UPDATE {$wpdb->w2dc_levels_relationships} SET level_id=%d WHERE post_id=%d", $listing_data_level->id, $existing_post_id));
						} else {
							$listing_data_level = $listing_data['existing_listing']->level;
						}
						
						$existing_post_args = array(
								'ID' => $existing_post_id,
						);
						if (isset($listing_data['user_id']) || $this->selected_user) {
							$existing_post_args['post_author'] = (isset($listing_data['user_id'])) ? $listing_data['user_id'] : $this->selected_user;
						}
						if (isset($listing_data['title'])) {
							$existing_post_args['post_title'] = $listing_data['title'];
						}
						if (isset($listing_data['content'])) {
							$existing_post_args['post_content'] = $listing_data['content'];
						}
						if (isset($listing_data['excerpt'])) {
							$existing_post_args['post_excerpt'] = $listing_data['excerpt'];
						}
						wp_update_post($existing_post_args);

						if (isset($listing_data['locations'])) {
							$this->processDirectoryLocations($listing_data);
						}
						
						if (isset($listing_data['locations_ids']) || isset($listing_data['address_line_1'])) {
							$this->processlistingLocations($existing_post_id, $listing_data, $listing_data_level);
						}
						
						if (isset($listing_data['categories'])) {
							wp_set_object_terms($existing_post_id, array(), W2DC_CATEGORIES_TAX);

							$this->processCategories($existing_post_id, $listing_data);
						}
						
						if (isset($listing_data['tags'])) {
							wp_set_object_terms($existing_post_id, array(), W2DC_TAGS_TAX);

							$this->processTags($existing_post_id, $listing_data);
						}
						
						if (isset($listing_data['content_fields'])) {
							$this->processContentFields($existing_post_id, $listing_data);
						}
						
						if (isset($listing_data['images'])) {
							wp_delete_attachments($existing_post_id);
							delete_post_meta($existing_post_id, '_attached_image');

							$this->processImages($existing_post_id, $listing_data);
						}
						
						if (isset($listing_data['videos'])) {
							delete_post_meta($existing_post_id, '_attached_video_id');
							
							$this->processVideos($existing_post_id, $listing_data);
						}
						
						if (isset($listing_data['expiration_date'])) {
							delete_post_meta($existing_post_id, '_expiration_date');
							
							update_post_meta($existing_post_id, '_expiration_date', $listing_data['expiration_date']);
						}
						
						if (isset($listing_data['contact_email'])) {
							delete_post_meta($existing_post_id, '_contact_email');

							add_post_meta($existing_post_id, '_contact_email', $listing_data['contact_email']);
						}
						
						if (isset($listing_data['claimable'])) {
							if ($listing_data['claimable'])
								add_post_meta($existing_post_id, '_is_claimable', true);
							else
								add_post_meta($existing_post_id, '_is_claimable', false);
						}
						
						do_action('w2dc_csv_update_listing', $existing_post_id, $listing_data);
					}
				}
			} else {
				$total_rejected_lines++;
			}
		}

		printf(__('Import finished, number of errors: %d, total rejected lines: %d', 'W2DC'), count($this->log['errors']), $total_rejected_lines);
		echo "<br />";
		echo "<br />";
	}
	
	public function setErrorOnLine($error) {
		$this->log['errors'][] = $error;
		echo $error;
		echo "<br />";
		return true;
	}
	
	public function processDirectoryLocations(&$listing_data) {
		foreach ($listing_data['locations'] as $location_item) {
			if (!is_numeric($location_item)) {
				$locations_chain = array_filter(array_map('trim', explode('>', $location_item)));
				$listing_term_id = 0;
				foreach ($locations_chain as $key => $location_name) {
					if ($term = term_exists($location_name, W2DC_LOCATIONS_TAX, $listing_term_id)) {
						$term_id = intval($term['term_id']);
						$listing_term_id = $term_id;
					} else {
						if ($this->if_term_not_found == 'create') {
							if ($newterm = wp_insert_term($location_name, W2DC_LOCATIONS_TAX, array('parent' => $listing_term_id)))
							if (!is_wp_error($newterm)) {
								$term_id = intval($newterm['term_id']);
								$listing_term_id = $term_id;
							} else {
								$error = sprintf(__('line %d: ', 'W2DC') . __('Something went wrong with directory location "%s"', 'W2DC'), $n, $location_name);
								$this->log['errors'][] = $error;
								echo $error;
								echo "<br />";
							}
						} else {
							$error = sprintf(__('line %d: ', 'W2DC') . esc_attr__("Directory location \"%s\" wasn't found, was skipped", 'W2DC'), $n, $location_name);
							$this->log['errors'][] = $error;
							echo $error;
							echo "<br />";
						}
					}
				}
				if ($listing_term_id)
					$listing_data['locations_ids'][] = $listing_term_id;
			} elseif (get_term($location_item, W2DC_LOCATIONS_TAX)) {
				$listing_data['locations_ids'][] = $location_item;
			} else {
				$error = sprintf(__('line %d: ', 'W2DC') . esc_attr__("Directory location with ID \"%d\" wasn't found", 'W2DC'), $n, $location_item);
				$this->log['errors'][] = $error;
				echo $error;
				echo "<br />";
			}
		}
	}
	
	public function processListingLocations($post_id, &$listing_data, $listing_data_level) {
		global $w2dc_instance;

		if (isset($listing_data['locations_ids']))
			$locations_items = $listing_data['locations_ids'];
		else
			$locations_items = $listing_data['address_line_1'];
		
		$locations_args = array();
		foreach ($locations_items AS $key=>$location_item) {
			if ($this->do_geocode && (!isset($listing_data['longitude'][$key]) || !isset($listing_data['latitude'][$key]))) {
				$location_string = '';
				if (isset($listing_data['locations_ids'][$key])) {
					$chain = array();
					$parent_id = $listing_data['locations_ids'][$key];
					while ($parent_id != 0) {
						if ($term = get_term($parent_id, W2DC_LOCATIONS_TAX)) {
							$chain[] = $term->name;
							$parent_id = $term->parent;
						} else
							$parent_id = 0;
					}
					$location_string = implode(', ', $chain);
				}
				if (isset($listing_data['address_line_1'][$key]))
					$location_string = $listing_data['address_line_1'][$key] . ' ' . $location_string;
				if (isset($listing_data['address_line_2'][$key]))
					$location_string = $listing_data['address_line_2'][$key] . ', ' . $location_string;
				if (isset($listing_data['zip'][$key]))
					$location_string = $location_string . ' ' . $listing_data['zip'][$key];
				if (get_option('w2dc_default_geocoding_location'))
					$location_string = $location_string . ' ' . get_option('w2dc_default_geocoding_location');
					
				$geoname = new w2dc_locationGeoname ;
				if ($result = $geoname->geonames_request(trim($location_string), 'coordinates')) {
					$listing_data['longitude'][$key] = $result[0];
					$listing_data['latitude'][$key] = $result[1];
				}
			}
		
			$locations_args['w2dc_location[]'][] = 1;
			$locations_args['selected_tax[]'][] = (isset($listing_data['locations_ids'][$key]) ? $listing_data['locations_ids'][$key] : 0);
			$locations_args['address_line_1[]'][] = (isset($listing_data['address_line_1'][$key]) ? $listing_data['address_line_1'][$key] : '');
			$locations_args['address_line_2[]'][] = (isset($listing_data['address_line_2'][$key]) ? $listing_data['address_line_2'][$key] : '');
			$locations_args['zip_or_postal_index[]'][] = (isset($listing_data['zip'][$key]) ? $listing_data['zip'][$key] : '');
		
			if (
			(!isset($listing_data['locations_ids'][$key]) && !isset($listing_data['address_line_1'][$key]) && !isset($listing_data['zip'][$key]))
			&&
			(isset($listing_data['latitude'][$key]) && isset($listing_data['longitude'][$key]))
			)
				$locations_args['manual_coords[]'][] = 1;
			else
				$locations_args['manual_coords[]'][] = 0;
		
			$locations_args['map_coords_1[]'][] = (isset($listing_data['latitude'][$key]) ? $listing_data['latitude'][$key] : '');
			$locations_args['map_coords_2[]'][] = (isset($listing_data['longitude'][$key]) ? $listing_data['longitude'][$key] : '');
			$locations_args['map_zoom'] = get_option('w2dc_default_map_zoom');
			$locations_args['map_icon_file[]'][] = (isset($listing_data['map_icon_file'][$key]) ? $listing_data['map_icon_file'][$key] : '');
		}
		$args = apply_filters('w2dc_csv_save_location_args', $locations_args, $post_id, $listing_data);
		
		$w2dc_instance->locations_manager->saveLocations($listing_data_level, $post_id, $locations_args);
	}
	
	public function processCategories($post_id, &$listing_data) {
		foreach ($listing_data['categories'] as $category_item) {
			$categories_chain = array_filter(array_map('trim', explode('>', $category_item)));
			$listing_term_id = 0;
			foreach ($categories_chain as $key => $category_name) {
				if ($term = term_exists($category_name, W2DC_CATEGORIES_TAX, $listing_term_id)) {
					$term_id = intval($term['term_id']);
					$listing_term_id = $term_id;
				} else {
					if ($this->if_term_not_found == 'create') {
						if ($newterm = wp_insert_term($category_name, W2DC_CATEGORIES_TAX, array('parent' => $listing_term_id)))
						if (!is_wp_error($newterm)) {
							$term_id = intval($newterm['term_id']);
							$listing_term_id = $term_id;
						} else {
							$error = sprintf(__('line %d: ', 'W2DC') . __('Something went wrong with directory category "%s"', 'W2DC'), $n, $category_name);
							$this->log['errors'][] = $error;
							echo $error;
							echo "<br />";
						}
					} else {
						$error = sprintf(__('line %d: ', 'W2DC') . esc_attr__("Directory category \"%s\" wasn't found, was skipped", 'W2DC'), $n, $category_name);
						$this->log['errors'][] = $error;
						echo $error;
						echo "<br />";
					}
				}
			}
			if ($listing_term_id)
				$listing_data['categories_ids'][] = $listing_term_id;
		}
		if (isset($listing_data['categories_ids']))
			wp_set_object_terms($post_id, $listing_data['categories_ids'], W2DC_CATEGORIES_TAX);
	}
	
	public function processTags($post_id, &$listing_data) {
		foreach ($listing_data['tags'] as $tag_name) {
			if ($term = term_exists($tag_name, W2DC_TAGS_TAX))
				$listing_data['tags_ids'][] = intval($term['term_id']);
			else {
				if ($this->if_term_not_found == 'create') {
					if ($newterm = wp_insert_term($tag_name, W2DC_TAGS_TAX))
					if (!is_wp_error($newterm))
						$listing_data['tags_ids'][] = intval($newterm['term_id']);
					else {
						$error = sprintf(__('line %d: ', 'W2DC') . __('Something went wrong with directory tag "%s"', 'W2DC'), $n, $tag_name);
						$this->log['errors'][] = $error;
						echo $error;
						echo "<br />";
					}
				} else {
					$error = sprintf(__('line %d: ', 'W2DC') . esc_attr__("Directory tag \"%s\" wasn't found, was skipped", 'W2DC'), $n, $tag_name);
					$this->log['errors'][] = $error;
					echo $error;
					echo "<br />";
				}
			}
		}
		if (isset($listing_data['tags_ids']))
			wp_set_object_terms($post_id, $listing_data['tags_ids'], W2DC_TAGS_TAX);
	}
	
	public function processContentFields($post_id, &$listing_data) {
		global $w2dc_instance;

		foreach ($listing_data['content_fields'] AS $field=>$values) {
			$content_field = $w2dc_instance->content_fields->getContentFieldBySlug($field);
			$content_field->saveValue($post_id, $values);
		}
	}
	
	public function processImages($post_id, &$listing_data) {
		foreach ($listing_data['images'] AS $image_item) {
			$value = explode('>', $image_item);
			$image_file_name = $value[0];
			$image_title = (isset($value[1]) ? $value[1] : '');
			if (file_exists($this->images_dir . $image_file_name)) {
				$filepath = $this->images_dir . $image_file_name;
		
				$file = array('name' => basename($filepath),
						'tmp_name' => $filepath,
						'error' => 0,
						'size' => filesize($filepath)
				);
		
				copy($filepath, $filepath . '.backup');
				$image = wp_handle_sideload($file, array('test_form' => FALSE));
				rename($filepath . '.backup', $filepath);
		
				if (!isset($image['error'])) {
					$attachment = array(
							'post_mime_type' => $image['type'],
							'post_title' => $image_title,
							'post_content' => '',
							'post_status' => 'inherit'
					);
					if ($attach_id = wp_insert_attachment($attachment, $image['file'], $post_id)) {
						require_once(ABSPATH . 'wp-admin/includes/image.php');
						$attach_data = wp_generate_attachment_metadata($attach_id, $image['file']);
						wp_update_attachment_metadata($attach_id, $attach_data);
							
						// insert attachment ID to the post meta
						add_post_meta($post_id, '_attached_image', $attach_id);
					} else {
						$error = sprintf(__('Image file "%s" could not be inserted.', 'W2DC'), $image_file_name);
						$this->log['errors'][] = $error;
						echo $error;
						echo "<br />";
					}
				} else {
					$error = sprintf(__("Image file \"%s\" wasn't attached. Full path: \"%s\". Error: %s", 'W2DC'), $image_file_name, $filepath, $image['error']);
					$this->log['errors'][] = $error;
					echo $error;
					echo "<br />";
				}
			} else {
				$error = sprintf(__("There isn't specified image file \"%s\" inside ZIP file. Or temp folder wasn't created: \"%s\"", 'W2DC'), $image_file_name, $this->images_dir);
				$this->log['errors'][] = $error;
				echo $error;
				echo "<br />";
			}
		}
	}
	
	public function processVideos($post_id, &$listing_data) {
		$validation = new w2dc_form_validation();
		foreach ($listing_data['videos'] AS $video_item) {
			$video_id = null;
			if ($validation->valid_url($video_item, false)) {
				preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $video_item, $matches_youtube);
				preg_match("#(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*#", $video_item, $matches_vimeo);
				if (isset($matches_youtube[0]) && strlen($matches_youtube[0]) == 11)
					$video_id = $matches_youtube[0];
				elseif (isset($matches_vimeo[5]) && strlen($matches_vimeo[5]) == 9) {
					$video_id = $matches_vimeo[5];
				} else {
					$error = sprintf(__('line %d: ', 'W2DC') . esc_attr__("YouTube or Vimeo video URL is incorrect", 'W2DC'), $n);
					$this->log['errors'][] = $error;
					echo $error;
					echo "<br />";
				}
			} else
				$video_id = $video_item;
			if ($video_id)
				add_post_meta($post_id, '_attached_video_id', $video_id);
		}
	}
	
	public function csvExport() {
		global $w2dc_instance;
		
		$number = 1000;
		$offset = 0;
		
		$validation = new w2dc_form_validation();
		$validation->set_rules('number', __('Listings number', 'W2DC'), 'integer');
		$validation->set_rules('offset', __('Listings offset', 'W2DC'), 'integer');
		if ($validation->run()) {
			if ($validation->result_array('number')) {
				$number = $validation->result_array('number');
			}
			if ($validation->result_array('offset')) {
				$offset = $validation->result_array('offset');
			}
		}

		$csv_columns = array(
				'post_id',
				'title',
				'directory_id',
				'level_id',
				'user',
				'description',
				'excerpt',
				'categories',
				'locations',
				'address_line_1',
				'address_line_2',
				'zip',
				'latitude',
				'longitude',
				'map_icon_file',
				'tags',
				'images',
				'videos',
				'expiration_date',
				'contact_email',
				'claimable',
		);
		
		foreach ($w2dc_instance->content_fields->content_fields_array AS $field)
			if (!$field->is_core_field)
				$csv_columns[] = $field->slug;

		$csv_output[] = $csv_columns;
		
		$args = array(
				'post_type' => W2DC_POST_TYPE,
				'orderby' => 'ID',
				'order' => 'ASC',
				'post_status' => 'publish,private,draft,pending',
				'posts_per_page' => $number,
				'offset' => $offset,
		);
		$query = new WP_Query($args);
		$count = 0;
		while ($query->have_posts()) {
			$count++;
			$query->the_post();
			$post = get_post();
			$listing = w2dc_getListing($post);
			
			$listing_id = $listing->post->ID;
			
			$categories = array();
			$categories_objects = wp_get_object_terms($listing_id, W2DC_CATEGORIES_TAX);
			foreach ($categories_objects AS $category) {
				$listing_categories = w2dc_get_term_parents($category->term_id, W2DC_CATEGORIES_TAX, false, true);
				$categories[] = implode(">", $listing_categories);
			}

			$tags = array();
			$tags_objects = wp_get_object_terms($listing_id, W2DC_TAGS_TAX);
			foreach ($tags_objects AS $tag) {
				$tags[] = $tag->name;
			}
			
			$selected_location = array();
			$address_line_1 = array();
			$address_line_2 = array();
			$zip = array();
			$map_coords_1 = array();
			$map_coords_2 = array();
			$map_icon_file = array();
			foreach ($listing->locations AS $location) {
				if ($location->selected_location) {
					$listing_locations = w2dc_get_term_parents($location->selected_location, W2DC_LOCATIONS_TAX, false, true);
					$listing_locations = $listing_locations;
					$categories[] = implode(">", $listing_locations);
				} else {
					$selected_location[] = '';
				}
				
				$address_line_1[] = ($location->address_line_1) ? $location->address_line_1 : '';
				$address_line_2[] = ($location->address_line_2) ? $location->address_line_2 : '';
				$zip[] = ($location->zip_or_postal_index) ? $location->zip_or_postal_index : '';
				$map_coords_1[] = ($location->map_coords_1) ? $location->map_coords_1 : '';
				$map_coords_2[] = ($location->map_coords_2) ? $location->map_coords_2 : '';
				$map_icon_file[] = ($location->map_icon_file) ? $location->map_icon_file : '';
			}
			
			$images = array();
			foreach ($listing->images AS $attachment_id=>$image) {
				$image_src = wp_get_attachment_image_src($attachment_id, 'full');
				$image_item = basename($image_src[0]);
				if ($image['post_title'])
					$image_item = ">" . $image['post_title'];
				$images[] = $image_item;
			}
			
			$videos = array();
			foreach ($listing->videos AS $video) {
				$videos[] = $video['id'];
			}

			$row = array(
					$listing_id,
					$listing->title(),
					$listing->directory->id,
					$listing->level->id,
					$listing->post->post_author,
					$listing->post->post_content,
					$listing->post->post_excerpt,
					implode(';', $categories),
					implode(';', $selected_location),
					implode(';', $address_line_1),
					implode(';', $address_line_2),
					implode(';', $zip),
					implode(';', $map_coords_1),
					implode(';', $map_coords_2),
					implode(';', $map_icon_file),
					implode(';', $tags),
					implode(';', $images),
					implode(';', $videos),
					((!$listing->level->eternal_active_period) ?  date('d.m.Y H:i', $listing->expiration_date) : ''),
					get_post_meta($listing_id, '_contact_email', true),
					get_post_meta($listing_id, '_is_claimable', true),
			);
			
			foreach ($w2dc_instance->content_fields->content_fields_array AS $field) {
				if (!$field->is_core_field) {
					if (isset($listing->content_fields[$field->id])) {
						$row[] = $listing->content_fields[$field->id]->exportCSV();
					} else {
						$row[] = '';
					}
				}
			}

			$csv_output[] = $row;
		}
		
		$csv_file_name = 'w2dc-listings--' . date('Y-m-d_H_i_s') . '--' . $count . '.csv';

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"" . $csv_file_name . "\";" );
		header("Content-Transfer-Encoding: binary");

		$outputBuffer = fopen("php://output", 'w');
		foreach($csv_output as $val) {
			fputcsv($outputBuffer, $val);
		}
		fclose($outputBuffer);
		
		exit;
	}
	
	public function exportImages() {
		$images = array();
		$upload_dir = wp_upload_dir();
		$upload_dir_path = trailingslashit($upload_dir['basedir']);

		$args = array(
				'post_type' => W2DC_POST_TYPE,
				'post_status' => 'publish,private,draft,pending',
				'posts_per_page' => -1
		);
		$query = new WP_Query($args);
		while ($query->have_posts()) {
			$query->the_post();
			$post = get_post();
			$listing = w2dc_getListing($post);

			foreach ($listing->images AS $attachment_id=>$image) {
				if ($image_file = wp_get_attachment_metadata($attachment_id, true)) {
					$file_path = $upload_dir_path . $image_file['file'];
					if (file_exists($file_path))
						$images[] = $file_path;
				}
			}
		}
		$images = array_unique($images);

		if ($images) {
			$zip_file = trailingslashit(get_temp_dir()) . 'w2dc_images.zip';
			
			require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');

			$zip = new PclZip($zip_file);
			$path = $zip->create(implode(',', $images), PCLZIP_OPT_REMOVE_ALL_PATH);
			if (!$path)
				die('Error : ' . $zip->errorInfo(true));

			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private", false);
			header("Content-Type: application/octet-stream");
			header('Content-Disposition: attachment; filename="w2dc_images.zip"');
			header('Content-Length: ' . filesize($zip_file));
			flush();
			readfile($zip_file);

			if (!isset($GLOBALS['wp_filesystem']) || !is_object($GLOBALS['wp_filesystem'])) {
				WP_Filesystem();
			}
			
			$wp_file = new WP_Filesystem_Direct();
			$wp_file->delete($zip_file);
		}

		exit;
	}
}

?>