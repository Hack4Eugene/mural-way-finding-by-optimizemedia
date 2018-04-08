<?php 

if (!function_exists('w2dc_getValue')) {
	function w2dc_getValue($target, $key, $default = false) {
		$target = is_object($target) ? (array) $target : $target;
	
		if (is_array($target) && isset($target[$key]))
			return $target[$key];
	
		return $default;
	}
}

if (!function_exists('w2dc_addMessage')) {
	function w2dc_addMessage($message, $type = 'updated') {
		global $w2dc_messages;
		
		if (is_array($message)) {
			foreach ($message AS $m) {
				w2dc_addMessage($m, $type);
			}
			return ;
		}
	
		if (!isset($w2dc_messages[$type]) || (isset($w2dc_messages[$type]) && !in_array($message, $w2dc_messages[$type])))
			$w2dc_messages[$type][] = $message;
	
		if (session_id() == '')
			@session_start();
	
		if (!isset($_SESSION['w2dc_messages'][$type]) || (isset($_SESSION['w2dc_messages'][$type]) && !in_array($message, $_SESSION['w2dc_messages'][$type])))
			$_SESSION['w2dc_messages'][$type][] = $message;
	}
}

if (!function_exists('w2dc_renderMessages')) {
	function w2dc_renderMessages() {
		global $w2dc_messages;
	
		$messages = array();
		if (isset($w2dc_messages) && is_array($w2dc_messages) && $w2dc_messages)
			$messages = $w2dc_messages;
	
		if (session_id() == '')
			@session_start();
		if (isset($_SESSION['w2dc_messages']))
			$messages = array_merge($messages, $_SESSION['w2dc_messages']);
	
		$messages = w2dc_superUnique($messages);
	
		foreach ($messages AS $type=>$messages) {
			$message_class = (is_admin()) ? $type : "w2dc-" . $type;

			echo '<div class="' . $message_class . '">';
			foreach ($messages AS $message) {
				echo '<p>' . trim(preg_replace("/<p>(.*?)<\/p>/", "$1", $message)) . '</p>';
			}
			echo '</div>';
		}
		
		$w2dc_messages = array();
		unset($_SESSION['w2dc_messages']);
	}
	function w2dc_superUnique($array) {
		$result = array_map("unserialize", array_unique(array_map("serialize", $array)));
		foreach ($result as $key => $value)
			if (is_array($value))
				$result[$key] = w2dc_superUnique($value);
		return $result;
	}
}

function w2dc_sumDates($date, $active_days, $active_months, $active_years)
{
	$date = strtotime('+'.$active_days.' day', $date);
	$date = strtotime('+'.$active_months.' month', $date);
	$date = strtotime('+'.$active_years.' year', $date);
	return $date;
}

if (!function_exists('w2dc_renderTemplate')) {
	function w2dc_renderTemplate($template, $args = array(), $return = false) {
		global $w2dc_instance;
	
		if ($args)
			extract($args);
		
		if (is_array($template)) {
			$plugin_template_path = $template[0];
			$template = $template[1];
		} else
			$plugin_template_path = W2DC_TEMPLATES_PATH;

		$core_template_path = $plugin_template_path . $template;

		if (!is_file($template))
			if (!is_file($core_template_path))
				return false;
			else
				$template = $core_template_path;

		$custom_template = str_replace('.tpl.php', '', $template) . '-custom.tpl.php';
		if (is_file($custom_template))
			$template = $custom_template;
	
		if ($return)
			ob_start();
	
		include($template);
		
		if ($return) {
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
	}
}

function w2dc_getCurrentListingInAdmin() {
	global $w2dc_instance;
	
	return $w2dc_instance->current_listing;
}

function w2dc_getIndexPage() {
	global $wp, $wpdb, $wp_rewrite, $w2dc_instance;
	
	$curr_index_page = array('slug' => '', 'id' => 0, 'url' => '');

	$flat_index_pages = $wpdb->get_results("SELECT ID AS id, post_name AS slug FROM {$wpdb->posts} WHERE (post_content LIKE '%[" . W2DC_MAIN_SHORTCODE . "]%') AND post_status = 'publish' AND post_type = 'page'", ARRAY_A);
	$custom_index_pages = $wpdb->get_results("SELECT ID AS id, post_name AS slug FROM {$wpdb->posts} WHERE (post_content LIKE '%[" . W2DC_MAIN_SHORTCODE . " %') AND post_status = 'publish' AND post_type = 'page'", ARRAY_A);
	$index_pages = array_merge($flat_index_pages, $custom_index_pages);

	// adapted for WPML
	global $sitepress;
	if (function_exists('wpml_object_id_filter') && $sitepress) {
		foreach ($index_pages AS $key=>&$cpage) {
			if ($tpage = apply_filters('wpml_object_id', $cpage['id'], 'page')) {
				$cpage['id'] = $tpage;
				$cpage['slug'] = get_post($cpage['id'])->post_name;
			} else {
				unset($index_pages[$key]);
			}
		}
	}

	$w2dc_instance->index_pages_all = array_unique($index_pages, SORT_REGULAR);

	if (!$index_pages)
		return $curr_index_page;

	if (get_queried_object_id()) {
		foreach ($index_pages AS $page) {
			if ($page['id'] == get_queried_object_id()) {
				$curr_index_page = $page;
				break;
			}
		}
	}
	if (!$curr_index_page['id']) {
		if ($wp_rewrite->using_permalinks()) {
			if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['base_url'])) {
				$base_url = $_REQUEST['base_url'];
			} else {
				$base_url = home_url($wp->request);
			}
			$urls_length = array();
			foreach ($index_pages AS $page) {
				$page_url = get_permalink($page['id']);
				if (strpos($base_url, $page_url) !== FALSE)
					$urls_length[$page['id']] = strlen($page_url);
			}
			if ($urls_length) {
				asort($urls_length);
				$urls_length = array_keys($urls_length);
				$page_id = array_pop($urls_length);
				foreach ($index_pages AS $page) {
					if ($page['id'] == $page_id) {
						$curr_index_page = $page;
						break;
					}
				}
			}
		} else {
			$homepage = null;
			if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['base_url'])) {
				if (($base_url = wp_parse_args($_REQUEST['base_url'])) && isset($base_url['homepage']))
					$homepage = $base_url['homepage'];
			} else {
				$homepage = get_query_var('homepage');
			}
			foreach ($index_pages AS $page) {
				if ($page['id'] == $homepage) {
					$curr_index_page = $page;
					break;
				}
			}
		}
	}

	if (!$curr_index_page['id']) {
		$curr_index_page = $index_pages[0];
	}

	if ($curr_index_page['id']) {
		if ($wp_rewrite->using_permalinks())
			$curr_index_page['url'] = get_permalink($curr_index_page['id']);
		else
			// found that on some instances of WP "native" trailing slashes may be missing
			$curr_index_page['url'] = add_query_arg('page_id', $curr_index_page['id'], home_url('/'));
	}

	return $curr_index_page;
}

function w2dc_getTemplatePage($shortcode) {
	global $wpdb, $wp_rewrite;

	if (!($template_page = $wpdb->get_row("SELECT ID AS id, post_name AS slug FROM {$wpdb->posts} WHERE post_content LIKE '%[" . $shortcode . "]%' AND post_status = 'publish' AND post_type = 'page' LIMIT 1", ARRAY_A)))
		$template_page = array('slug' => '', 'id' => 0, 'url' => '');
	
	// adapted for WPML
	global $sitepress;
	if (function_exists('wpml_object_id_filter') && $sitepress) {
		if ($tpage = apply_filters('wpml_object_id', $template_page['id'], 'page')) {
			$template_page['id'] = $tpage;
			$template_page['slug'] = get_post($template_page['id'])->post_name;
		} else 
			$template_page = array('slug' => '', 'id' => 0, 'url' => '');
	}

	if ($template_page['id']) {
		if ($wp_rewrite->using_permalinks())
			$template_page['url'] = get_permalink($template_page['id']);
		else
			// found that on some instances of WP "native" trailing slashes may be missing
			$template_page['url'] = add_query_arg('page_id', $template_page['id'], home_url('/'));
	}

	return $template_page;
}

function w2dc_directoryUrl($path = '', $directory = null) {
	global $w2dc_instance;
	
	if (empty($directory)) {
		$directory_page_url = $w2dc_instance->index_page_url;
	} else {
		$directory_page_url = $directory->url;
	}
	
	// adapted for WPML
	global $sitepress;
	if (function_exists('wpml_object_id_filter') && $sitepress) {
		if ($sitepress->get_option('language_negotiation_type') == 3) {
			// remove any previous value.
			$directory_page_url = remove_query_arg('lang', $directory_page_url);
		}
	}

	if (!is_array($path)) {
		if ($path)
			$path = rtrim($path, '/') . '/';
		// found that on some instances of WP "native" trailing slashes may be missing
		$url = rtrim($directory_page_url, '/') . '/' . $path;
	} else
		$url = add_query_arg($path, $directory_page_url);

	// adapted for WPML
	global $sitepress;
	if (function_exists('wpml_object_id_filter') && $sitepress) {
		$url = $sitepress->convert_url($url);
	}
	
	$url = w2dc_add_homepage_id($url);
	
	return utf8_uri_encode($url);
}

