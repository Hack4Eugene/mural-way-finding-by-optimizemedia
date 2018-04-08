<?php

function w2dc_tax_dropdowns_init($tax = 'category', $field_name = null, $term_id = null, $count = true, $labels = array(), $titles = array(), $uID = null, $exact_terms = array()) {
	// unique ID need when we place some dropdowns groups on one page
	if (!$uID)
		$uID = rand(1, 10000);

	$localized_data[$uID] = array(
			'labels' => $labels,
			'titles' => $titles
	);
	echo "<script>w2dc_js_objects['tax_dropdowns_" . $uID . "'] = " . json_encode($localized_data) . "</script>";

	if (!is_null($term_id) && $term_id != 0) {
		$chain = array();
		$parent_id = $term_id;
		while ($parent_id != 0) {
			if ($term = get_term($parent_id, $tax)) {
				$chain[] = $term->term_id;
				$parent_id = $term->parent;
			} else
				break;
		}
	}
	$path_chain = array();
	$chain[] = 0;
	$chain = array_reverse($chain);

	if (!$field_name) {
		$field_name = 'selected_tax[' . $uID . ']';
		$path_field_name = 'selected_tax_path[' . $uID . ']';
	} else {
		$path_field_name = $field_name . '_path';
	}

	echo '<div id="w2dc-tax-dropdowns-wrap-' . $uID . '" class="' . $tax . ' cs_count_' . (int)$count . ' w2dc-tax-dropdowns-wrap">';
	echo '<input type="hidden" name="' . $field_name . '" id="selected_tax[' . $uID . ']" class="selected_tax_' . $tax . '" value="' . $term_id . '" />';
	echo '<input type="hidden" id="exact_terms[' . $uID . ']" value="' . addslashes(implode(',', $exact_terms)) . '" />';
	foreach ($chain AS $key=>$term_id) {
		if ($count)
			// there is a wp bug with pad_counts in get_terms function - so we use this construction
			$terms = wp_list_filter(get_categories(array('taxonomy' => $tax, 'pad_counts' => true, 'hide_empty' => false)), array('parent' => $term_id));
		else
			$terms = get_categories(array('taxonomy' => $tax, 'pad_counts' => true, 'hide_empty' => false, 'parent' => $term_id));
		if (!empty($terms)) {
			foreach ($terms AS $id=>$term) {
				if ($exact_terms && (!in_array($term->term_id, $exact_terms) && !in_array($term->slug, $exact_terms))) {
					unset($terms[$id]);
				}
			}

			if (!empty($terms)) {
				$level_num = $key + 1;
				echo '<div id="wrap_chainlist_' . $level_num . '_' .$uID . '" class="w2dc-row w2dc-form-group w2dc-location-input">';
	
					if (isset($labels[$key]))
						echo '<label class="w2dc-col-md-2 w2dc-control-label" for="chainlist_' . $level_num . '_' . $uID . '">' . $labels[$key] . '</label>';
	
					if (isset($labels[$key]))
					echo '<div class="w2dc-col-md-10">';
					else
					echo '<div class="w2dc-col-md-12">';
						echo '<select id="chainlist_' . $level_num . '_' . $uID . '" class="w2dc-form-control">';
						echo '<option value="">- ' . ((isset($titles[$key])) ? $titles[$key] : __('Select term', 'W2DC')) . ' -</option>';
						foreach ($terms AS $term) {
							if ($count)
								$term_count = " ($term->count)";
							else
								 $term_count = '';
							if (isset($chain[$key+1]) && $term->term_id == $chain[$key+1]) {
								$selected = 'selected';
								$path_chain[] = $term->name;
							} else
								$selected = '';
							echo '<option id="' . $term->slug . '" value="' . $term->term_id . '" ' . $selected . '>' . $term->name . $term_count . '</option>';
						}
						echo '</select>';
					echo '</div>';
				echo '</div>';
			}
		}
		echo '<input type="hidden" name="' . $path_field_name . '" id="selected_tax_path[' . $uID . ']" class="selected_tax_path_' . $tax . '" value="' . implode(', ', $path_chain) . '" />';
	}
	echo '</div>';
}

