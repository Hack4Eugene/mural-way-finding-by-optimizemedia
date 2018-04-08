<?php 

class w2dc_content_field_hours extends w2dc_content_field {
	public $hours_clock = 12;
	public $week_days;
	
	protected $can_be_required = false;
	protected $can_be_ordered = false;
	protected $is_configuration_page = true;
	protected $can_be_searched = false;
	protected $is_search_configuration_page = false;
	
	public function __construct() {
		$this->week_days = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
	}
	
	public function isNotEmpty($listing) {
		if (array_filter($this->value))
			return true;
		else
			return false;
	}

	public function configure() {
		global $wpdb, $w2dc_instance;
	
		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_configure_content_fields_nonce'], W2DC_PATH)) {
			$validation = new w2dc_form_validation();
			$validation->set_rules('hours_clock', __('Time convention', 'W2DC'), 'required');
			if ($validation->run()) {
				$result = $validation->result_array();
				if ($wpdb->update($wpdb->w2dc_content_fields, array('options' => serialize(array('hours_clock' => $result['hours_clock']))), array('id' => $this->id), null, array('%d')))
					w2dc_addMessage(__('Field configuration was updated successfully!', 'W2DC'));
	
				$w2dc_instance->content_fields_manager->showContentFieldsTable();
			} else {
				$this->hours_clock = $validation->result_array('hours_clock');

				w2dc_renderTemplate('content_fields/fields/hours_configuration.tpl.php', array('content_field' => $this));
			}
		} else
			w2dc_renderTemplate('content_fields/fields/hours_configuration.tpl.php', array('content_field' => $this));
	}
	
	public function buildOptions() {
		if (isset($this->options['hours_clock']))
			$this->hours_clock = $this->options['hours_clock'];
	}
	
	public function orderWeekDays() {
		$week = array(intval(get_option('start_of_week')));
		while (count($week) < 7) {
			$day_num = $week[count($week)-1]+1;
			if ($day_num == 7) $day_num = 0;
			$week[] = $day_num;
		}
		foreach ($week AS $day_num)
			$week_days[$day_num] = $this->week_days[$day_num];
		
		$this->week_days_names = array(__('Sunday', 'W2DC'), __('Monday', 'W2DC'), __('Tuesday', 'W2DC'), __('Wednesday', 'W2DC'), __('Thursday', 'W2DC'), __('Friday', 'W2DC'), __('Saturday', 'W2DC'));
		
		return $week_days;
	}

	public function renderInput() {
		$week_days = $this->orderWeekDays();

		w2dc_renderTemplate('content_fields/fields/hours_input.tpl.php', array('content_field' => $this, 'week_days' => $week_days));
	}
	
	public function validateValues(&$errors, $data) {
		$validation = new w2dc_form_validation();
		foreach ($this->week_days AS $day) {
			if ($this->hours_clock == 12) {
				$validation->set_rules($day.'_from_hour_' . $this->id, $this->name, 'integer|greater_than[-1]|less_than[13]');
				$validation->set_rules($day.'_from_minute_' . $this->id, $this->name, 'integer|greater_than[-1]|less_than[60]');
				$validation->set_rules($day.'_from_am_pm_' . $this->id, $this->name);
				$validation->set_rules($day.'_to_hour_' . $this->id, $this->name, 'integer|greater_than[-1]|less_than[13]');
				$validation->set_rules($day.'_to_minute_' . $this->id, $this->name, 'integer|greater_than[-1]|less_than[60]');
				$validation->set_rules($day.'_to_am_pm_' . $this->id, $this->name);
			} elseif ($this->hours_clock == 24) {
				$validation->set_rules($day.'_from_hour_' . $this->id, $this->name, 'integer|greater_than[-1]|less_than[24]');
				$validation->set_rules($day.'_from_minute_' . $this->id, $this->name, 'integer|greater_than[-1]|less_than[60]');
				$validation->set_rules($day.'_to_hour_' . $this->id, $this->name, 'integer|greater_than[-1]|less_than[24]');
				$validation->set_rules($day.'_to_minute_' . $this->id, $this->name, 'integer|greater_than[-1]|less_than[60]');
			}
			$validation->set_rules($day.'_closed_' . $this->id, 'is_checked');
		}
		if (!$validation->run())
			$errors[] = $validation->error_array();

		$processed = false;
		foreach ($this->week_days AS $day) {
			if (!$validation->result_array($day.'_closed_'.$this->id)) {
				if ($this->hours_clock == 12 && $validation->result_array($day.'_from_am_pm_'.$this->id) != $validation->result_array($day.'_to_am_pm_'.$this->id))
					$processed = true;
				if ($validation->result_array($day.'_from_hour_'.$this->id) != '00' || $validation->result_array($day.'_from_minute_'.$this->id) != '00')
					$processed = true;
				$value[$day.'_from'] = $validation->result_array($day.'_from_hour_'.$this->id).':'.$validation->result_array($day.'_from_minute_'.$this->id).(($this->hours_clock == 12) ? ' '.$validation->result_array($day.'_from_am_pm_'.$this->id) : '');
				if ($validation->result_array($day.'_to_hour_'.$this->id) != '00' || $validation->result_array($day.'_to_minute_'.$this->id) != '00')
					$processed = true;
				$value[$day.'_to'] = $validation->result_array($day.'_to_hour_'.$this->id).':'.$validation->result_array($day.'_to_minute_'.$this->id).(($this->hours_clock == 12) ? ' '.$validation->result_array($day.'_to_am_pm_'.$this->id) : '');
				if ($validation->result_array($day.'_closed_'.$this->id))
					$processed = true;
			} else {
				$processed = true;
				$value[$day.'_closed'] = $validation->result_array($day.'_closed_'.$this->id);
			}
		}
		if (!$processed)
			$value = '';
		return $value;
	}
	
	public function saveValue($post_id, $validation_results) {
		return update_post_meta($post_id, '_content_field_' . $this->id, $validation_results);
	}
	
	public function loadValue($post_id) {
		$value = get_post_meta($post_id, '_content_field_' . $this->id, true);
		foreach ($this->week_days AS $day) {
			foreach (array('_from', '_to', '_closed') AS $from_to_closed) {
				if (isset($value[$day.$from_to_closed])) {
					$this->value[$day.$from_to_closed] = $value[$day.$from_to_closed];
				} else {
					$this->value[$day.$from_to_closed] = '';
				}
			}
		}

		$this->value = apply_filters('w2dc_content_field_load', $this->value, $this, $post_id);
		return $this->value;
	}
	
	public function renderOutput($listing = null) {
		w2dc_renderTemplate('content_fields/fields/hours_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}
	
	public function renderOutputForMap($location, $listing) {
		if ($strings = $this->processStrings())
			return '<div class="w2dc-map-field-hours">' . implode('<br />', $this->processStrings()) . '</div>';
	}
	
	public function processStrings() {
		$week_days = $this->orderWeekDays();
		
		$this->week_days_names = array(__('Sunday', 'W2DC'), __('Monday', 'W2DC'), __('Tuesday', 'W2DC'), __('Wednesday', 'W2DC'), __('Thursday', 'W2DC'), __('Friday', 'W2DC'), __('Saturday', 'W2DC'));
		$strings = array();
		foreach ($week_days AS $key=>$day) {
			if ($this->value[$day.'_from'] || $this->value[$day.'_to'] || $this->value[$day.'_closed'])
				$strings[] = '<strong>' . $this->week_days_names[$key] . '</strong> ' . (($this->value[$day.'_closed']) ? __('Closed', 'W2DC') : $this->value[$day.'_from'] . ' - ' . $this->value[$day.'_to']);
		}
		return $strings;
	}
	
	public function getOptionsHour($index) {
		$time = explode(':', $this->value[$index]);
		if ($time && $time[0])
			$hour = $time[0];
		else 
			$hour = '00';
		$result = '';
		$result .= '<option ' . selected('00', $hour, false) . '>00</option>';
		$result .= '<option ' . selected('01', $hour, false) . '>01</option>';
		$result .= '<option ' . selected('02', $hour, false) . '>02</option>';
		$result .= '<option ' . selected('03', $hour, false) . '>03</option>';
		$result .= '<option ' . selected('04', $hour, false) . '>04</option>';
		$result .= '<option ' . selected('05', $hour, false) . '>05</option>';
		$result .= '<option ' . selected('06', $hour, false) . '>06</option>';
		$result .= '<option ' . selected('07', $hour, false) . '>07</option>';
		$result .= '<option ' . selected('08', $hour, false) . '>08</option>';
		$result .= '<option ' . selected('09', $hour, false) . '>09</option>';
		$result .= '<option ' . selected('10', $hour, false) . '>10</option>';
		$result .= '<option ' . selected('11', $hour, false) . '>11</option>';
		$result .= '<option ' . selected('12', $hour, false) . '>12</option>';
		if ($this->hours_clock == 24) {
			$result .= '<option ' . selected('13', $hour, false) . '>13</option>';
			$result .= '<option ' . selected('14', $hour, false) . '>14</option>';
			$result .= '<option ' . selected('15', $hour, false) . '>15</option>';
			$result .= '<option ' . selected('16', $hour, false) . '>16</option>';
			$result .= '<option ' . selected('17', $hour, false) . '>17</option>';
			$result .= '<option ' . selected('18', $hour, false) . '>18</option>';
			$result .= '<option ' . selected('19', $hour, false) . '>19</option>';
			$result .= '<option ' . selected('20', $hour, false) . '>20</option>';
			$result .= '<option ' . selected('21', $hour, false) . '>21</option>';
			$result .= '<option ' . selected('22', $hour, false) . '>22</option>';
			$result .= '<option ' . selected('23', $hour, false) . '>23</option>';
		}
		return $result;
	}

	public function getOptionsMinute($index) {
		$time = explode(':', $this->value[$index]);
		if (count($time) > 1) {
			if (!($minute = intval(substr($time[1], 0, 2))) && is_int($minute))
				if (!($minute = intval(substr($time[1], 0, 1))) && is_int($minute))
					$minute = '00';
		} else 
			$minute = '00';
		$result = '';
		$result .= '<option ' . selected('00', $minute, false) . '>00</option>';
		$result .= '<option ' . selected('01', $minute, false) . '>01</option>';
		$result .= '<option ' . selected('02', $minute, false) . '>02</option>';
		$result .= '<option ' . selected('03', $minute, false) . '>03</option>';
		$result .= '<option ' . selected('04', $minute, false) . '>04</option>';
		$result .= '<option ' . selected('05', $minute, false) . '>05</option>';
		$result .= '<option ' . selected('06', $minute, false) . '>06</option>';
		$result .= '<option ' . selected('07', $minute, false) . '>07</option>';
		$result .= '<option ' . selected('08', $minute, false) . '>08</option>';
		$result .= '<option ' . selected('09', $minute, false) . '>09</option>';
		$result .= '<option ' . selected('10', $minute, false) . '>10</option>';
		$result .= '<option ' . selected('11', $minute, false) . '>11</option>';
		$result .= '<option ' . selected('12', $minute, false) . '>12</option>';
		$result .= '<option ' . selected('13', $minute, false) . '>13</option>';
		$result .= '<option ' . selected('14', $minute, false) . '>14</option>';
		$result .= '<option ' . selected('15', $minute, false) . '>15</option>';
		$result .= '<option ' . selected('16', $minute, false) . '>16</option>';
		$result .= '<option ' . selected('17', $minute, false) . '>17</option>';
		$result .= '<option ' . selected('18', $minute, false) . '>18</option>';
		$result .= '<option ' . selected('19', $minute, false) . '>19</option>';
		$result .= '<option ' . selected('20', $minute, false) . '>20</option>';
		$result .= '<option ' . selected('21', $minute, false) . '>21</option>';
		$result .= '<option ' . selected('22', $minute, false) . '>22</option>';
		$result .= '<option ' . selected('23', $minute, false) . '>23</option>';
		$result .= '<option ' . selected('24', $minute, false) . '>24</option>';
		$result .= '<option ' . selected('25', $minute, false) . '>25</option>';
		$result .= '<option ' . selected('26', $minute, false) . '>26</option>';
		$result .= '<option ' . selected('27', $minute, false) . '>27</option>';
		$result .= '<option ' . selected('28', $minute, false) . '>28</option>';
		$result .= '<option ' . selected('29', $minute, false) . '>29</option>';
		$result .= '<option ' . selected('30', $minute, false) . '>30</option>';
		$result .= '<option ' . selected('31', $minute, false) . '>31</option>';
		$result .= '<option ' . selected('32', $minute, false) . '>32</option>';
		$result .= '<option ' . selected('33', $minute, false) . '>33</option>';
		$result .= '<option ' . selected('34', $minute, false) . '>34</option>';
		$result .= '<option ' . selected('35', $minute, false) . '>35</option>';
		$result .= '<option ' . selected('36', $minute, false) . '>36</option>';
		$result .= '<option ' . selected('37', $minute, false) . '>37</option>';
		$result .= '<option ' . selected('38', $minute, false) . '>38</option>';
		$result .= '<option ' . selected('39', $minute, false) . '>39</option>';
		$result .= '<option ' . selected('40', $minute, false) . '>40</option>';
		$result .= '<option ' . selected('41', $minute, false) . '>41</option>';
		$result .= '<option ' . selected('42', $minute, false) . '>42</option>';
		$result .= '<option ' . selected('43', $minute, false) . '>43</option>';
		$result .= '<option ' . selected('44', $minute, false) . '>44</option>';
		$result .= '<option ' . selected('45', $minute, false) . '>45</option>';
		$result .= '<option ' . selected('46', $minute, false) . '>46</option>';
		$result .= '<option ' . selected('47', $minute, false) . '>47</option>';
		$result .= '<option ' . selected('48', $minute, false) . '>48</option>';
		$result .= '<option ' . selected('49', $minute, false) . '>49</option>';
		$result .= '<option ' . selected('50', $minute, false) . '>50</option>';
		$result .= '<option ' . selected('51', $minute, false) . '>51</option>';
		$result .= '<option ' . selected('52', $minute, false) . '>52</option>';
		$result .= '<option ' . selected('53', $minute, false) . '>53</option>';
		$result .= '<option ' . selected('54', $minute, false) . '>54</option>';
		$result .= '<option ' . selected('55', $minute, false) . '>55</option>';
		$result .= '<option ' . selected('56', $minute, false) . '>56</option>';
		$result .= '<option ' . selected('57', $minute, false) . '>57</option>';
		$result .= '<option ' . selected('58', $minute, false) . '>58</option>';
		$result .= '<option ' . selected('59', $minute, false) . '>59</option>';
		return $result;
	}
	
	public function getOptionsAmPm($index) {
		if (stripos($this->value[$index], 'am') !== FALSE)
			$am_pm = 'AM';
		elseif (stripos($this->value[$index], 'pm') !== FALSE)
			$am_pm = 'PM';
		else 
			$am_pm = '';
		$result = '';
		$result .= '<option ' . selected(__('AM', 'W2DC'), $am_pm, false) . '>' . __('AM', 'W2DC') . '</option>';
		$result .= '<option ' . selected(__('PM', 'W2DC'), $am_pm, false) . '>' . __('PM', 'W2DC') . '</option>';
		return $result;
	}
	
	public function validateCsvValues($value, &$errors) {
		$values = array_filter(array_map('trim', explode(',', $value)));
		$value = array();
		$processed_days = array();
		$processed = false;
		foreach ($values AS $item) {
			if ($this->hours_clock == 12) {
				preg_match("/(Mon|Tue|Wed|Thu|Fri|Sat|Sun)\s([0-12]{2}):([0-59]{2})\s(AM|PM)\s-\s([0-12]{2}):([0-59]{2})\s(AM|PM)/i", $item, $matches);
				$length_required = 8;
			} elseif ($this->hours_clock == 24) {
				preg_match("/(Mon|Tue|Wed|Thu|Fri|Sat|Sun)\s([0-23]{2}):([0-59]{2})\s-\s([0-23]{2}):([0-59]{2})/i", $item, $matches);
				$length_required = 6;
			}
			if ($matches && count($matches) == $length_required && in_array(strtolower($matches[1]), $this->week_days)) {
				$day = strtolower($matches[1]);
				$processed_days[] = $day;
				$processed = true;
				if ($this->hours_clock == 12) {
					$value[$day.'_from'] = $matches[2].':'.$matches[3].' '.strtoupper($matches[4]);
					$value[$day.'_to'] = $matches[5].':'.$matches[6].' '.strtoupper($matches[7]);
				} elseif ($this->hours_clock == 24) {
					$value[$day.'_from'] = $matches[2].':'.$matches[3];
					$value[$day.'_to'] = $matches[4].':'.$matches[5];
				}
			} else 
				$errors[] = __("Opening hours field value does not match required format", 'W2DC');
		}
		foreach ($this->week_days AS $day) {
			if (in_array($day, $processed_days))
				$value[$day.'_closed'] = 0;
			else
				$value[$day.'_closed'] = 1;
		}
		if (!$processed)
			$value = '';
		
		return $value;
	}
	
	public function exportCSV() {
		$week_days = $this->orderWeekDays();

		$output = array();
		foreach ($week_days AS $key=>$day) {
			if ($this->value[$day.'_from'] || $this->value[$day.'_to'] || $this->value[$day.'_closed']) {
				if (!$this->value[$day.'_closed']) {
					$output[] = ucfirst($this->week_days[$key]) . ' ' .  $this->value[$day.'_from'] . ' - ' . $this->value[$day.'_to'];
				} else {
					$output[] = '';
				}
			}
		}
		
		$output = array_filter($output);

		if ($output)
			return  implode(',', $output);
	}
}
?>