function w2dc_templatePageUri($slug_array, $page_url) {
	global $w2dc_instance;
	
	if ($page_url)
		$page_url = $page_url;
	else
		$page_url = $w2dc_instance->index_page_url;
	
	// adapted for WPML
	global $sitepress;
	if (function_exists('wpml_object_id_filter') && $sitepress) {
		if ($sitepress->get_option('language_negotiation_type') == 3) {
			// remove any previous value.
			$page_url = remove_query_arg('lang', $page_url);
		}
	}

	$template_url = add_query_arg($slug_array, $page_url);

	// adapted for WPML
	global $sitepress;
	if (function_exists('wpml_object_id_filter') && $sitepress) {
		$template_url = $sitepress->convert_url($template_url);
	}
	
	$template_url = w2dc_add_homepage_id($template_url);

	return utf8_uri_encode($template_url);
}

function w2dc_add_homepage_id($url) {
	global $w2dc_instance, $wp_rewrite;
	
	$homepage = null;
	if (defined('DOING_AJAX') && DOING_AJAX && isset($base_url['homepage'])) {
		if ($base_url = wp_parse_args($_REQUEST['base_url']))
			$homepage = $base_url['homepage'];
	} else {
		$homepage = get_queried_object_id();
	}
	if (!$wp_rewrite->using_permalinks() && $homepage && count($w2dc_instance->index_pages_all) > 1) {
		foreach ($w2dc_instance->index_pages_all AS $page) {
			if ($page['id'] == $homepage) {
				$url = add_query_arg('homepage', $homepage, $url);
				break;
			}
		}
	}
	return $url;
}

function w2dc_get_term_parents($id, $tax, $link = false, $return_array = false, $separator = '/', &$chain = array()) {
	$parent = get_term($id, $tax);
	if (is_wp_error($parent) || !$parent)
		if ($return_array)
			return array();
		else 
			return '';

	$name = $parent->name;
	
	if ($parent->parent && ($parent->parent != $parent->term_id))
		w2dc_get_term_parents($parent->parent, $tax, $link, $return_array, $separator, $chain);

	if ($link)
		$chain[] = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="' . get_term_link($parent->slug, $tax) . '" title="' . esc_attr(sprintf(__('View all listings in %s', 'W2DC'), $parent->name)) . '"><span itemprop="name">' . $name . '</span></a></li>';
	else
		$chain[] = $name;
	
	if ($return_array)
		return $chain;
	else
		return implode($separator, $chain);
}

function w2dc_get_term_parents_slugs($id, $tax, &$chain = array()) {
	$parent = get_term($id, $tax);
	if (is_wp_error($parent) || !$parent)
		return '';

	$slug = $parent->slug;
	
	if ($parent->parent && ($parent->parent != $parent->term_id))
		w2dc_get_term_parents_slugs($parent->parent, $tax, $chain);

	$chain[] = $slug;

	return $chain;
}

function w2dc_get_term_parents_ids($id, $tax, &$chain = array()) {
	$parent = get_term($id, $tax);
	if (is_wp_error($parent) || !$parent)
		return '';

	$id = $parent->term_id;
	
	if ($parent->parent && ($parent->parent != $parent->term_id))
		w2dc_get_term_parents_ids($parent->parent, $tax, $chain);

	$chain[] = $id;

	return $chain;
}

function w2dc_checkQuickList($is_listing_id = null)
{
	if (isset($_COOKIE['favourites']))
		$favourites = explode('*', $_COOKIE['favourites']);
	else
		$favourites = array();
	$favourites = array_values(array_filter($favourites));

	if ($is_listing_id)
		if (in_array($is_listing_id, $favourites))
			return true;
		else 
			return false;

	$favourites_array = array();
	foreach ($favourites AS $listing_id)
		if (is_numeric($listing_id))
		$favourites_array[] = $listing_id;
	return $favourites_array;
}

function w2dc_getDatePickerFormat() {
	$wp_date_format = get_option('date_format');
	return str_replace(
			array('S',  'd', 'j',  'l',  'm', 'n',  'F',  'Y'),
			array('',  'dd', 'd', 'DD', 'mm', 'm', 'MM', 'yy'),
		$wp_date_format);
}

function w2dc_getDatePickerLangFile($locale) {
	if ($locale) {
		$_locale = explode('-', str_replace('_', '-', $locale));
		$lang_code = array_shift($_locale);
		if (is_file(W2DC_RESOURCES_PATH . 'js/i18n/datepicker-'.$locale.'.js'))
			return W2DC_RESOURCES_URL . 'js/i18n/datepicker-'.$locale.'.js';
		elseif (is_file(W2DC_RESOURCES_PATH . 'js/i18n/datepicker-'.$lang_code.'.js'))
			return W2DC_RESOURCES_URL . 'js/i18n/datepicker-'.$lang_code.'.js';
	}
}

function w2dc_getDatePickerLangCode($locale) {
	if ($locale) {
		$_locale = explode('-', str_replace('_', '-', $locale));
		$lang_code = array_shift($_locale);
		if (is_file(W2DC_RESOURCES_PATH . 'js/i18n/datepicker-'.$locale.'.js'))
			return $locale;
		elseif (is_file(W2DC_RESOURCES_PATH . 'js/i18n/datepicker-'.$lang_code.'.js'))
			return $lang_code;
	}
}

function w2dc_generateRandomVal($val = null) {
	if (!$val)
		return rand(1, 10000);
	else
		return $val;
}

/**
 * Fetch the IP Address
 *
 * @return	string
 */
