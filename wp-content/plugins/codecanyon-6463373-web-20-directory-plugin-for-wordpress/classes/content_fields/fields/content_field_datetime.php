<?php 

class w2dc_content_field_datetime extends w2dc_content_field {
	public $is_time = true;
	
	protected $is_configuration_page = true;
	protected $can_be_searched = true;
	
	public function __construct() {
		// adapted for WPML
		//add_filter('wpml_config_array', array($this, 'wpml_config_array'));
	}
	
	public function isNotEmpty($listing) {
		if (isset($this->value['date']) && $this->value['date'])
			return true;
		if ($this->is_time)
			if ((isset($this->value['hour']) && $this->value['hour'] != '00') || (isset($this->value['minute']) && $this->value['minute'] != '00'))
				return true;

		return false;
	}

	public function configure() {
		global $wpdb, $w2dc_instance;

		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_configure_content_fields_nonce'], W2DC_PATH)) {
			$validation = new w2dc_form_validation();
			$validation->set_rules('is_time', __('Enable time in field', 'W2DC'), 'is_checked');
			if ($validation->run()) {
				$result = $validation->result_array();
				if ($wpdb->update($wpdb->w2dc_content_fields, array('options' => serialize(array('is_time' => $result['is_time']))), array('id' => $this->id), null, array('%d')))
					w2dc_addMessage(__('Field configuration was updated successfully!', 'W2DC'));
				
				$w2dc_instance->content_fields_manager->showContentFieldsTable();
			} else {
				$this->is_time = $validation->result_array('is_time');
				w2dc_addMessage($validation->error_array(), 'error');

				w2dc_renderTemplate('content_fields/fields/datetime_configuration.tpl.php', array('content_field' => $this));
			}
		} else
			w2dc_renderTemplate('content_fields/fields/datetime_configuration.tpl.php', array('content_field' => $this));
	}
	
	public function buildOptions() {
		if (isset($this->options['is_time']))
			$this->is_time = $this->options['is_time'];
	}
	
	public function delete() {
		global $wpdb;
	
		$wpdb->delete($wpdb->postmeta, array('meta_key' => '_content_field_' . $this->id . '_date'));
		$wpdb->delete($wpdb->postmeta, array('meta_key' => '_content_field_' . $this->id . '_hour'));
		$wpdb->delete($wpdb->postmeta, array('meta_key' => '_content_field_' . $this->id . '_minute'));
	
		$wpdb->delete($wpdb->w2dc_content_fields, array('id' => $this->id));
		return true;
	}
	
	public function renderInput() {
		wp_enqueue_script('jquery-ui-datepicker');
		
		if ($i18n_file = w2dc_getDatePickerLangFile(get_locale())) {
			wp_register_script('datepicker-i18n', $i18n_file, array('jquery-ui-datepicker'));
			wp_enqueue_script('datepicker-i18n');
		}

		w2dc_renderTemplate('content_fields/fields/datetime_input.tpl.php', array('content_field' => $this, 'dateformat' => w2dc_getDatePickerFormat()));
	}
	
	public function validateValues(&$errors, $data) {
		$field_index_date = 'w2dc-field-input-' . $this->id;
		$field_index_hour = 'w2dc-field-input-hour_' . $this->id;
		$field_index_minute = 'w2dc-field-input-minute_' . $this->id;

		$validation = new w2dc_form_validation();
		$rules = '';
		if ($this->canBeRequired() && $this->is_required)
			$rules .= 'required|is_natural_no_zero';
		$validation->set_rules($field_index_date, $this->name, $rules);
		$validation->set_rules($field_index_hour, $this->name);
		$validation->set_rules($field_index_minute, $this->name);
		if (!$validation->run())
			$errors[] = $validation->error_array();

		return array('date' => $validation->result_array($field_index_date), 'hour' => $validation->result_array($field_index_hour), 'minute' => $validation->result_array($field_index_minute));
	}
	
	public function saveValue($post_id, $validation_results) {
		if ($validation_results && is_array($validation_results)) {
			update_post_meta($post_id, '_content_field_' . $this->id . '_date', $validation_results['date']);
			update_post_meta($post_id, '_content_field_' . $this->id . '_hour', $validation_results['hour']);
			update_post_meta($post_id, '_content_field_' . $this->id . '_minute', $validation_results['minute']);
			return true;
		}
	}
	
	public function loadValue($post_id) {
		$this->value = array(
			'date' => get_post_meta($post_id, '_content_field_' . $this->id . '_date', true),
			'hour' => (get_post_meta($post_id, '_content_field_' . $this->id . '_hour', true) ? get_post_meta($post_id, '_content_field_' . $this->id . '_hour', true) : '00'),
			'minute' => (get_post_meta($post_id, '_content_field_' . $this->id . '_minute', true) ? get_post_meta($post_id, '_content_field_' . $this->id . '_minute', true) : '00'),
		);
		
		$this->value = apply_filters('w2dc_content_field_load', $this->value, $this, $post_id);
		return $this->value;
	}
	
	public function renderOutput($listing = null) {
		if ($this->value['date']) {
			$formatted_date = mysql2date(get_option('date_format'), date('Y-m-d H:i:s', $this->value['date']));
	
			w2dc_renderTemplate('content_fields/fields/datetime_output.tpl.php', array('content_field' => $this, 'formatted_date' => $formatted_date, 'listing' => $listing));
		}
	}
	
	public function orderParams() {
		$order_params = array('orderby' => 'meta_value_num', 'meta_key' => '_content_field_' . $this->id . '_date');
		if (get_option('w2dc_orderby_exclude_null'))
			$order_params['meta_query'] = array(
				array(
					'key' => '_content_field_' . $this->id . '_date',
					'value'   => array(''),
					'compare' => 'NOT IN'
				)
			);
		return $order_params;
	}
	
	public function validateCsvValues($value, &$errors) {
		$output = array();
		if ($tmstmp = strtotime($value)) {
			$output['minute'] = date('i', $tmstmp);
			$output['hour'] = date('H', $tmstmp);
			$output['date'] = $tmstmp - 3600*$output['hour'] - 60*$output['minute'];
		} else {
			$errors[] = __("Date-Time field is invalid", "W2DC");
		}
		return $output;
	}
	
	public function exportCSV() {
		if ($this->value['date'])
			return date('d.m.Y H:i', $this->value['date']);
	}
	
	public function renderOutputForMap($location, $listing) {
		if ($this->value['date']) {
			$formatted_date = mysql2date(get_option('date_format'), date('Y-m-d H:i:s', $this->value['date']));
	
			return w2dc_renderTemplate('content_fields/fields/datetime_output_map.tpl.php', array('content_field' => $this, 'formatted_date' => $formatted_date, 'listing' => $listing), true);
		}
	}
	
	// adapted for WPML
	/* public function wpml_config_array($config_all) {
		$config_all['wpml-config']['custom-fields']['custom-field'][] = array(
				'value' => '_content_field_' . $this->id . '_date',
				'attr' => array('action' => 'copy')
		);
		$config_all['wpml-config']['custom-fields']['custom-field'][] = array(
				'value' => '_content_field_' . $this->id . '_hour',
				'attr' => array('action' => 'copy')
		);
		$config_all['wpml-config']['custom-fields']['custom-field'][] = array(
				'value' => '_content_field_' . $this->id . '_minute',
				'attr' => array('action' => 'copy')
		);

		return $config_all;
	} */
}
?>