<?php 

class w2dc_content_field_price_search extends w2dc_content_field_number_search {

	public function renderSearch($random_id, $columns = 2, $defaults = array()) {
		if ($this->mode == 'exact_number') {
			if (isset($defaults['field_' . $this->content_field->slug]))
				$this->value = $defaults['field_' . $this->content_field->slug];
		} elseif ($this->mode == 'min_max' || $this->mode == 'min_max_slider' || $this->mode == 'range_slider') {
			if (isset($defaults['field_' . $this->content_field->slug . '_min']))
				$this->min_max_value['min'] = $defaults['field_' . $this->content_field->slug . '_min'];
			if (isset($defaults['field_' . $this->content_field->slug . '_max']))
				$this->min_max_value['max'] = $defaults['field_' . $this->content_field->slug . '_max'];
		}

		if ($this->mode == 'exact_number')
			w2dc_renderTemplate('search_fields/fields/price_input_exactnumber.tpl.php', array('search_field' => $this, 'columns' => $columns, 'random_id' => $random_id));
		elseif ($this->mode == 'min_max')
			w2dc_renderTemplate('search_fields/fields/price_input_minmax.tpl.php', array('search_field' => $this, 'columns' => $columns, 'random_id' => $random_id));
		elseif ($this->mode == 'min_max_slider' || $this->mode == 'range_slider')
			w2dc_renderTemplate('search_fields/fields/price_input_slider.tpl.php', array('search_field' => $this, 'columns' => $columns, 'random_id' => $random_id));
	}
}
?>