function w2dc_ip_address()
{
	if (isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_CLIENT_IP']))
		$ip_address = $_SERVER['HTTP_CLIENT_IP'];
	elseif (isset($_SERVER['REMOTE_ADDR']))
		$ip_address = $_SERVER['REMOTE_ADDR'];
	elseif (isset($_SERVER['HTTP_CLIENT_IP']))
		$ip_address = $_SERVER['HTTP_CLIENT_IP'];
	elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else
		return false;

	if (strstr($ip_address, ',')) {
		$x = explode(',', $ip_address);
		$ip_address = trim(end($x));
	}

	$validation = new w2dc_form_validation();
	if (!$validation->valid_ip($ip_address))
		return false;

	return $ip_address;
}

function w2dc_crop_content($limit = 35, $strip_html = true, $has_link = true, $nofollow = false) {
	if (has_excerpt())
		$raw_content = apply_filters('the_excerpt', get_the_excerpt());
	elseif (get_option('w2dc_cropped_content_as_excerpt') && get_post()->post_content !== '')
		$raw_content = apply_filters('the_content', get_the_content());
	else 
		return ;

	$raw_content = str_replace(']]>', ']]&gt;', $raw_content);
	if ($strip_html) {
		$raw_content = strip_tags($raw_content);
		$pattern = get_shortcode_regex();
		// Remove shortcodes from excerpt
		$raw_content = preg_replace_callback("/$pattern/s", 'w2dc_remove_shortcodes', $raw_content);
	}

	if (!$limit)
		return $raw_content;
	
	if ($has_link)
		$readmore = ' <a href="'.get_permalink(get_the_ID()).'" '.(($nofollow) ? 'rel="nofollow"' : '').'>'.__('&#91;...&#93;', 'W2DC').'</a>';
	else
		$readmore = ' '.__('&#91;...&#93;', 'W2DC');

	$content = explode(' ', $raw_content, $limit);
	if (count($content) >= $limit) {
		array_pop($content);
		$content = implode(" ", $content) . $readmore;
	} else
		$content = $raw_content;

	return $content;
}

// Remove shortcodes from excerpt
function w2dc_remove_shortcodes($m) {
	if (function_exists('su_cmpt') && su_cmpt() !== false)
	if ($m[2] == su_cmpt() . 'dropcap' || $m[2] == su_cmpt() . 'highlight' || $m[2] == su_cmpt() . 'tooltip')
		return $m[0];

	// allow [[foo]] syntax for escaping a tag
	if ($m[1] == '[' && $m[6] == ']')
		return substr($m[0], 1, -1);

	return $m[1] . $m[6];
}

function w2dc_is_anyone_in_taxonomy($tax) {
	//global $wpdb;
	//return $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->term_taxonomy . ' WHERE `taxonomy`="' . $tax . '"');
	
	return count(get_categories(array('taxonomy' => $tax, 'hide_empty' => false, 'parent' => 0, 'number' => 1)));
}

function w2dc_comments_open() {
	if (get_option('w2dc_listings_comments_mode') == 'enabled' || (get_option('w2dc_listings_comments_mode') == 'wp_settings' && comments_open()))
		return true;
	else 
		return false;
}

function w2dc_get_term_by_path($term_path, $full_match = true, $output = OBJECT) {
	$term_path = rawurlencode( urldecode( $term_path ) );
	$term_path = str_replace( '%2F', '/', $term_path );
	$term_path = str_replace( '%20', ' ', $term_path );

	global $wp_rewrite;
	if ($wp_rewrite->using_permalinks()) {
		$term_paths = '/' . trim( $term_path, '/' );
		$leaf_path  = sanitize_title( basename( $term_paths ) );
		$term_paths = explode( '/', $term_paths );
		$full_path = '';
		foreach ( (array) $term_paths as $pathdir )
			$full_path .= ( $pathdir != '' ? '/' : '' ) . sanitize_title( $pathdir );
	
		//$terms = get_terms( array(W2DC_CATEGORIES_TAX, W2DC_LOCATIONS_TAX, W2DC_TAGS_TAX), array('get' => 'all', 'slug' => $leaf_path) );
		$terms = array();
		if ($term = get_term_by('slug', $leaf_path, W2DC_CATEGORIES_TAX))
			$terms[] = $term;
		if ($term = get_term_by('slug', $leaf_path, W2DC_LOCATIONS_TAX))
			$terms[] = $term;
		if ($term = get_term_by('slug', $leaf_path, W2DC_TAGS_TAX))
			$terms[] = $term;
	
		if ( empty( $terms ) )
			return null;
	
		foreach ( $terms as $term ) {
			$path = '/' . $leaf_path;
			$curterm = $term;
			while ( ( $curterm->parent != 0 ) && ( $curterm->parent != $curterm->term_id ) ) {
				$curterm = get_term( $curterm->parent, $term->taxonomy );
				if ( is_wp_error( $curterm ) )
					return $curterm;
				$path = '/' . $curterm->slug . $path;
			}

			if ( $path == $full_path ) {
				$term = get_term( $term->term_id, $term->taxonomy, $output );
				_make_cat_compat( $term );
				return $term;
			}
		}
	
		// If full matching is not required, return the first cat that matches the leaf.
		if ( ! $full_match ) {
			$term = reset( $terms );
			$term = get_term( $term->term_id, $term->taxonomy, $output );
			_make_cat_compat( $term );
			return $term;
		}
	} else {
		if ($term = get_term_by('slug', $term_path, W2DC_CATEGORIES_TAX))
			return $term;
		if ($term = get_term_by('slug', $term_path, W2DC_LOCATIONS_TAX))
			return $term;
		if ($term = get_term_by('slug', $term_path, W2DC_TAGS_TAX))
			return $term;
	}

	return null;
}

function w2dc_get_fa_icons_names() {
	$icons[] = 'w2dc-fa-adjust';
	$icons[] = 'w2dc-fa-adn';
	$icons[] = 'w2dc-fa-align-center';
	$icons[] = 'w2dc-fa-align-justify';
	$icons[] = 'w2dc-fa-align-left';
	$icons[] = 'w2dc-fa-align-right';
	$icons[] = 'w2dc-fa-ambulance';
	$icons[] = 'w2dc-fa-anchor';
	$icons[] = 'w2dc-fa-android';
	$icons[] = 'w2dc-fa-angellist';
	$icons[] = 'w2dc-fa-angle-double-down';
	$icons[] = 'w2dc-fa-angle-double-left';
	$icons[] = 'w2dc-fa-angle-double-right';
	$icons[] = 'w2dc-fa-angle-double-up';
	$icons[] = 'w2dc-fa-angle-down';
	$icons[] = 'w2dc-fa-angle-left';
	$icons[] = 'w2dc-fa-angle-right';
	$icons[] = 'w2dc-fa-angle-up';
	$icons[] = 'w2dc-fa-apple';
	$icons[] = 'w2dc-fa-archive';
	$icons[] = 'w2dc-fa-area-chart';
	$icons[] = 'w2dc-fa-arrow-circle-down';
	$icons[] = 'w2dc-fa-arrow-circle-left';
	$icons[] = 'w2dc-fa-arrow-circle-o-down';
	$icons[] = 'w2dc-fa-arrow-circle-o-left';
	$icons[] = 'w2dc-fa-arrow-circle-o-right';
	$icons[] = 'w2dc-fa-arrow-circle-o-up';
	$icons[] = 'w2dc-fa-arrow-circle-right';
	$icons[] = 'w2dc-fa-arrow-circle-up';
	$icons[] = 'w2dc-fa-arrow-down';
	$icons[] = 'w2dc-fa-arrow-left';
	$icons[] = 'w2dc-fa-arrow-right';
	$icons[] = 'w2dc-fa-arrow-up';
	$icons[] = 'w2dc-fa-arrows';
	$icons[] = 'w2dc-fa-arrows-alt';
	$icons[] = 'w2dc-fa-arrows-h';
	$icons[] = 'w2dc-fa-arrows-v';
	$icons[] = 'w2dc-fa-asterisk';
	$icons[] = 'w2dc-fa-at';
	$icons[] = 'w2dc-fa-automobile';
	$icons[] = 'w2dc-fa-backward';
	$icons[] = 'w2dc-fa-ban';
	$icons[] = 'w2dc-fa-bank';
	$icons[] = 'w2dc-fa-bar-chart';
	$icons[] = 'w2dc-fa-bar-chart-o';
	$icons[] = 'w2dc-fa-barcode';
	$icons[] = 'w2dc-fa-bars';
	$icons[] = 'w2dc-fa-bed';
	$icons[] = 'w2dc-fa-beer';
	$icons[] = 'w2dc-fa-behance';
	$icons[] = 'w2dc-fa-behance-square';
	$icons[] = 'w2dc-fa-bell';
	$icons[] = 'w2dc-fa-bell-o';
	$icons[] = 'w2dc-fa-bell-slash';
	$icons[] = 'w2dc-fa-bell-slash-o';
	$icons[] = 'w2dc-fa-bicycle';
	$icons[] = 'w2dc-fa-binoculars';
	$icons[] = 'w2dc-fa-birthday-cake';
	$icons[] = 'w2dc-fa-bitbucket';
	$icons[] = 'w2dc-fa-bitbucket-square';
	$icons[] = 'w2dc-fa-bitcoin';
	$icons[] = 'w2dc-fa-bold';
	$icons[] = 'w2dc-fa-bolt';
	$icons[] = 'w2dc-fa-bomb';
	$icons[] = 'w2dc-fa-book';
	$icons[] = 'w2dc-fa-bookmark';
	$icons[] = 'w2dc-fa-bookmark-o';
	$icons[] = 'w2dc-fa-briefcase';
	$icons[] = 'w2dc-fa-btc';
	$icons[] = 'w2dc-fa-bug';
	$icons[] = 'w2dc-fa-building';
	$icons[] = 'w2dc-fa-building-o';
	$icons[] = 'w2dc-fa-bullhorn';
	$icons[] = 'w2dc-fa-bullseye';
	$icons[] = 'w2dc-fa-bus';
	$icons[] = 'w2dc-fa-buysellads';
	$icons[] = 'w2dc-fa-cab';
	$icons[] = 'w2dc-fa-calculator';
	$icons[] = 'w2dc-fa-calendar';
	$icons[] = 'w2dc-fa-calendar-o';
	$icons[] = 'w2dc-fa-camera';
	$icons[] = 'w2dc-fa-camera-retro';
	$icons[] = 'w2dc-fa-car';
	$icons[] = 'w2dc-fa-caret-down';
	$icons[] = 'w2dc-fa-caret-left';
	$icons[] = 'w2dc-fa-caret-right';
	$icons[] = 'w2dc-fa-caret-square-o-down';
	$icons[] = 'w2dc-fa-caret-square-o-left';
	$icons[] = 'w2dc-fa-caret-square-o-right';
	$icons[] = 'w2dc-fa-caret-square-o-up';
	$icons[] = 'w2dc-fa-caret-up';
	$icons[] = 'w2dc-fa-cart-arrow-down';
	$icons[] = 'w2dc-fa-cart-plus';
	$icons[] = 'w2dc-fa-cc';
	$icons[] = 'w2dc-fa-cc-amex';
	$icons[] = 'w2dc-fa-cc-discover';
	$icons[] = 'w2dc-fa-cc-mastercard';
	$icons[] = 'w2dc-fa-cc-paypal';
	$icons[] = 'w2dc-fa-cc-stripe';
	$icons[] = 'w2dc-fa-cc-visa';
	$icons[] = 'w2dc-fa-certificate';
	$icons[] = 'w2dc-fa-chain';
	$icons[] = 'w2dc-fa-chain-broken';
	$icons[] = 'w2dc-fa-check';
	$icons[] = 'w2dc-fa-check-circle';
	$icons[] = 'w2dc-fa-check-circle-o';
	$icons[] = 'w2dc-fa-check-square';
	$icons[] = 'w2dc-fa-check-square-o';
	$icons[] = 'w2dc-fa-chevron-circle-down';
	$icons[] = 'w2dc-fa-chevron-circle-left';
	$icons[] = 'w2dc-fa-chevron-circle-right';
	$icons[] = 'w2dc-fa-chevron-circle-up';
	$icons[] = 'w2dc-fa-chevron-down';
	$icons[] = 'w2dc-fa-chevron-left';
	$icons[] = 'w2dc-fa-chevron-right';
	$icons[] = 'w2dc-fa-chevron-up';
	$icons[] = 'w2dc-fa-child';
	$icons[] = 'w2dc-fa-circle';
	$icons[] = 'w2dc-fa-circle-o';
	$icons[] = 'w2dc-fa-circle-o-notch';
	$icons[] = 'w2dc-fa-circle-thin';
	$icons[] = 'w2dc-fa-clipboard';
	$icons[] = 'w2dc-fa-clock-o';
	$icons[] = 'w2dc-fa-close';
	$icons[] = 'w2dc-fa-cloud';
	$icons[] = 'w2dc-fa-cloud-download';
	$icons[] = 'w2dc-fa-cloud-upload';
	$icons[] = 'w2dc-fa-cny';
	$icons[] = 'w2dc-fa-code';
	$icons[] = 'w2dc-fa-code-fork';
	$icons[] = 'w2dc-fa-codepen';
	$icons[] = 'w2dc-fa-coffee';
	$icons[] = 'w2dc-fa-cog';
	$icons[] = 'w2dc-fa-cogs';
	$icons[] = 'w2dc-fa-columns';
	$icons[] = 'w2dc-fa-comment';
	$icons[] = 'w2dc-fa-comment-o';
	$icons[] = 'w2dc-fa-comments';
	$icons[] = 'w2dc-fa-comments-o';
	$icons[] = 'w2dc-fa-compass';
	$icons[] = 'w2dc-fa-compress';
	$icons[] = 'w2dc-fa-connectdevelop';
	$icons[] = 'w2dc-fa-copy';
	$icons[] = 'w2dc-fa-copyright';
	$icons[] = 'w2dc-fa-credit-card';
	$icons[] = 'w2dc-fa-crop';
	$icons[] = 'w2dc-fa-crosshairs';
	$icons[] = 'w2dc-fa-css3';
	$icons[] = 'w2dc-fa-cube';
	$icons[] = 'w2dc-fa-cubes';
	$icons[] = 'w2dc-fa-cut';
	$icons[] = 'w2dc-fa-cutlery';
	$icons[] = 'w2dc-fa-dashboard';
	$icons[] = 'w2dc-fa-dashcube';
	$icons[] = 'w2dc-fa-database';
	$icons[] = 'w2dc-fa-dedent';
	$icons[] = 'w2dc-fa-delicious';
	$icons[] = 'w2dc-fa-desktop';
	$icons[] = 'w2dc-fa-deviantart';
	$icons[] = 'w2dc-fa-diamond';
	$icons[] = 'w2dc-fa-digg';
	$icons[] = 'w2dc-fa-dollar';
	$icons[] = 'w2dc-fa-dot-circle-o';
	$icons[] = 'w2dc-fa-download';
	$icons[] = 'w2dc-fa-dribbble';
	$icons[] = 'w2dc-fa-dropbox';
	$icons[] = 'w2dc-fa-drupal';
	$icons[] = 'w2dc-fa-edit';
	$icons[] = 'w2dc-fa-eject';
	$icons[] = 'w2dc-fa-ellipsis-h';
	$icons[] = 'w2dc-fa-ellipsis-v';
	$icons[] = 'w2dc-fa-empire';
	$icons[] = 'w2dc-fa-envelope';
	$icons[] = 'w2dc-fa-envelope-o';
	$icons[] = 'w2dc-fa-envelope-square';
	$icons[] = 'w2dc-fa-eraser';
	$icons[] = 'w2dc-fa-eur';
	$icons[] = 'w2dc-fa-euro';
	$icons[] = 'w2dc-fa-exchange';
	$icons[] = 'w2dc-fa-exclamation';
	$icons[] = 'w2dc-fa-exclamation-circle';
	$icons[] = 'w2dc-fa-exclamation-triangle';
	$icons[] = 'w2dc-fa-expand';
	$icons[] = 'w2dc-fa-external-link';
	$icons[] = 'w2dc-fa-external-link-square';
	$icons[] = 'w2dc-fa-eye';
	$icons[] = 'w2dc-fa-eye-slash';
	$icons[] = 'w2dc-fa-eyedropper';
	$icons[] = 'w2dc-fa-facebook';
	$icons[] = 'w2dc-fa-facebook-f';
	$icons[] = 'w2dc-fa-facebook-official';
	$icons[] = 'w2dc-fa-facebook-square';
	$icons[] = 'w2dc-fa-fast-backward';
	$icons[] = 'w2dc-fa-fast-forward';
	$icons[] = 'w2dc-fa-fax';
	$icons[] = 'w2dc-fa-female';
	$icons[] = 'w2dc-fa-fighter-jet';
	$icons[] = 'w2dc-fa-file';
	$icons[] = 'w2dc-fa-file-archive-o';
	$icons[] = 'w2dc-fa-file-audio-o';
	$icons[] = 'w2dc-fa-file-code-o';
	$icons[] = 'w2dc-fa-file-excel-o';
	$icons[] = 'w2dc-fa-file-image-o';
	$icons[] = 'w2dc-fa-file-movie-o';
	$icons[] = 'w2dc-fa-file-o';
	$icons[] = 'w2dc-fa-file-pdf-o';
	$icons[] = 'w2dc-fa-file-photo-o';
	$icons[] = 'w2dc-fa-file-picture-o';
	$icons[] = 'w2dc-fa-file-powerpoint-o';
	$icons[] = 'w2dc-fa-file-sound-o';
	$icons[] = 'w2dc-fa-file-text';
	$icons[] = 'w2dc-fa-file-text-o';
	$icons[] = 'w2dc-fa-file-video-o';
	$icons[] = 'w2dc-fa-file-word-o';
	$icons[] = 'w2dc-fa-file-zip-o';
	$icons[] = 'w2dc-fa-files-o';
	$icons[] = 'w2dc-fa-film';
	$icons[] = 'w2dc-fa-filter';
	$icons[] = 'w2dc-fa-fire';
	$icons[] = 'w2dc-fa-fire-extinguisher';
	$icons[] = 'w2dc-fa-flag';
	$icons[] = 'w2dc-fa-flag-checkered';
	$icons[] = 'w2dc-fa-flag-o';
	$icons[] = 'w2dc-fa-flash';
	$icons[] = 'w2dc-fa-flask';
	$icons[] = 'w2dc-fa-flickr';
	$icons[] = 'w2dc-fa-floppy-o';
	$icons[] = 'w2dc-fa-folder';
	$icons[] = 'w2dc-fa-folder-o';
	$icons[] = 'w2dc-fa-folder-open';
	$icons[] = 'w2dc-fa-folder-open-o';
	$icons[] = 'w2dc-fa-font';
	$icons[] = 'w2dc-fa-forumbee';
	$icons[] = 'w2dc-fa-forward';
	$icons[] = 'w2dc-fa-foursquare';
	$icons[] = 'w2dc-fa-frown-o';
	$icons[] = 'w2dc-fa-futbol-o';
	$icons[] = 'w2dc-fa-gamepad';
	$icons[] = 'w2dc-fa-gavel';
	$icons[] = 'w2dc-fa-gbp';
	$icons[] = 'w2dc-fa-ge';
	$icons[] = 'w2dc-fa-gear';
	$icons[] = 'w2dc-fa-gears';
	$icons[] = 'w2dc-fa-genderless';
	$icons[] = 'w2dc-fa-gift';
	$icons[] = 'w2dc-fa-git';
	$icons[] = 'w2dc-fa-git-square';
	$icons[] = 'w2dc-fa-github';
	$icons[] = 'w2dc-fa-github-alt';
	$icons[] = 'w2dc-fa-github-square';
	$icons[] = 'w2dc-fa-gittip';
	$icons[] = 'w2dc-fa-glass';
	$icons[] = 'w2dc-fa-globe';
	$icons[] = 'w2dc-fa-google';
	$icons[] = 'w2dc-fa-google-plus';
	$icons[] = 'w2dc-fa-google-plus-square';
	$icons[] = 'w2dc-fa-google-wallet';
	$icons[] = 'w2dc-fa-graduation-cap';
	$icons[] = 'w2dc-fa-gratipay';
	$icons[] = 'w2dc-fa-group';
	$icons[] = 'w2dc-fa-h-square';
	$icons[] = 'w2dc-fa-hacker-news';
	$icons[] = 'w2dc-fa-hand-o-down';
	$icons[] = 'w2dc-fa-hand-o-left';
	$icons[] = 'w2dc-fa-hand-o-right';
	$icons[] = 'w2dc-fa-hand-o-up';
	$icons[] = 'w2dc-fa-hdd-o';
	$icons[] = 'w2dc-fa-header';
	$icons[] = 'w2dc-fa-headphones';
	$icons[] = 'w2dc-fa-heart';
	$icons[] = 'w2dc-fa-heart-o';
	$icons[] = 'w2dc-fa-heartbeat';
	$icons[] = 'w2dc-fa-history';
	$icons[] = 'w2dc-fa-home';
	$icons[] = 'w2dc-fa-hospital-o';
	$icons[] = 'w2dc-fa-hotel';
	$icons[] = 'w2dc-fa-html5';
	$icons[] = 'w2dc-fa-ils';
	$icons[] = 'w2dc-fa-image';
	$icons[] = 'w2dc-fa-inbox';
	$icons[] = 'w2dc-fa-indent';
	$icons[] = 'w2dc-fa-info';
	$icons[] = 'w2dc-fa-info-circle';
	$icons[] = 'w2dc-fa-inr';
	$icons[] = 'w2dc-fa-instagram';
	$icons[] = 'w2dc-fa-institution';
	$icons[] = 'w2dc-fa-ioxhost';
	$icons[] = 'w2dc-fa-italic';
	$icons[] = 'w2dc-fa-joomla';
	$icons[] = 'w2dc-fa-jpy';
	$icons[] = 'w2dc-fa-jsfiddle';
	$icons[] = 'w2dc-fa-key';
	$icons[] = 'w2dc-fa-keyboard-o';
	$icons[] = 'w2dc-fa-krw';
	$icons[] = 'w2dc-fa-language';
	$icons[] = 'w2dc-fa-laptop';
	$icons[] = 'w2dc-fa-lastfm';
	$icons[] = 'w2dc-fa-lastfm-square';
	$icons[] = 'w2dc-fa-leaf';
	$icons[] = 'w2dc-fa-leanpub';
	$icons[] = 'w2dc-fa-legal';
	$icons[] = 'w2dc-fa-lemon-o';
	$icons[] = 'w2dc-fa-level-down';
	$icons[] = 'w2dc-fa-level-up';
	$icons[] = 'w2dc-fa-life-bouy';
	$icons[] = 'w2dc-fa-life-ring';
	$icons[] = 'w2dc-fa-life-saver';
	$icons[] = 'w2dc-fa-lightbulb-o';
	$icons[] = 'w2dc-fa-line-chart';
	$icons[] = 'w2dc-fa-link';
	$icons[] = 'w2dc-fa-linkedin';
	$icons[] = 'w2dc-fa-linkedin-square';
	$icons[] = 'w2dc-fa-linux';
	$icons[] = 'w2dc-fa-list';
	$icons[] = 'w2dc-fa-list-alt';
	$icons[] = 'w2dc-fa-list-ol';
	$icons[] = 'w2dc-fa-list-ul';
	$icons[] = 'w2dc-fa-location-arrow';
	$icons[] = 'w2dc-fa-lock';
	$icons[] = 'w2dc-fa-long-arrow-down';
	$icons[] = 'w2dc-fa-long-arrow-left';
	$icons[] = 'w2dc-fa-long-arrow-right';
	$icons[] = 'w2dc-fa-long-arrow-up';
	$icons[] = 'w2dc-fa-magic';
	$icons[] = 'w2dc-fa-magnet';
	$icons[] = 'w2dc-fa-mail-forward';
	$icons[] = 'w2dc-fa-mail-reply';
	$icons[] = 'w2dc-fa-mail-reply-all';
	$icons[] = 'w2dc-fa-male';
	$icons[] = 'w2dc-fa-map-marker';
	$icons[] = 'w2dc-fa-mars';
	$icons[] = 'w2dc-fa-mars-double';
	$icons[] = 'w2dc-fa-mars-stroke';
	$icons[] = 'w2dc-fa-mars-stroke-h';
	$icons[] = 'w2dc-fa-mars-stroke-v';
	$icons[] = 'w2dc-fa-maxcdn';
	$icons[] = 'w2dc-fa-meanpath';
	$icons[] = 'w2dc-fa-medium';
	$icons[] = 'w2dc-fa-medkit';
	$icons[] = 'w2dc-fa-meh-o';
	$icons[] = 'w2dc-fa-mercury';
	$icons[] = 'w2dc-fa-microphone';
	$icons[] = 'w2dc-fa-microphone-slash';
	$icons[] = 'w2dc-fa-minus';
	$icons[] = 'w2dc-fa-minus-circle';
	$icons[] = 'w2dc-fa-minus-square';
	$icons[] = 'w2dc-fa-minus-square-o';
	$icons[] = 'w2dc-fa-mobile';
	$icons[] = 'w2dc-fa-mobile-phone';
	$icons[] = 'w2dc-fa-money';
	$icons[] = 'w2dc-fa-moon-o';
	$icons[] = 'w2dc-fa-mortar-board';
	$icons[] = 'w2dc-fa-motorcycle';
	$icons[] = 'w2dc-fa-music';
	$icons[] = 'w2dc-fa-navicon';
	$icons[] = 'w2dc-fa-neuter';
	$icons[] = 'w2dc-fa-newspaper-o';
	$icons[] = 'w2dc-fa-openid';
	$icons[] = 'w2dc-fa-outdent';
	$icons[] = 'w2dc-fa-pagelines';
	$icons[] = 'w2dc-fa-paint-brush';
	$icons[] = 'w2dc-fa-paper-plane';
	$icons[] = 'w2dc-fa-paper-plane-o';
	$icons[] = 'w2dc-fa-paperclip';
	$icons[] = 'w2dc-fa-paragraph';
	$icons[] = 'w2dc-fa-paste';
	$icons[] = 'w2dc-fa-pause';
	$icons[] = 'w2dc-fa-paw';
	$icons[] = 'w2dc-fa-paypal';
	$icons[] = 'w2dc-fa-pencil';
	$icons[] = 'w2dc-fa-pencil-square';
	$icons[] = 'w2dc-fa-pencil-square-o';
	$icons[] = 'w2dc-fa-phone';
	$icons[] = 'w2dc-fa-phone-square';
	$icons[] = 'w2dc-fa-photo';
	$icons[] = 'w2dc-fa-picture-o';
	$icons[] = 'w2dc-fa-pie-chart';
	$icons[] = 'w2dc-fa-pied-piper';
	$icons[] = 'w2dc-fa-pied-piper-alt';
	$icons[] = 'w2dc-fa-pinterest';
	$icons[] = 'w2dc-fa-pinterest-p';
	$icons[] = 'w2dc-fa-pinterest-square';
	$icons[] = 'w2dc-fa-plane';
	$icons[] = 'w2dc-fa-play';
	$icons[] = 'w2dc-fa-play-circle';
	$icons[] = 'w2dc-fa-play-circle-o';
	$icons[] = 'w2dc-fa-plug';
	$icons[] = 'w2dc-fa-plus';
	$icons[] = 'w2dc-fa-plus-circle';
	$icons[] = 'w2dc-fa-plus-square';
	$icons[] = 'w2dc-fa-plus-square-o';
	$icons[] = 'w2dc-fa-power-off';
	$icons[] = 'w2dc-fa-print';
	$icons[] = 'w2dc-fa-puzzle-piece';
	$icons[] = 'w2dc-fa-qq';
	$icons[] = 'w2dc-fa-qrcode';
	$icons[] = 'w2dc-fa-question';
	$icons[] = 'w2dc-fa-question-circle';
	$icons[] = 'w2dc-fa-quote-left';
	$icons[] = 'w2dc-fa-quote-right';
	$icons[] = 'w2dc-fa-ra';
	$icons[] = 'w2dc-fa-random';
	$icons[] = 'w2dc-fa-rebel';
	$icons[] = 'w2dc-fa-recycle';
	$icons[] = 'w2dc-fa-reddit';
	$icons[] = 'w2dc-fa-reddit-square';
	$icons[] = 'w2dc-fa-refresh';
	$icons[] = 'w2dc-fa-remove';
	$icons[] = 'w2dc-fa-renren';
	$icons[] = 'w2dc-fa-reorder';
	$icons[] = 'w2dc-fa-repeat';
	$icons[] = 'w2dc-fa-reply';
	$icons[] = 'w2dc-fa-reply-all';
	$icons[] = 'w2dc-fa-retweet';
	$icons[] = 'w2dc-fa-rmb';
	$icons[] = 'w2dc-fa-road';
	$icons[] = 'w2dc-fa-rocket';
	$icons[] = 'w2dc-fa-rotate-left';
	$icons[] = 'w2dc-fa-rotate-right';
	$icons[] = 'w2dc-fa-rouble';
	$icons[] = 'w2dc-fa-rss';
	$icons[] = 'w2dc-fa-rss-square';
	$icons[] = 'w2dc-fa-rub';
	$icons[] = 'w2dc-fa-ruble';
	$icons[] = 'w2dc-fa-rupee';
	$icons[] = 'w2dc-fa-save';
	$icons[] = 'w2dc-fa-scissors';
	$icons[] = 'w2dc-fa-search';
	$icons[] = 'w2dc-fa-search-minus';
	$icons[] = 'w2dc-fa-search-plus';
	$icons[] = 'w2dc-fa-sellsy';
	$icons[] = 'w2dc-fa-send';
	$icons[] = 'w2dc-fa-send-o';
	$icons[] = 'w2dc-fa-server';
	$icons[] = 'w2dc-fa-share';
	$icons[] = 'w2dc-fa-share-alt';
	$icons[] = 'w2dc-fa-share-alt-square';
	$icons[] = 'w2dc-fa-share-square';
	$icons[] = 'w2dc-fa-share-square-o';
	$icons[] = 'w2dc-fa-shekel';
	$icons[] = 'w2dc-fa-sheqel';
	$icons[] = 'w2dc-fa-shield';
	$icons[] = 'w2dc-fa-ship';
	$icons[] = 'w2dc-fa-shirtsinbulk';
	$icons[] = 'w2dc-fa-shopping-cart';
	$icons[] = 'w2dc-fa-sign-out';
	$icons[] = 'w2dc-fa-signal';
	$icons[] = 'w2dc-fa-simplybuilt';
	$icons[] = 'w2dc-fa-sitemap';
	$icons[] = 'w2dc-fa-skyatlas';
	$icons[] = 'w2dc-fa-skype';
	$icons[] = 'w2dc-fa-slack';
	$icons[] = 'w2dc-fa-sliders';
	$icons[] = 'w2dc-fa-slideshare';
	$icons[] = 'w2dc-fa-smile-o';
	$icons[] = 'w2dc-fa-soccer-ball-o';
	$icons[] = 'w2dc-fa-sort';
	$icons[] = 'w2dc-fa-sort-alpha-asc';
	$icons[] = 'w2dc-fa-sort-alpha-desc';
	$icons[] = 'w2dc-fa-sort-amount-asc';
	$icons[] = 'w2dc-fa-sort-amount-desc';
	$icons[] = 'w2dc-fa-sort-asc';
	$icons[] = 'w2dc-fa-sort-desc';
	$icons[] = 'w2dc-fa-sort-down';
	$icons[] = 'w2dc-fa-sort-numeric-asc';
	$icons[] = 'w2dc-fa-sort-numeric-desc';
	$icons[] = 'w2dc-fa-sort-up';
	$icons[] = 'w2dc-fa-soundcloud';
	$icons[] = 'w2dc-fa-space-shuttle';
	$icons[] = 'w2dc-fa-spinner';
	$icons[] = 'w2dc-fa-spoon';
	$icons[] = 'w2dc-fa-spotify';
	$icons[] = 'w2dc-fa-square';
	$icons[] = 'w2dc-fa-square-o';
	$icons[] = 'w2dc-fa-stack-exchange';
	$icons[] = 'w2dc-fa-stack-overflow';
	$icons[] = 'w2dc-fa-star';
	$icons[] = 'w2dc-fa-star-half';
	$icons[] = 'w2dc-fa-star-half-empty';
	$icons[] = 'w2dc-fa-star-half-full';
	$icons[] = 'w2dc-fa-star-half-o';
	$icons[] = 'w2dc-fa-star-o';
	$icons[] = 'w2dc-fa-steam';
	$icons[] = 'w2dc-fa-steam-square';
	$icons[] = 'w2dc-fa-step-backward';
	$icons[] = 'w2dc-fa-step-forward';
	$icons[] = 'w2dc-fa-stethoscope';
	$icons[] = 'w2dc-fa-stop';
	$icons[] = 'w2dc-fa-street-view';
	$icons[] = 'w2dc-fa-strikethrough';
	$icons[] = 'w2dc-fa-stumbleupon';
	$icons[] = 'w2dc-fa-stumbleupon-circle';
	$icons[] = 'w2dc-fa-subscript';
	$icons[] = 'w2dc-fa-subway';
	$icons[] = 'w2dc-fa-suitcase';
	$icons[] = 'w2dc-fa-sun-o';
	$icons[] = 'w2dc-fa-superscript';
	$icons[] = 'w2dc-fa-support';
	$icons[] = 'w2dc-fa-table';
	$icons[] = 'w2dc-fa-tablet';
	$icons[] = 'w2dc-fa-tachometer';
	$icons[] = 'w2dc-fa-tag';
	$icons[] = 'w2dc-fa-tags';
	$icons[] = 'w2dc-fa-tasks';
	$icons[] = 'w2dc-fa-taxi';
	$icons[] = 'w2dc-fa-tencent-weibo';
	$icons[] = 'w2dc-fa-terminal';
	$icons[] = 'w2dc-fa-text-height';
	$icons[] = 'w2dc-fa-text-width';
	$icons[] = 'w2dc-fa-th';
	$icons[] = 'w2dc-fa-th-large';
	$icons[] = 'w2dc-fa-th-list';
	$icons[] = 'w2dc-fa-thumb-tack';
	$icons[] = 'w2dc-fa-thumbs-down';
	$icons[] = 'w2dc-fa-thumbs-o-down';
	$icons[] = 'w2dc-fa-thumbs-o-up';
	$icons[] = 'w2dc-fa-thumbs-up';
	$icons[] = 'w2dc-fa-ticket';
	$icons[] = 'w2dc-fa-times';
	$icons[] = 'w2dc-fa-times-circle';
	$icons[] = 'w2dc-fa-times-circle-o';
	$icons[] = 'w2dc-fa-tint';
	$icons[] = 'w2dc-fa-toggle-down';
	$icons[] = 'w2dc-fa-toggle-left';
	$icons[] = 'w2dc-fa-toggle-off';
	$icons[] = 'w2dc-fa-toggle-on';
	$icons[] = 'w2dc-fa-toggle-right';
	$icons[] = 'w2dc-fa-toggle-up';
	$icons[] = 'w2dc-fa-train';
	$icons[] = 'w2dc-fa-transgender';
	$icons[] = 'w2dc-fa-transgender-alt';
	$icons[] = 'w2dc-fa-trash';
	$icons[] = 'w2dc-fa-trash-o';
	$icons[] = 'w2dc-fa-tree';
	$icons[] = 'w2dc-fa-trello';
	$icons[] = 'w2dc-fa-trophy';
	$icons[] = 'w2dc-fa-truck';
	$icons[] = 'w2dc-fa-try';
	$icons[] = 'w2dc-fa-tty';
	$icons[] = 'w2dc-fa-tumblr';
	$icons[] = 'w2dc-fa-tumblr-square';
	$icons[] = 'w2dc-fa-turkish-lira';
	$icons[] = 'w2dc-fa-twitch';
	$icons[] = 'w2dc-fa-twitter';
	$icons[] = 'w2dc-fa-twitter-square';
	$icons[] = 'w2dc-fa-umbrella';
	$icons[] = 'w2dc-fa-underline';
	$icons[] = 'w2dc-fa-undo';
	$icons[] = 'w2dc-fa-university';
	$icons[] = 'w2dc-fa-unlink';
	$icons[] = 'w2dc-fa-unlock';
	$icons[] = 'w2dc-fa-unlock-alt';
	$icons[] = 'w2dc-fa-unsorted';
	$icons[] = 'w2dc-fa-upload';
	$icons[] = 'w2dc-fa-usd';
	$icons[] = 'w2dc-fa-user';
	$icons[] = 'w2dc-fa-user-md';
	$icons[] = 'w2dc-fa-user-plus';
	$icons[] = 'w2dc-fa-user-secret';
	$icons[] = 'w2dc-fa-user-times';
	$icons[] = 'w2dc-fa-users';
	$icons[] = 'w2dc-fa-venus';
	$icons[] = 'w2dc-fa-venus-double';
	$icons[] = 'w2dc-fa-venus-mars';
	$icons[] = 'w2dc-fa-viacoin';
	$icons[] = 'w2dc-fa-video-camera';
	$icons[] = 'w2dc-fa-vimeo-square';
	$icons[] = 'w2dc-fa-vine';
	$icons[] = 'w2dc-fa-vk';
	$icons[] = 'w2dc-fa-volume-down';
	$icons[] = 'w2dc-fa-volume-off';
	$icons[] = 'w2dc-fa-volume-up';
	$icons[] = 'w2dc-fa-warning';
	$icons[] = 'w2dc-fa-wechat';
	$icons[] = 'w2dc-fa-weibo';
	$icons[] = 'w2dc-fa-weixin';
	$icons[] = 'w2dc-fa-whatsapp';
	$icons[] = 'w2dc-fa-wheelchair';
	$icons[] = 'w2dc-fa-wifi';
	$icons[] = 'w2dc-fa-windows';
	$icons[] = 'w2dc-fa-won';
	$icons[] = 'w2dc-fa-wordpress';
	$icons[] = 'w2dc-fa-wrench';
	$icons[] = 'w2dc-fa-xing';
	$icons[] = 'w2dc-fa-xing-square';
	$icons[] = 'w2dc-fa-yahoo';
	$icons[] = 'w2dc-fa-yen';
	$icons[] = 'w2dc-fa-youtube';
	$icons[] = 'w2dc-fa-youtube-play';
	$icons[] = 'w2dc-fa-youtube-square';
	return $icons;
}

function w2dc_current_user_can_edit_listing($listing_id) {
	if (!current_user_can('edit_others_posts')) {
		$post = get_post($listing_id);
		$current_user = wp_get_current_user();
		if ($current_user->ID != $post->post_author)
			return false;
		if ($post->post_status == 'pending'  && !is_admin())
			return false;
	}
	return true;
}

function w2dc_get_edit_listing_link($listing_id, $context = 'display') {
	if (w2dc_current_user_can_edit_listing($listing_id)) {
		$post = get_post($listing_id);
		$current_user = wp_get_current_user();
		if (current_user_can('edit_others_posts') && $current_user->ID != $post->post_author)
			return get_edit_post_link($listing_id, $context);
		else
			return apply_filters('w2dc_get_edit_listing_link', get_edit_post_link($listing_id, $context), $listing_id);
	}
}

function w2dc_show_edit_button($listing_id) {
	global $w2dc_instance;
	if (
		w2dc_current_user_can_edit_listing($listing_id)
		&&
		(
			(get_option('w2dc_fsubmit_addon') && isset($w2dc_instance->dashboard_page_url) && $w2dc_instance->dashboard_page_url)
			||
			((!get_option('w2dc_fsubmit_addon') || !isset($w2dc_instance->dashboard_page_url) || !$w2dc_instance->dashboard_page_url) && !get_option('w2dc_hide_admin_bar') && current_user_can('edit_posts'))
		)
	)
		return true;
}

function w2dc_hex2rgba($color, $opacity = false) {
	$default = 'rgb(0,0,0)';

	//Return default if no color provided
	if(empty($color))
		return $default;

	//Sanitize $color if "#" is provided
	if ($color[0] == '#' ) {
		$color = substr( $color, 1 );
	}

	//Check if color has 6 or 3 characters and get values
	if (strlen($color) == 6) {
		$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
	} elseif ( strlen( $color ) == 3 ) {
		$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
	} else {
		return $default;
	}

	//Convert hexadec to rgb
	$rgb =  array_map('hexdec', $hex);

	//Check if opacity is set(rgba or rgb)
	if($opacity){
		if(abs($opacity) > 1)
			$opacity = 1.0;
		$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
	} else {
		$output = 'rgb('.implode(",",$rgb).')';
	}

	//Return rgb(a) color string
	return $output;
}

function w2dc_adjust_brightness($hex, $steps) {
	// Steps should be between -255 and 255. Negative = darker, positive = lighter
	$steps = max(-255, min(255, $steps));

	// Normalize into a six character long hex string
	$hex = str_replace('#', '', $hex);
	if (strlen($hex) == 3) {
		$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
	}

	// Split into three parts: R, G and B
	$color_parts = str_split($hex, 2);
	$return = '#';

	foreach ($color_parts as $color) {
		$color   = hexdec($color); // Convert to decimal
		$color   = max(0,min(255,$color + $steps)); // Adjust color
		$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
	}

	return $return;
}

// adapted for Relevanssi
function w2dc_is_relevanssi_search($defaults = false) {
	if (function_exists('relevanssi_do_query') && (w2dc_getValue($_GET, 'what_search') || w2dc_getValue($_POST, 'what_search') || ($defaults && isset($defaults['what_search']) && $defaults['what_search'])))
		return true;
}

function w2dc_is_maps_used() {
	global $w2dc_instance;
	
	foreach ($w2dc_instance->levels->levels_array as $level) {
		if ($level->google_map)
			return true;
	}
	return false;
}

function w2dc_error_log($wp_error) {
	w2dc_addMessage($wp_error->get_error_message(), 'error');
	error_log($wp_error->get_error_message());
}

function w2dc_country_codes() {
	$codes['Afghanistan'] = 'AF';
	$codes['Åland Islands'] = 'AX';
	$codes['Albania'] = 'AL';
	$codes['Algeria'] = 'DZ';
	$codes['American Samoa'] = 'AS';
	$codes['Andorra'] = 'AD';
	$codes['Angola'] = 'AO';
	$codes['Anguilla'] = 'AI';
	$codes['Antarctica'] = 'AQ';
	$codes['Antigua and Barbuda'] = 'AG';
	$codes['Argentina'] = 'AR';
	$codes['Armenia'] = 'AM';
	$codes['Aruba'] = 'AW';
	$codes['Australia'] = 'AU';
	$codes['Austria'] = 'AT';
	$codes['Azerbaijan'] = 'AZ';
	$codes['Bahamas'] = 'BS';
	$codes['Bahrain'] = 'BH';
	$codes['Bangladesh'] = 'BD';
	$codes['Barbados'] = 'BB';
	$codes['Belarus'] = 'BY';
	$codes['Belgium'] = 'BE';
	$codes['Belize'] = 'BZ';
	$codes['Benin'] = 'BJ';
	$codes['Bermuda'] = 'BM';
	$codes['Bhutan'] = 'BT';
	$codes['Bolivia, Plurinational State of'] = 'BO';
	$codes['Bonaire, Sint Eustatius and Saba'] = 'BQ';
	$codes['Bosnia and Herzegovina'] = 'BA';
	$codes['Botswana'] = 'BW';
	$codes['Bouvet Island'] = 'BV';
	$codes['Brazil'] = 'BR';
	$codes['British Indian Ocean Territory'] = 'IO';
	$codes['Brunei Darussalam'] = 'BN';
	$codes['Bulgaria'] = 'BG';
	$codes['Burkina Faso'] = 'BF';
	$codes['Burundi'] = 'BI';
	$codes['Cambodia'] = 'KH';
	$codes['Cameroon'] = 'CM';
	$codes['Canada'] = 'CA';
	$codes['Cape Verde'] = 'CV';
	$codes['Cayman Islands'] = 'KY';
	$codes['Central African Republic'] = 'CF';
	$codes['Chad'] = 'TD';
	$codes['Chile'] = 'CL';
	$codes['China'] = 'CN';
	$codes['Christmas Island'] = 'CX';
	$codes['Cocos (Keeling) Islands'] = 'CC';
	$codes['Colombia'] = 'CO';
	$codes['Comoros'] = 'KM';
	$codes['Congo'] = 'CG';
	$codes['Congo, the Democratic Republic of the'] = 'CD';
	$codes['Cook Islands'] = 'CK';
	$codes['Costa Rica'] = 'CR';
	$codes['Côte d\'Ivoire'] = 'CI';
	$codes['Croatia'] = 'HR';
	$codes['Cuba'] = 'CU';
	$codes['Curaçao'] = 'CW';
	$codes['Cyprus'] = 'CY';
	$codes['Czech Republic'] = 'CZ';
	$codes['Denmark'] = 'DK';
	$codes['Djibouti'] = 'DJ';
	$codes['Dominica'] = 'DM';
	$codes['Dominican Republic'] = 'DO';
	$codes['Ecuador'] = 'EC';
	$codes['Egypt'] = 'EG';
	$codes['El Salvador'] = 'SV';
	$codes['Equatorial Guinea'] = 'GQ';
	$codes['Eritrea'] = 'ER';
	$codes['Estonia'] = 'EE';
	$codes['Ethiopia'] = 'ET';
	$codes['Falkland Islands (Malvinas)'] = 'FK';
	$codes['Faroe Islands'] = 'FO';
	$codes['Fiji'] = 'FJ';
	$codes['Finland'] = 'FI';
	$codes['France'] = 'FR';
	$codes['French Guiana'] = 'GF';
	$codes['French Polynesia'] = 'PF';
	$codes['French Southern Territories'] = 'TF';
	$codes['Gabon'] = 'GA';
	$codes['Gambia'] = 'GM';
	$codes['Georgia'] = 'GE';
	$codes['Germany'] = 'DE';
	$codes['Ghana'] = 'GH';
	$codes['Gibraltar'] = 'GI';
	$codes['Greece'] = 'GR';
	$codes['Greenland'] = 'GL';
	$codes['Grenada'] = 'GD';
	$codes['Guadeloupe'] = 'GP';
	$codes['Guam'] = 'GU';
	$codes['Guatemala'] = 'GT';
	$codes['Guernsey'] = 'GG';
	$codes['Guinea'] = 'GN';
	$codes['Guinea-Bissau'] = 'GW';
	$codes['Guyana'] = 'GY';
	$codes['Haiti'] = 'HT';
	$codes['Heard Island and McDonald Islands'] = 'HM';
	$codes['Holy See (Vatican City State)'] = 'VA';
	$codes['Honduras'] = 'HN';
	$codes['Hong Kong'] = 'HK';
	$codes['Hungary'] = 'HU';
	$codes['Iceland'] = 'IS';
	$codes['India'] = 'IN';
	$codes['Indonesia'] = 'ID';
	$codes['Iran, Islamic Republic of'] = 'IR';
	$codes['Iraq'] = 'IQ';
	$codes['Ireland'] = 'IE';
	$codes['Isle of Man'] = 'IM';
	$codes['Israel'] = 'IL';
	$codes['Italy'] = 'IT';
	$codes['Jamaica'] = 'JM';
	$codes['Japan'] = 'JP';
	$codes['Jersey'] = 'JE';
	$codes['Jordan'] = 'JO';
	$codes['Kazakhstan'] = 'KZ';
	$codes['Kenya'] = 'KE';
	$codes['Kiribati'] = 'KI';
	$codes['Korea, Democratic People\'s Republic of'] = 'KP';
	$codes['Korea, Republic of'] = 'KR';
	$codes['Kuwait'] = 'KW';
	$codes['Kyrgyzstan'] = 'KG';
	$codes['Lao People\'s Democratic Republic'] = 'LA';
	$codes['Latvia'] = 'LV';
	$codes['Lebanon'] = 'LB';
	$codes['Lesotho'] = 'LS';
	$codes['Liberia'] = 'LR';
	$codes['Libya'] = 'LY';
	$codes['Liechtenstein'] = 'LI';
	$codes['Lithuania'] = 'LT';
	$codes['Luxembourg'] = 'LU';
	$codes['Macao'] = 'MO';
	$codes['Macedonia, the Former Yugoslav Republic of'] = 'MK';
	$codes['Madagascar'] = 'MG';
	$codes['Malawi'] = 'MW';
	$codes['Malaysia'] = 'MY';
	$codes['Maldives'] = 'MV';
	$codes['Mali'] = 'ML';
	$codes['Malta'] = 'MT';
	$codes['Marshall Islands'] = 'MH';
	$codes['Martinique'] = 'MQ';
	$codes['Mauritania'] = 'MR';
	$codes['Mauritius'] = 'MU';
	$codes['Mayotte'] = 'YT';
	$codes['Mexico'] = 'MX';
	$codes['Micronesia, Federated States of'] = 'FM';
	$codes['Moldova, Republic of'] = 'MD';
	$codes['Monaco'] = 'MC';
	$codes['Mongolia'] = 'MN';
	$codes['Montenegro'] = 'ME';
	$codes['Montserrat'] = 'MS';
	$codes['Morocco'] = 'MA';
	$codes['Mozambique'] = 'MZ';
	$codes['Myanmar'] = 'MM';
	$codes['Namibia'] = 'NA';
	$codes['Nauru'] = 'NR';
	$codes['Nepal'] = 'NP';
	$codes['Netherlands'] = 'NL';
	$codes['New Caledonia'] = 'NC';
	$codes['New Zealand'] = 'NZ';
	$codes['Nicaragua'] = 'NI';
	$codes['Niger'] = 'NE';
	$codes['Nigeria'] = 'NG';
	$codes['Niue'] = 'NU';
	$codes['Norfolk Island'] = 'NF';
	$codes['Northern Mariana Islands'] = 'MP';
	$codes['Norway'] = 'NO';
	$codes['Oman'] = 'OM';
	$codes['Pakistan'] = 'PK';
	$codes['Palau'] = 'PW';
	$codes['Palestine, State of'] = 'PS';
	$codes['Panama'] = 'PA';
	$codes['Papua New Guinea'] = 'PG';
	$codes['Paraguay'] = 'PY';
	$codes['Peru'] = 'PE';
	$codes['Philippines'] = 'PH';
	$codes['Pitcairn'] = 'PN';
	$codes['Poland'] = 'PL';
	$codes['Portugal'] = 'PT';
	$codes['Puerto Rico'] = 'PR';
	$codes['Qatar'] = 'QA';
	$codes['Réunion'] = 'RE';
	$codes['Romania'] = 'RO';
	$codes['Russian Federation'] = 'RU';
	$codes['Rwanda'] = 'RW';
	$codes['Saint Barthélemy'] = 'BL';
	$codes['Saint Helena, Ascension and Tristan da Cunha'] = 'SH';
	$codes['Saint Kitts and Nevis'] = 'KN';
	$codes['Saint Lucia'] = 'LC';
	$codes['Saint Martin (French part)'] = 'MF';
	$codes['Saint Pierre and Miquelon'] = 'PM';
	$codes['Saint Vincent and the Grenadines'] = 'VC';
	$codes['Samoa'] = 'WS';
	$codes['San Marino'] = 'SM';
	$codes['Sao Tome and Principe'] = 'ST';
	$codes['Saudi Arabia'] = 'SA';
	$codes['Senegal'] = 'SN';
	$codes['Serbia'] = 'RS';
	$codes['Seychelles'] = 'SC';
	$codes['Sierra Leone'] = 'SL';
	$codes['Singapore'] = 'SG';
	$codes['Sint Maarten (Dutch part)'] = 'SX';
	$codes['Slovakia'] = 'SK';
	$codes['Slovenia'] = 'SI';
	$codes['Solomon Islands'] = 'SB';
	$codes['Somalia'] = 'SO';
	$codes['South Africa'] = 'ZA';
	$codes['South Georgia and the South Sandwich Islands'] = 'GS';
	$codes['South Sudan'] = 'SS';
	$codes['Spain'] = 'ES';
	$codes['Sri Lanka'] = 'LK';
	$codes['Sudan'] = 'SD';
	$codes['Suriname'] = 'SR';
	$codes['Svalbard and Jan Mayen'] = 'SJ';
	$codes['Swaziland'] = 'SZ';
	$codes['Sweden'] = 'SE';
	$codes['Switzerland'] = 'CH';
	$codes['Syrian Arab Republic'] = 'SY';
	$codes['Taiwan, Province of China"'] = 'TW';
	$codes['Tajikistan'] = 'TJ';
	$codes['"Tanzania, United Republic of"'] = 'TZ';
	$codes['Thailand'] = 'TH';
	$codes['Timor-Leste'] = 'TL';
	$codes['Togo'] = 'TG';
	$codes['Tokelau'] = 'TK';
	$codes['Tonga'] = 'TO';
	$codes['Trinidad and Tobago'] = 'TT';
	$codes['Tunisia'] = 'TN';
	$codes['Turkey'] = 'TR';
	$codes['Turkmenistan'] = 'TM';
	$codes['Turks and Caicos Islands'] = 'TC';
	$codes['Tuvalu'] = 'TV';
	$codes['Uganda'] = 'UG';
	$codes['Ukraine'] = 'UA';
	$codes['United Arab Emirates'] = 'AE';
	$codes['United Kingdom'] = 'GB';
	$codes['United States'] = 'US';
	$codes['United States Minor Outlying Islands'] = 'UM';
	$codes['Uruguay'] = 'UY';
	$codes['Uzbekistan'] = 'UZ';
	$codes['Vanuatu'] = 'VU';
	$codes['Venezuela,  Bolivarian Republic of'] = 'VE';
	$codes['Viet Nam'] = 'VN';
	$codes['Virgin Islands, British'] = 'VG';
	$codes['Virgin Islands, U.S.'] = 'VI';
	$codes['Wallis and Futuna'] = 'WF';
	$codes['Western Sahara'] = 'EH';
	$codes['Yemen'] = 'YE';
	$codes['Zambia'] = 'ZM';
	$codes['Zimbabwe'] = 'ZW';
	return $codes;
}

function w2dc_is_woo_active() {
	if (
		!get_option('w2dc_payments_addon')
		&&
		in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))
		&&
		get_option('w2dc_woocommerce_functionality')
	) 
		return true;
}