function w2dc_tax_dropdowns_updateterms() {
	$parentid = w2dc_getValue($_POST, 'parentid');
	$next_level = w2dc_getValue($_POST, 'next_level');
	$tax = w2dc_getValue($_POST, 'tax');
	$count = w2dc_getValue($_POST, 'count');
	$exact_terms = array_filter(explode(',', w2dc_getValue($_POST, 'exact_terms')));
	if (!$label = w2dc_getValue($_POST, 'label'))
		$label = '';
	if (!$title = w2dc_getValue($_POST, 'title'))
		$title = __('Select term', 'W2DC');
	$uID = w2dc_getValue($_POST, 'uID');

	if ($count == 'cs_count_1')
		// there is a wp bug with pad_counts in get_terms function - so we use this construction
		$terms = wp_list_filter(get_categories(array('taxonomy' => $tax, 'pad_counts' => true, 'hide_empty' => false)), array('parent' => $parentid));
	else
		$terms = get_categories(array('taxonomy' => $tax, 'pad_counts' => true, 'hide_empty' => false, 'parent' => $parentid));
	if (!empty($terms)) {
		foreach ($terms AS $id=>$term) {
			if ($exact_terms && (!in_array($term->term_id, $exact_terms) && !in_array($term->slug, $exact_terms))) {
				unset($terms[$id]);
			}
		}

		if (!empty($terms)) {
			echo '<div id="wrap_chainlist_' . $next_level . '_' . $uID . '" class="w2dc-row w2dc-form-group w2dc-location-input">';
	
				if ($label)
					echo '<label class="w2dc-col-md-2 w2dc-control-label" for="chainlist_' . $next_level . '_' . $uID . '">' . $label . '</label>';
	
				if ($label)
				echo '<div class="w2dc-col-md-10">';
				else 
				echo '<div class="w2dc-col-md-12">';
					echo '<select id="chainlist_' . $next_level . '_' . $uID . '" class="w2dc-form-control">';
					echo '<option value="">- ' . $title . ' -</option>';
					foreach ($terms as $term) {
						if (!$exact_terms || (in_array($term->term_id, $exact_terms) || in_array($term->slug, $exact_terms))) {
							if ($count == 'cs_count_1') {
								$term_count = " ($term->count)";
							} else { $term_count = '';
							}
							echo '<option id="' . $term->slug . '" value="' . $term->term_id . '">' . $term->name . $term_count . '</option>';
						}
					}
			
					echo '</select>';
				echo '</div>';
			echo '</div>';
		}
	}
	die();
}

function w2dc_renderOptionsTerms($tax, $parent, $selected_terms, $level = 0) {
	$terms = get_terms($tax, array('parent' => $parent, 'hide_empty' => false));

	foreach ($terms AS $term) {
		echo '<option value="' . $term->term_id . '" ' . (($selected_terms && (in_array($term->term_id, $selected_terms) || in_array($term->slug, $selected_terms))) ? 'selected' : '') . '>' . (str_repeat('&nbsp;&nbsp;&nbsp;', $level)) . $term->name . '</option>';
		w2dc_renderOptionsTerms($tax, $term->term_id, $selected_terms, $level+1);
	}
	return $terms;
}
function w2dc_termsSelectList($name, $tax = 'category', $selected_terms = array()) {
	echo '<select multiple="multiple" name="' . $name . '[]" class="selected_terms_list w2dc-form-control w2dc-form-group" style="height: 300px">';
	echo '<option value="" ' . ((!$selected_terms) ? 'selected' : '') . '>' . __('- Select All -', 'W2DC') . '</option>';

	w2dc_renderOptionsTerms($tax, 0, $selected_terms);

	echo '</select>';
}

function w2dc_recaptcha() {
	if (get_option('w2dc_enable_recaptcha') && get_option('w2dc_recaptcha_public_key') && get_option('w2dc_recaptcha_private_key')) {
		return '<div class="g-recaptcha" data-sitekey="'.get_option('w2dc_recaptcha_public_key').'"></div>';
	}
}

