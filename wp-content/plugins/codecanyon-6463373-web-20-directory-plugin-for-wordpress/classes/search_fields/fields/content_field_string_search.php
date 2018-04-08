<?php 

class w2dc_content_field_string_search extends w2dc_content_field_search {

	public function renderSearch($random_id, $columns = 2, $defaults = array()) {
		if (isset($defaults['field_' . $this->content_field->slug])) {
			$this->value = $defaults['field_' . $this->content_field->slug];
		}
		
		w2dc_renderTemplate('search_fields/fields/string_textarea_input.tpl.php', array('search_field' => $this, 'columns' => $columns, 'random_id' => $random_id));
	}
	
	public function validateSearch(&$args, $defaults = array(), $include_GET_params = true) {
		$field_index = 'field_' . $this->content_field->slug;

		if ($include_GET_params)
			$value = (w2dc_getValue($_REQUEST, $field_index) ? w2dc_getValue($_REQUEST, $field_index) : w2dc_getValue($defaults, $field_index));
		else
			$value = w2dc_getValue($defaults, $field_index);

		if ($value) {
			$this->value = $value;
			$args['meta_query']['relation'] = 'AND';
			$args['meta_query'][] = array(
					'key' => '_content_field_' . $this->content_field->id,
					'value' => stripslashes($this->value),
					'compare' => 'LIKE'
			);
		}
	}
	
	public function getVCParams() {
		return array(
				array(
						'type' => 'textfield',
						'param_name' => 'field_' . $this->content_field->slug,
						'heading' => $this->content_field->name,
				),
		);
	}
}
?>