function w2dc_is_woo_packages() {
	global $w2dc_instance;

	if (
		w2dc_is_woo_active() &&
		(get_option('w2dc_woocommerce_mode') == 'packages' || get_option('w2dc_woocommerce_mode') == 'both') &&
		$w2dc_instance->listings_package_product->get_all_packages()
	)
		return true;
}

function w2dc_is_only_woo_packages() {
	global $w2dc_instance;

	if (
		w2dc_is_woo_active() &&
		get_option('w2dc_woocommerce_mode') == 'packages' &&
		$w2dc_instance->listings_package_product->get_all_packages()
	)
		return true;
}

function w2dc_get_admin_notification_email() {
	if (get_option('w2dc_admin_notifications_email'))
		return get_option('w2dc_admin_notifications_email');
	else 
		return get_option('admin_email');
}

function w2dc_wpml_translation_complete_notice() {
	global $sitepress;

	if (function_exists('wpml_object_id_filter') && $sitepress && defined('WPML_ST_VERSION')) {
		echo '<p class="description">';
		_e('After save do not forget to set completed translation status for this string on String Translation page.', 'W2DC');
		echo '</p>';
	}
}

function w2dc_phpmailer_init($phpmailer) {
	$phpmailer->AltBody = wp_specialchars_decode($phpmailer->Body, ENT_QUOTES);
}
function w2dc_mail($email, $subject, $body, $headers = null) {
	// create and add HTML part into emails
	add_action('phpmailer_init', 'w2dc_phpmailer_init');

	if (!$headers) {
		$headers[] = "From: " . get_option('blogname') . " <" . w2dc_get_admin_notification_email() . ">";
		$headers[] = "Reply-To: " . w2dc_get_admin_notification_email();
		$headers[] = "Content-Type: text/html";
	}
		
	$subject = "[" . get_option('blogname') . "] " .$subject;

	$body = make_clickable(wpautop($body));
	
	return wp_mail($email, $subject, $body, $headers);
}