function w2dc_is_recaptcha_passed() {
	if (get_option('w2dc_enable_recaptcha') && get_option('w2dc_recaptcha_public_key') && get_option('w2dc_recaptcha_private_key')) {
		if (isset($_POST['g-recaptcha-response']))
			$captcha = $_POST['g-recaptcha-response'];
		else
			return false;
		
		$response = wp_remote_get("https://www.google.com/recaptcha/api/siteverify?secret=".get_option('w2dc_recaptcha_private_key')."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
		if (!is_wp_error($response)) {
			$body = wp_remote_retrieve_body($response);
			$json = json_decode($body);
			if ($json->success === false)
				return false;
			else
				return true;
		} else
			return false;
	} else
		return true;
}

function w2dc_orderLinks($base_url, $defaults = array(), $return = false, $shortcode_hash = null) {
	global $w2dc_instance;

	if (isset($_GET['order_by']) && $_GET['order_by']) {
		$order_by = $_GET['order_by'];
		$order = w2dc_getValue($_GET, 'order', 'ASC');
	} else {
		if (isset($defaults['order_by']) && $defaults['order_by']) {
			$order_by = $defaults['order_by'];
			$order = w2dc_getValue($defaults, 'order', 'ASC');
		} else {
			$order_by = 'post_date';
			$order = 'DESC';
		}
	}

	$ordering['array'] = array();
	if (get_option('w2dc_orderby_date'))
		$ordering['array']['post_date'] = __('Date', 'W2DC');
	if (get_option('w2dc_orderby_title'))
		$ordering['array']['title'] = __('Title', 'W2DC');

	$category = w2dc_is_category();
	$content_fields = $w2dc_instance->content_fields->getOrderingContentFields();
	foreach ($content_fields AS $content_field) {
		if ($category && $content_field->categories) {
			if (in_array($category->term_id, $content_field->categories)) {
				$ordering['array'][$content_field->slug] = $content_field->name;
			}
		} else {
			$ordering['array'][$content_field->slug] = $content_field->name;
		}
	}
	
	$ordering['links'] = array();
	$ordering['struct'] = array();
	foreach ($ordering['array'] AS $field_slug=>$field_name) {
		$class = '';
		$next_order = 'DESC';
		if ($order_by == $field_slug) {
			if ($order == 'ASC') {
				$class = 'ascending';
				$next_order = 'DESC';
				$url = esc_url(add_query_arg(array('order_by' => $field_slug, 'order' => $next_order), $base_url));
			} elseif ($order == 'DESC') {
				$class = 'descending';
				$next_order = 'ASC';
				$url = esc_url(add_query_arg('order_by', $field_slug, $base_url));
			}
		} else {
			if ($field_slug == 'title') {
				$next_order = 'ASC';
				$url = esc_url(add_query_arg(array('order_by' => $field_slug, 'order' => $next_order), $base_url));
			} else
				$url = esc_url(add_query_arg('order_by', $field_slug, $base_url));
		}

		$ordering['links'][$field_slug] = '<a class="' . $class . '" href="' . $url . '" rel="nofollow">' .$field_name . '</a>';
		$ordering['struct'][$field_slug] = array('class' => $class, 'url' => $url, 'field_name' => $field_name, 'order' => $next_order);
	}

	$ordering = apply_filters('w2dc_ordering_options', $ordering, $base_url, $defaults, $shortcode_hash);

	if ($return)
		return $ordering;
	else
		echo __('Order by: ', 'W2DC') . implode(' | ', $ordering['links']);
}

function w2dc_orderingItems() {
	global $w2dc_instance;

	$ordering = array('post_date' => __('Date', 'W2DC'), 'title' => __('Title', 'W2DC'), 'rand' => __('Random', 'W2DC'));
	$content_fields = $w2dc_instance->content_fields->getOrderingContentFields();
	foreach ($content_fields AS $content_field)
		$ordering[$content_field->slug] = $content_field->name;
	$ordering = apply_filters('w2dc_default_orderby_options', $ordering);
	$ordering_items = array();
	foreach ($ordering AS $field_slug=>$field_name)
		$ordering_items[] = array('value' => $field_slug, 'label' => $field_name);
	
	return $ordering_items;
}

function w2dc_renderSubCategories($parent_category_slug = '', $columns = 2, $count = false, $exact_categories = array()) {
	if ($parent_category = w2dc_get_term_by_path($parent_category_slug))
		$parent_category_id = $parent_category->term_id;
	else
		$parent_category_id = 0;
	
	w2dc_renderAllCategories($parent_category_id, 1, $columns, $count, 0, array(), $exact_categories);
}

function w2dc_renderSubLocations($parent_location_slug = '', $columns = 2, $count = false, $exact_locations = array()) {
	if ($parent_location = w2dc_get_term_by_path($parent_location_slug))
		$parent_location_id = $parent_location->term_id;
	else
		$parent_location_id = 0;
	
	w2dc_renderAllLocations($parent_location_id, 1, $columns, $count, 0, $exact_locations);
}

function w2dc_terms_checklist($post_id) {
	if ($terms = get_categories(array('taxonomy' => W2DC_CATEGORIES_TAX, 'pad_counts' => true, 'hide_empty' => false, 'parent' => 0))) {
		$checked_categories_ids = array();
		$checked_categories = wp_get_object_terms($post_id, W2DC_CATEGORIES_TAX);
		foreach ($checked_categories AS $term)
			$checked_categories_ids[] = $term->term_id;

		echo '<ul id="w2dc-categorychecklist" class="w2dc-categorychecklist">';
		foreach ($terms AS $term) {
			$checked = '';
			if (in_array($term->term_id, $checked_categories_ids))
				$checked = 'checked';
				
			echo '
<li id="' . W2DC_CATEGORIES_TAX . '-' . $term->term_id . '">';
			echo '<label class="selectit"><input type="checkbox" ' . $checked . ' id="in-' . W2DC_CATEGORIES_TAX . '-' . $term->term_id . '" name="tax_input[' . W2DC_CATEGORIES_TAX . '][]" value="' . $term->term_id . '"> ' . $term->name . '</label>';
			echo _w2dc_terms_checklist($term->term_id, $checked_categories_ids);
			echo '</li>';
		}
		echo '</ul>';
	}
}
function _w2dc_terms_checklist($parent = 0, $checked_categories_ids = array()) {
	$html = '';
	if ($terms = get_categories(array('taxonomy' => W2DC_CATEGORIES_TAX, 'pad_counts' => true, 'hide_empty' => false, 'parent' => $parent))) {
		$html .= '<ul class="children">';
		foreach ($terms AS $term) {
			$checked = '';
			if (in_array($term->term_id, $checked_categories_ids))
				$checked = 'checked';

			$html .= '
<li id="' . W2DC_CATEGORIES_TAX . '-' . $term->term_id . '">';
			$html .= '<label class="selectit"><input type="checkbox" ' . $checked . ' id="in-' . W2DC_CATEGORIES_TAX . '-' . $term->term_id . '" name="tax_input[' . W2DC_CATEGORIES_TAX . '][]" value="' . $term->term_id . '"> ' . $term->name . '</label>';
			$html .= _w2dc_terms_checklist($term->term_id, $checked_categories_ids);
			$html .= '</li>';
		}
		$html .= '</ul>';
	}
	return $html;
}

function w2dc_tags_selectbox($post_id) {
	$terms = get_categories(array('taxonomy' => W2DC_TAGS_TAX, 'pad_counts' => true, 'hide_empty' => false));
	$checked_tags_ids = array();
	$checked_tags_names = array();
	$checked_tags = wp_get_object_terms($post_id, W2DC_TAGS_TAX);
	foreach ($checked_tags AS $term) {
		$checked_tags_ids[] = $term->term_id;
		$checked_tags_names[] = $term->name;
	}

	echo '<select name="' . W2DC_TAGS_TAX . '[]" multiple="multiple" class="w2dc-tokenizer">';
	foreach ($terms AS $term) {
		$checked = '';
		if (in_array($term->term_id, $checked_tags_ids))
			$checked = 'selected';
		echo '<option value="' . esc_attr($term->name) . '" ' . $checked . '>' . $term->name . '</option>';
	}
	echo '</select>';
}

function w2dc_categoriesOfLevels($allowed_levels = array()) {
	global $w2dc_instance;
	
	$allowed_categories = array();
	foreach ((array) $allowed_levels AS $level_id) {
		if (isset($w2dc_instance->levels->levels_array[$level_id])) {
			$level = $w2dc_instance->levels->levels_array[$level_id];
			$allowed_categories = array_merge($allowed_categories, $level->categories);
		}
	}
	
	return $allowed_categories;
}

function w2dc_renderAllCategories($parent = 0, $depth = 2, $columns = 2, $count = false, $max_subcategories = 0, $allowed_levels = array(), $exact_categories = array()) {
	if ($depth > 2)
		$depth = 2;
	if ($depth == 0 || !is_numeric($depth))
		$depth = 1;
	if ($columns > 4)
		$columns = 4;
	if ($columns == 0 || !is_numeric($columns))
		$columns = 1;
	
	$allowed_categories = implode(',', w2dc_categoriesOfLevels($allowed_levels));

	// we use array_merge with empty array because we need to flush keys in terms array
	if ($count)
		$terms = array_merge(
			// there is a wp bug with pad_counts in get_terms function - so we use this construction
			wp_list_filter(
					get_categories(array(
							'taxonomy' => W2DC_CATEGORIES_TAX,
							'pad_counts' => true,
							'hide_empty' => false,
							// filter terms by listings levels
							'include' => $allowed_categories,
					)),
					array('parent' => $parent)
			), array());
	else
		$terms = array_merge(
			get_categories(array(
					'taxonomy' => W2DC_CATEGORIES_TAX,
					'pad_counts' => true,
					'hide_empty' => false,
					'parent' => $parent,
					// filter terms by listings levels
					'include' => $allowed_categories,
			)), array());

	if ($terms) {
		foreach ($terms AS $key=>$term) {
			$term_children = get_term_children($term->term_id, W2DC_CATEGORIES_TAX);
			$term_children_slugs = array();
			if (is_array($term_children))
			foreach ($term_children AS $term_id) {
				$term_child = get_term($term_id, W2DC_CATEGORIES_TAX);
				$term_children_slugs[] = $term_child->slug;
			}
			if ($exact_categories && (!in_array($term->term_id, $exact_categories) && !in_array($term->slug, $exact_categories)) && (!is_array($term_children) || (!array_intersect($exact_categories, $term_children) && !array_intersect($exact_categories, $term_children_slugs)))) {
				unset($terms[$key]);
			}
		}
		
		if ($terms) {
			echo '<div class="w2dc-content w2dc-categories-columns w2dc-categories-columns-' . $columns . '">';
			$terms_count = count($terms);
			$counter = 0;
			$tcounter = 0;
	
			foreach ($terms AS $key=>$term) {
				$tcounter++;
				if ($counter == 0)
					echo '<div class="w2dc-categories-row ' . (($columns == 1) ? 'w2dc-categories-row-one-column': '') . '">';
		
				echo '<div class="w2dc-categories-column w2dc-categories-column-' . $columns . '">';
				if ($count)
					$term_count = " ($term->count)";
				else
					$term_count = '';
		
				if ($icon_file = w2dc_getCategoryIcon($term->term_id))
					$icon_image = '<img class="w2dc-field-icon" src="' . W2DC_CATEGORIES_ICONS_URL . $icon_file . '" />';
				else
					$icon_image = '';
		
				echo '<div class="w2dc-categories-column-wrapper">';
				echo '<div class="w2dc-categories-root"><a href="' . get_term_link($term) . '" title="' . $term->name .$term_count . '">' . $icon_image . $term->name .$term_count . '</a></div>';
				echo _w2dc_renderAllCategories($term->term_id, $depth, 1, $count, $max_subcategories, $allowed_categories, $exact_categories);
				echo '</div>';
		
				echo '</div>';
		
				$counter++;
				if ($counter == $columns)
					echo '</div>';
				elseif ($tcounter == $terms_count && $counter != $columns) {
					while ($counter != $columns) {
						echo '<div class="w2dc-categories-column w2dc-categories-column-' . $columns . ' w2dc-categories-column-hidden"></div>';
						$counter++;
					}
					echo '</div>';
				}
				if ($counter == $columns) $counter = 0;
			}
			echo '</div>';
		}
	}
}
function _w2dc_renderAllCategories($parent = 0, $depth = 2, $level = 0, $count = false, $max_subcategories = 0, $allowed_categories = array(), $exact_categories = array()) {
	if ($count)
		// there is a wp bug with pad_counts in get_terms function - so we use this construction
		$terms = wp_list_filter(
				get_categories(array(
						'taxonomy' => W2DC_CATEGORIES_TAX,
						'pad_counts' => true,
						'hide_empty' => false,
						// filter terms by listings levels
						'include' => $allowed_categories,
				)),
				array('parent' => $parent)
		);
	else
		$terms = get_categories(array(
				'taxonomy' => W2DC_CATEGORIES_TAX,
				'pad_counts' => true,
				'hide_empty' => false,
				'parent' => $parent,
				// filter terms by listings levels
				'include' => $allowed_categories,
		));

	$html = '';
	if ($terms && ($depth == 0 || !is_numeric($depth) || $depth > $level)) {
		foreach ($terms AS $key=>$term) {
			if ($exact_categories && (!in_array($term->term_id, $exact_categories) && !in_array($term->slug, $exact_categories))) {
				unset($terms[$key]);
			}
		}
		
		if ($terms) {
			$level++;
			$counter = 0;
			$html .= '<div class="subcategories">';
			$html .= '<ul>';
			foreach ($terms AS $term) {
				if ($count)
					$term_count = " ($term->count)";
				else
					$term_count = '';
	
				if ($icon_file = w2dc_getCategoryIcon($term->term_id))
					$icon_image = '<img class="w2dc-field-icon" src="' . W2DC_CATEGORIES_ICONS_URL . $icon_file . '" />';
				else
					$icon_image = '';
	
				$counter++;
				if ($max_subcategories != 0 && $counter > $max_subcategories) {
					$html .= '<li><a href="' . get_term_link(intval($parent), W2DC_CATEGORIES_TAX) . '">' . __('View all subcategories ->', 'W2DC') . '</a></li>';
					break;
				} else
					$html .= '<li><a href="' . get_term_link($term) . '" title="' . $term->name .$term_count . '">' . $icon_image . $term->name . $term_count . '</a></li>';
			}
			$html .= '</ul>';
			$html .= '</div>';
		}
	}
	return $html;
}

function w2dc_renderAllLocations($parent = 0, $depth = 2, $columns = 2, $count = false, $max_sublocations = 0, $exact_locations = array()) {
	if ($depth > 2)
		$depth = 2;
	if ($depth == 0 || !is_numeric($depth))
		$depth = 1;
	if ($columns > 4)
		$columns = 4;
	if ($columns == 0 || !is_numeric($columns))
		$columns = 1;

	// we use array_merge with empty array because we need to flush keys in terms array
	if ($count)
		$terms = array_merge(
			// there is a wp bug with pad_counts in get_terms function - so we use this construction
			wp_list_filter(
					get_categories(array(
							'taxonomy' => W2DC_LOCATIONS_TAX,
							'pad_counts' => true,
							'hide_empty' => false,
					)),
					array('parent' => $parent)
			), array());
	else
		$terms = array_merge(
			get_categories(array(
					'taxonomy' => W2DC_LOCATIONS_TAX,
					'pad_counts' => true,
					'hide_empty' => false,
					'parent' => $parent,
			)), array());

	if ($terms) {
		foreach ($terms AS $key=>$term) {
			$term_children = get_term_children($term->term_id, W2DC_LOCATIONS_TAX);
			$term_children_slugs = array();
			if (is_array($term_children))
			foreach ($term_children AS $term_id) {
				$term_child = get_term($term_id, W2DC_LOCATIONS_TAX);
				$term_children_slugs[] = $term_child->slug;
			}
			if ($exact_locations && (!in_array($term->term_id, $exact_locations) && !in_array($term->slug, $exact_locations)) && (!is_array($term_children) || (!array_intersect($exact_locations, $term_children) && !array_intersect($exact_locations, $term_children_slugs)))) {
				unset($terms[$key]);
			}
		}
		
		if ($terms) {
			echo '<div class="w2dc-content w2dc-locations-columns w2dc-locations-columns-' . $columns . '">';
			$terms_count = count($terms);
			$counter = 0;
			$tcounter = 0;
	
			foreach ($terms AS $key=>$term) {
				$tcounter++;
				if ($counter == 0)
					echo '<div class="w2dc-locations-row ' . (($columns == 1) ? 'w2dc-locations-row-one-column': '') . '">';
	
				echo '<div class="w2dc-locations-column w2dc-locations-column-' . $columns . '">';
				if ($count)
					$term_count = " ($term->count)";
				else
					$term_count = '';
	
				if ($icon_file = w2dc_getLocationIcon($term->term_id))
					$icon_image = '<img class="w2dc-field-icon" src="' . W2DC_LOCATIONS_ICONS_URL . $icon_file . '" />';
				else
					$icon_image = '';
	
				echo '<div class="w2dc-locations-column-wrapper">';
				echo '<div class="w2dc-locations-root"><a href="' . get_term_link($term) . '" title="' . $term->name .$term_count . '">' . $icon_image . $term->name .$term_count . '</a></div>';
				echo _w2dc_renderAllLocations($term->term_id, $depth, 1, $count, $max_sublocations, $exact_locations);
				echo '</div>';
	
				echo '</div>';
	
				$counter++;
				if ($counter == $columns)
					echo '</div>';
				elseif ($tcounter == $terms_count && $counter != $columns) {
					while ($counter != $columns) {
						echo '<div class="w2dc-locations-column w2dc-locations-column-' . $columns . ' w2dc-locations-column-hidden"></div>';
						$counter++;
					}
					echo '</div>';
				}
				if ($counter == $columns) $counter = 0;
			}
			echo '</div>';
		}
	}
}
function _w2dc_renderAllLocations($parent = 0, $depth = 2, $level = 0, $count = false, $max_sublocations = 0, $exact_locations = array()) {
	if ($count)
		// there is a wp bug with pad_counts in get_terms function - so we use this construction
		$terms = wp_list_filter(
				get_categories(array(
						'taxonomy' => W2DC_LOCATIONS_TAX,
						'pad_counts' => true,
						'hide_empty' => false,
				)),
				array('parent' => $parent)
		);
	else
		$terms = get_categories(array(
				'taxonomy' => W2DC_LOCATIONS_TAX,
				'pad_counts' => true,
				'hide_empty' => false,
				'parent' => $parent,
		));

	$html = '';
	if ($terms && ($depth == 0 || !is_numeric($depth) || $depth > $level)) {
		foreach ($terms AS $key=>$term) {
			if ($exact_locations && (!in_array($term->term_id, $exact_locations) && !in_array($term->slug, $exact_locations))) {
				unset($terms[$key]);
			}
		}

		if ($terms) {
			$level++;
			$counter = 0;
			$html .= '<div class="sublocations">';
			$html .= '<ul>';
			foreach ($terms AS $term) {
				if ($count)
					$term_count = " ($term->count)";
				else
					$term_count = '';
	
				if ($icon_file = w2dc_getLocationIcon($term->term_id))
					$icon_image = '<img class="w2dc-field-icon" src="' . W2DC_LOCATIONS_ICONS_URL . $icon_file . '" />';
				else
					$icon_image = '';
	
				$counter++;
				if ($max_sublocations != 0 && $counter > $max_sublocations) {
					$html .= '<li><a href="' . get_term_link(intval($parent), W2DC_LOCATIONS_TAX) . '">' . __('View all sublocations ->', 'W2DC') . '</a></li>';
					break;
				} else
					$html .= '<li><a href="' . get_term_link($term) . '" title="' . $term->name .$term_count . '">' . $icon_image . $term->name . $term_count . '</a></li>';
			}
			$html .= '</ul>';
			$html .= '</div>';
		}
	}
	return $html;
}

function w2dc_getCategoryIcon($term_id) {
	global $w2dc_instance;
	
	if ($icon_file = $w2dc_instance->categories_manager->getCategoryIconFile($term_id))
		return $icon_file;
}

function w2dc_getLocationIcon($term_id) {
	global $w2dc_instance;
	
	if ($icon_file = $w2dc_instance->locations_manager->getLocationIconFile($term_id))
		return $icon_file;
}

function w2dc_show_404() {
	status_header(404);
	nocache_headers();
	include(get_404_template());
	exit;
}

function w2dc_login_form($args = array()) {
	$defaults = array(
			'redirect' => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // Default redirect is back to the current page
			'form_id' => 'loginform',
			'label_username' => __( 'Username' ),
			'label_password' => __( 'Password' ),
			'label_remember' => __( 'Remember Me' ),
			'label_log_in' => __( 'Log In' ),
			'id_username' => 'user_login',
			'id_password' => 'user_pass',
			'id_remember' => 'rememberme',
			'id_submit' => 'wp-submit',
			'remember' => true,
			'value_username' => '',
			'value_remember' => false, // Set this to true to default the "Remember me" checkbox to checked
	);
	$args = wp_parse_args($args, apply_filters( 'login_form_defaults', $defaults));
	
	echo '<div class="w2dc-content">';
	
	echo '
		<form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" action="' . esc_url( site_url( 'wp-login.php', 'login_post' ) ) . '" method="post" class="w2dc_login_form" role="form">
			' . apply_filters( 'login_form_top', '', $args ) . '
			<p class="w2dc-form-group">
				<label for="' . esc_attr( $args['id_username'] ) . '">' . esc_html( $args['label_username'] ) . '</label>
				<input type="text" name="log" id="' . esc_attr( $args['id_username'] ) . '" class="w2dc-form-control" value="' . esc_attr( $args['value_username'] ) . '" />
			</p>
			<p class="login-password">
				<label for="' . esc_attr( $args['id_password'] ) . '">' . esc_html( $args['label_password'] ) . '</label>
				<input type="password" name="pwd" id="' . esc_attr( $args['id_password'] ) . '" class="w2dc-form-control" value="" />
			</p>
			' . apply_filters( 'login_form_middle', '', $args ) . '
			' . ( $args['remember'] ? '<p class="checkbox"><label><input name="rememberme" type="checkbox" id="' . esc_attr( $args['id_remember'] ) . '" value="forever"' . ( $args['value_remember'] ? ' checked="checked"' : '' ) . ' /> ' . esc_html( $args['label_remember'] ) . '</label></p>' : '' ) . '
			<p class="login-submit">
				<input type="submit" name="wp-submit" id="' . esc_attr( $args['id_submit'] ) . '" class="w2dc-btn w2dc-btn-primary" value="' . esc_attr( $args['label_log_in'] ) . '" />
				<input type="hidden" name="redirect_to" value="' . esc_url( $args['redirect'] ) . '" />
			</p>
			' . apply_filters( 'login_form_bottom', '', $args ) . '
		</form>';

	do_action('login_form');
	do_action('login_footer');
	echo '<p id="nav">';
	if (get_option('users_can_register'))
		echo '<a href="' . esc_url( wp_registration_url() ) . '" rel="nofollow">' . __('Register', 'W2DC') . '</a> | ';

	echo '<a title="' . esc_attr__('Password Lost and Found', 'W2DC') . '" href="' . esc_url( wp_lostpassword_url() ) . '">' . __('Lost your password?', 'W2DC') . '</a>';
	echo '</p>';

	echo '</div>';
}


if (!function_exists('w2dc_renderPaginator')) {
	function w2dc_renderPaginator($query, $hash = null, $show_more_button = false) {
		global $w2dc_instance;

		if (get_class($query) == 'WP_Query') {
			if (get_query_var('page'))
				$paged = get_query_var('page');
			elseif (get_query_var('paged'))
				$paged = get_query_var('paged');
			else
				$paged = 1;

			$total_pages = $query->max_num_pages;
			$total_lines = ceil($total_pages/10);
		
			if ($total_pages > 1){
				$current_page = max(1, $paged);
				$current_line = floor(($current_page-1)/10) + 1;
		
				$previous_page = $current_page - 1;
				$next_page = $current_page + 1;
				$previous_line_page = floor(($current_page-1)/10)*10;
				$next_line_page = ceil($current_page/10)*10 + 1;
				
				if (!$show_more_button) {
					echo '<div class="w2dc-pagination-wrapper">';
					echo '<ul class="w2dc-pagination">';
					if ($total_pages > 10 && $current_page > 10)
						echo '<li class="w2dc-inactive previous_line"><a href="' . get_pagenum_link($previous_line_page) . '" title="' . esc_attr__('Previous Line', 'W2DC') . '" data-page=' . $previous_line_page . ' data-controller-hash=' . $hash . '><<</a></li>' ;
			
					if ($total_pages > 3 && $current_page > 1)
						echo '<li class="w2dc-inactive previous"><a href="' . get_pagenum_link($previous_page) . '" title="' . esc_attr__('Previous Page', 'W2DC') . '" data-page=' . $previous_page . ' data-controller-hash=' . $hash . '><</i></a></li>' ;
			
					$count = ($current_line-1)*10;
					$end = ($total_pages < $current_line*10) ? $total_pages : $current_line*10;
					while ($count < $end) {
						$count = $count + 1;
						if ($count == $current_page)
							echo '<li class="w2dc-active"><a href="' . get_pagenum_link($count) . '">' . $count . '</a></li>' ;
						else
							echo '<li class="w2dc-inactive"><a href="' . get_pagenum_link($count) . '" data-page=' . $count . ' data-controller-hash=' . $hash . '>' . $count . '</a></li>' ;
					}
			
					if ($total_pages > 3 && $current_page < $total_pages)
						echo '<li class="w2dc-inactive next"><a href="' . get_pagenum_link($next_page) . '" title="' . esc_attr__('Next Page', 'W2DC') . '" data-page=' . $next_page . ' data-controller-hash=' . $hash . '>></i></a></li>' ;
			
					if ($total_pages > 10 && $current_line < $total_lines)
						echo '<li class="w2dc-inactive next_line"><a href="' . get_pagenum_link($next_line_page) . '" title="' . esc_attr__('Next Line', 'W2DC') . '" data-page=' . $next_line_page . ' data-controller-hash=' . $hash . '>>></a></li>' ;
			
					echo '</ul>';
					echo '</div>';
				} else {
					echo '<button class="w2dc-btn w2dc-btn-primary w2dc-btn-lg w2dc-btn-block w2dc-show-more-button" data-controller-hash="' . $hash . '">' . sprintf(__('Show more %s', 'W2DC'), $w2dc_instance->current_directory->plural) . '</button>';
				}
			}
		}
	}
}

function w2dc_renderSharingButton($post_id, $button) {
	global $w2dc_social_services;

	$post_title = urlencode(get_the_title($post_id));
	$thumb_url = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), array(200, 200));
	$post_thumbnail = urlencode($thumb_url[0]);
	if (get_post_type($post_id) == W2DC_POST_TYPE) {
		$listing = new w2dc_listing;
		if ($listing->loadListingFromPost($post_id))
			$post_title = urlencode($listing->title());
	}
	$post_url = urlencode(get_permalink($post_id));

	if (isset($w2dc_social_services[$button])) {
		$share_url = false;
		$share_counter = false;
		switch ($button) {
			case 'facebook':
				$share_url = 'http://www.facebook.com/sharer.php?u=' . $post_url;
				if (get_option('w2dc_share_counter')) {
					$response = wp_remote_get('http://graph.facebook.com/?id=' . $post_url);
					if (!is_wp_error($response)) {
						$body = wp_remote_retrieve_body($response);
						$json = json_decode($body);
						$share_counter = (isset($json->share->share_count)) ? intval($json->share->share_count) : 0;
					}
				}
			break;
			case 'twitter':
				$share_url = 'http://twitter.com/share?url=' . $post_url . '&amp;text=' . $post_title;
				/* if (get_option('w2dc_share_counter')) {
					$response = wp_remote_get('https://urls.api.twitter.com/1/urls/count.json?url=' . $post_url);
					if (!is_wp_error($response)) {
						$body = wp_remote_retrieve_body($response);
						$json = json_decode($body);
						$share_counter = (isset($json->count)) ? intval($json->count) : 0;
					}
				} */
			break;
			case 'google':
				$share_url = 'https://plus.google.com/share?url=' . $post_url;
				if (get_option('w2dc_share_counter')) {
					$args = array(
				            'method' => 'POST',
				            'headers' => array(
				                'Content-Type' => 'application/json'
				            ),
				            'body' => json_encode(array(
				                'method' => 'pos.plusones.get',
				                'id' => 'p',
				                'method' => 'pos.plusones.get',
				                'jsonrpc' => '2.0',
				                'key' => 'p',
				                'apiVersion' => 'v1',
				                'params' => array(
				                    'nolog' => true,
				                    'id' => $post_url,
				                    'source' => 'widget',
				                    'userId' => '@viewer',
				                    'groupId' => '@self'
				                ) 
				             )),          
				            'sslverify'=>false
				        ); 
				    $response = wp_remote_post("https://clients6.google.com/rpc", $args);
					if (!is_wp_error($response)) {
						$body = wp_remote_retrieve_body($response);
						$json = json_decode($body);
						$share_counter = (isset($json->result->metadata->globalCounts->count)) ? intval($json->result->metadata->globalCounts->count) : 0;
					}
				}
			break;
			case 'digg':
				$share_url = 'http://www.digg.com/submit?url=' . $post_url;
			break;
			case 'reddit':
				$share_url = 'http://reddit.com/submit?url=' . $post_url . '&amp;title=' . $post_title;
				if (get_option('w2dc_share_counter')) {
					$response = wp_remote_get('https://www.reddit.com/api/info.json?url=' . $post_url);
					if (!is_wp_error($response)) {
						$body = wp_remote_retrieve_body($response);
						$json = json_decode($body);
						$share_counter = (isset($json->data->children[0]->data->score)) ? intval($json->data->children[0]->data->score) : 0;
					}
				}
			break;
			case 'linkedin':
				$share_url = 'http://www.linkedin.com/shareArticle?mini=true&amp;url=' . $post_url;
				if (get_option('w2dc_share_counter')) {
					$response = wp_remote_get('https://www.linkedin.com/countserv/count/share?url=' . $post_url . '&format=json');
					if (!is_wp_error($response)) {
						$body = wp_remote_retrieve_body($response);
						$json = json_decode($body);
						$share_counter = (isset($json->count)) ? intval($json->count) : 0;
					}
				}
			break;
			case 'pinterest':
				$share_url = 'https://www.pinterest.com/pin/create/button/?url=' . $post_url . '&amp;media=' . $post_thumbnail . '&amp;description=' . $post_title;
				if (get_option('w2dc_share_counter')) {
					$response = wp_remote_get('https://api.pinterest.com/v1/urls/count.json?url=' . $post_url);
					if (!is_wp_error($response)) {
						$body = preg_replace('/^receiveCount\((.*)\)$/', "\\1", $response['body']);
						$json = json_decode($body);
						$share_counter = (isset($json->count)) ? intval($json->count) : 0;
					}
				}
			break;
			case 'stumbleupon':
				$share_url = 'http://www.stumbleupon.com/submit?url=' . $post_url . '&amp;title=' . $post_title;
				if (get_option('w2dc_share_counter')) {
					$response = wp_remote_get('https://www.stumbleupon.com/services/1.01/badge.getinfo?url=' . $post_url);
					if (!is_wp_error($response)) {
						$body = wp_remote_retrieve_body($response);
						$json = json_decode($body);
						$share_counter = (isset($json->result->views)) ? intval($json->result->views) : 0;
					}
				}
			break;
			case 'tumblr':
				$share_url = 'http://www.tumblr.com/share/link?url=' . str_replace('http://', '', str_replace('https://', '', $post_url)) . '&amp;name=' . $post_title;
			break;
			case 'vk':
				$share_url = 'http://vkontakte.ru/share.php?url=' . $post_url;
				if (get_option('w2dc_share_counter')) {
					$response = wp_remote_get('https://vkontakte.ru/share.php?act=count&index=1&url=' . $post_url);
					if (!is_wp_error($response)) {
						$tmp = array();
						preg_match('/^VK.Share.count\(1, (\d+)\);$/i', $response['body'], $tmp);
						$share_counter = (isset($tmp[1])) ? intval($tmp[1]) : 0;
					}
				}
			break;
			case 'email':
				$share_url = 'mailto:?Subject=' . $post_title . '&amp;Body=' . $post_url;
			break;
		}

		if ($share_url !== false) {
			echo '<a href="'.$share_url.'" data-toggle="w2dc-tooltip" title="'.sprintf(__('Share on %s', 'W2DC'),  $w2dc_social_services[$button]['label']).'" target="_blank"><img src="'.W2DC_RESOURCES_URL.'images/social/'.get_option('w2dc_share_buttons_style').'/'.$button.'.png" /></a>';
			if (get_option('w2dc_share_counter') && $share_counter !== false)
				echo '<span class="w2dc-share-count">'.number_format($share_counter).'</span>';
		}
	}
}

?>