function w2dc_getListing($post) {
	$listing = new w2dc_listing;
	if ($listing->loadListingFromPost($post))
		return $listing;
}

function w2dc_is_listing() {
	global $w2dc_instance;

	if (($directory_controller = $w2dc_instance->getShortcodeProperty(W2DC_MAIN_SHORTCODE)) || ($directory_controller = $w2dc_instance->getShortcodeProperty(W2DC_LISTING_SHORTCODE)) || ($directory_controller = $w2dc_instance->getShortcodeProperty('webdirectory-listing'))) {
		if ($directory_controller->is_single) {
			return $directory_controller->listing;
		}
	}
}

function w2dc_is_category() {
	global $w2dc_instance;

	if (($directory_controller = $w2dc_instance->getShortcodeProperty(W2DC_MAIN_SHORTCODE))) {
		if ($directory_controller->is_category) {
			return $directory_controller->category;
		}
	}
}

function w2dc_is_location() {
	global $w2dc_instance;

	if (($directory_controller = $w2dc_instance->getShortcodeProperty(W2DC_MAIN_SHORTCODE))) {
		if ($directory_controller->is_location) {
			return $directory_controller->location;
		}
	}
}

function w2dc_is_tag() {
	global $w2dc_instance;

	if (($directory_controller = $w2dc_instance->getShortcodeProperty(W2DC_MAIN_SHORTCODE))) {
		if ($directory_controller->is_tag) {
			return $directory_controller->tag;
		}
	}
}

function w2dc_getListingDirectory($listing_id) {
	global $w2dc_instance;

	if (get_post_type($listing_id) == W2DC_POST_TYPE) {
		if ($directory_id = get_post_meta($listing_id, '_directory_id', true)) {
			if ($directory = $w2dc_instance->directories->getDirectoryById($directory_id)) {
				return $directory;
			}
		}
	} elseif ($w2dc_instance->current_directory) {
		return $w2dc_instance->current_directory;
	}
	return $w2dc_instance->directories->getDefaultDirectory();
}

?>