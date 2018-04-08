<?php

define('W2DC_FSUBMIT_PATH', plugin_dir_path(__FILE__));

function w2dc_fsubmit_loadPaths() {
	define('W2DC_FSUBMIT_TEMPLATES_PATH', W2DC_FSUBMIT_PATH . 'templates/');
	define('W2DC_FSUBMIT_RESOURCES_PATH', W2DC_FSUBMIT_PATH . 'resources/');
	define('W2DC_FSUBMIT_RESOURCES_URL', plugins_url('/', __FILE__) . 'resources/');
}
add_action('init', 'w2dc_fsubmit_loadPaths', 0);

define('W2DC_FSUBMIT_SHORTCODE', 'webdirectory-submit');
define('W2DC_DASHBOARD_SHORTCODE', 'webdirectory-dashboard');

include_once W2DC_FSUBMIT_PATH . 'classes/dashboard_controller.php';
include_once W2DC_FSUBMIT_PATH . 'classes/submit_controller.php';
include_once W2DC_FSUBMIT_PATH . 'classes/levels_table_controller.php';
include_once W2DC_FSUBMIT_PATH . 'classes/wc/wc.php';

global $w2dc_wpml_dependent_options;
$w2dc_wpml_dependent_options[] = 'w2dc_tospage';
$w2dc_wpml_dependent_options[] = 'w2dc_submit_login_page';
$w2dc_wpml_dependent_options[] = 'w2dc_dashboard_login_page';

class w2dc_fsubmit_plugin {

	public function init() {
		global $w2dc_instance, $w2dc_shortcodes_init;
		
		if (!get_option('w2dc_installed_fsubmit'))
			//w2dc_install_fsubmit();
			add_action('init', 'w2dc_install_fsubmit', 0);
		add_action('w2dc_version_upgrade', 'w2dc_upgrade_fsubmit');

		add_filter('w2dc_build_settings', array($this, 'plugin_settings'));

		// add new shortcodes for frontend submission and dashboard
		$w2dc_shortcodes_init['webdirectory-submit'] = 'w2dc_submit_controller';
		$w2dc_shortcodes_init['webdirectory-dashboard'] = 'w2dc_dashboard_controller';
		$w2dc_shortcodes_init['webdirectory-levels-table'] = 'w2dc_levels_table_controller';
		add_shortcode('webdirectory-submit', array($w2dc_instance, 'renderShortcode'));
		add_shortcode('webdirectory-dashboard', array($w2dc_instance, 'renderShortcode'));
		add_shortcode('webdirectory-levels-table', array($w2dc_instance, 'renderShortcode'));
		
		add_action('init', array($this, 'getSubmitPage'), 0);
		add_action('init', array($this, 'getDasboardPage'), 0);

		add_filter('w2dc_get_edit_listing_link', array($this, 'edit_listings_links'), 10, 2);

		add_action('w2dc_directory_frontpanel', array($this, 'add_submit_button'));
		add_action('w2dc_directory_frontpanel', array($this, 'add_claim_button'));
		
		add_action('w2dc_directory_frontpanel', array($this, 'add_logout_button'));

		add_action('init', array($this, 'remove_admin_bar'));
		add_action('admin_init', array($this, 'restrict_dashboard'));

		//add_action('w2dc_listing_process_activate', array($this, 'listing_activation_post_status'), 10, 2);
		
		if (get_option('w2dc_payments_addon')) {
			add_action('show_user_profile', array($this, 'add_user_profile_fields'));
			add_action('edit_user_profile', array($this, 'add_user_profile_fields'));
			add_action('personal_options_update', array($this, 'save_user_profile_fields'));
			add_action('edit_user_profile_update', array($this, 'save_user_profile_fields'));
		}

		add_action('transition_post_status', array($this, 'on_listing_approval'), 10, 3);
		
		add_filter('no_texturize_shortcodes', array($this, 'w2dc_no_texturize'));

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'));
	}
	
	public function w2dc_no_texturize($shortcodes) {
		$shortcodes[] = 'webdirectory-submit';
		$shortcodes[] = 'webdirectory-dashboard';

		return $shortcodes;
	}

	public function plugin_settings($options) {
		global $sitepress; // adapted for WPML

		$pages = get_pages();
		$all_pages[] = array('value' => 0, 'label' => __('- Select page -', 'W2DC'));
		foreach ($pages AS $page)
			$all_pages[] = array('value' => $page->ID, 'label' => $page->post_title);
		
		$options['template']['menus']['general']['controls']['fsubmit'] = array(
			'type' => 'section',
			'title' => __('Frontend submission and dashboard', 'W2DC'),
			'fields' => array(
				array(
					'type' => 'radiobutton',
					'name' => 'w2dc_fsubmit_login_mode',
					'label' => __('Frontend submission login mode', 'W2DC'),
					'items' => array(
						array(
							'value' => 1,
							'label' => __('login required', 'W2DC'),
						),
						array(
							'value' => 2,
							'label' => __('necessary to fill in user info form', 'W2DC'),
						),
						array(
							'value' => 3,
							'label' => __('not necessary to fill in user info form', 'W2DC'),
						),
						array(
							'value' => 4,
							'label' => __('do not show show user info form', 'W2DC'),
						),
					),
					'default' => array(
						get_option('w2dc_fsubmit_login_mode'),
					),
				),
				array(
					'type' => 'select',
					'name' => 'w2dc_fsubmit_default_status',
					'label' => __('Post status after frontend submit', 'W2DC'),
					'items' => array(
						array(
							'value' => 1,
							'label' => __('Pending Review', 'W2DC'),
						),
						array(
							'value' => 2,
							'label' => __('Draft', 'W2DC'),
						),
						array(
							'value' => 3,
							'label' => __('Published', 'W2DC'),
						),
					),
					'default' => array(
						get_option('w2dc_fsubmit_default_status'),
					),
				),
				array(
					'type' => 'select',
					'name' => 'w2dc_fsubmit_edit_status',
					'label' => __('Post status after listing was modified', 'W2DC'),
					'items' => array(
						array(
							'value' => 1,
							'label' => __('Pending Review', 'W2DC'),
						),
						array(
							'value' => 2,
							'label' => __('Draft', 'W2DC'),
						),
						array(
							'value' => 3,
							'label' => __('Published', 'W2DC'),
						),
					),
					'default' => array(get_option('w2dc_fsubmit_edit_status'),),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_fsubmit_button',
					'label' => __('Enable submit listing button', 'W2DC'),
					'default' => get_option('w2dc_fsubmit_button'),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_hide_admin_bar',
					'label' => __('Hide top admin bar at the frontend for regular users and do not allow them to see dashboard at all', 'W2DC'),
					'default' => get_option('w2dc_hide_admin_bar'),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_allow_edit_profile',
					'label' => __('Allow users to manage own profile at the frontend dashboard', 'W2DC'),
					'default' => get_option('w2dc_allow_edit_profile'),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_enable_tags',
					'label' => __('Enable listings tags input at the frontend', 'W2DC'),
					'default' => get_option('w2dc_enable_tags'),
				),
				array(
					'type' => 'select',
					'name' => w2dc_get_wpml_dependent_option_name('w2dc_tospage'), // adapted for WPML
					'label' => __('Require Terms of Services on submission page?', 'W2DC'),
					'description' => __('If yes, create a WordPress page containing your TOS agreement and assign it using the dropdown above.', 'W2DC') . w2dc_get_wpml_dependent_option_description(),
					'items' => $all_pages,
					'default' => (w2dc_get_wpml_dependent_option('w2dc_tospage') ? array(w2dc_get_wpml_dependent_option('w2dc_tospage')) : array(0)), // adapted for WPML
				),
				array(
					'type' => 'select',
					'name' => w2dc_get_wpml_dependent_option_name('w2dc_submit_login_page'), // adapted for WPML
					'label' => __('Use custom login page for listings submission process', 'W2DC'),
					'description' => __('You may use any 3rd party plugin to make custom login page and assign it with submission process using the dropdown above.', 'W2DC') . w2dc_get_wpml_dependent_option_description(),
					'items' => $all_pages,
					'default' => (w2dc_get_wpml_dependent_option('w2dc_submit_login_page') ? array(w2dc_get_wpml_dependent_option('w2dc_submit_login_page')) : array(0)), // adapted for WPML
				),
				array(
					'type' => 'select',
					'name' => w2dc_get_wpml_dependent_option_name('w2dc_dashboard_login_page'), // adapted for WPML
					'label' => __('Use custom login page for login into dashboard', 'W2DC'),
					'description' => __('You may use any 3rd party plugin to make custom login page and assign it with login into dashboard using the dropdown above.', 'W2DC') . w2dc_get_wpml_dependent_option_description(),
					'items' => $all_pages,
					'default' => (w2dc_get_wpml_dependent_option('w2dc_dashboard_login_page') ? array(w2dc_get_wpml_dependent_option('w2dc_dashboard_login_page')) : array(0)), // adapted for WPML
				),
			),
		);
		$options['template']['menus']['general']['controls']['claim'] = array(
			'type' => 'section',
			'title' => __('Claim functionality', 'W2DC'),
			'fields' => array(
				array(
					'type' => 'toggle',
					'name' => 'w2dc_claim_functionality',
					'label' => __('Enable claim functionality', 'W2DC'),
					'default' => get_option('w2dc_claim_functionality'),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_claim_approval',
					'label' => __('Approval of claim required', 'W2DC'),
					'description' => __('In other case claim will be processed immediately without any notifications', 'W2DC'),
					'default' => get_option('w2dc_claim_approval'),
				),
				array(
					'type' => 'radiobutton',
					'name' => 'w2dc_after_claim',
					'label' => __('What will be with listing status after successful approval?', 'W2DC'),
					'description' => __('When set to expired - renewal may be payment option', 'W2DC'),
					'items' => array(
						array(
							'value' => 'active',
							'label' =>__('just approval', 'W2DC'),
						),
						array(
							'value' => 'expired',
							'label' =>__('expired status', 'W2DC'),
						),
					),
					'default' => array(
							get_option('w2dc_after_claim')
					),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_hide_claim_contact_form',
					'label' => __('Hide contact form on claimable listings', 'W2DC'),
					'default' => get_option('w2dc_hide_claim_contact_form'),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_hide_claim_metabox',
					'label' => __('Hide claim metabox at the frontend dashboard', 'W2DC'),
					'default' => get_option('w2dc_hide_claim_metabox'),
				),
			),
		);
		
		// adapted for WPML
		global $sitepress;
		if (function_exists('wpml_object_id_filter') && $sitepress) {
			$options['template']['menus']['advanced']['controls']['wpml']['fields'][] = array(
				'type' => 'toggle',
				'name' => 'w2dc_enable_frontend_translations',
				'label' => __('Enable frontend translations management', 'W2DC'),
				'default' => get_option('w2dc_enable_frontend_translations'),
			);
		}
		
		$options['template']['menus']['notifications']['controls']['notifications']['fields'][] = array(
			'type' => 'textarea',
			'name' => 'w2dc_newuser_notification',
			'label' => __('Registration of new user notification', 'W2DC'),
			'default' => get_option('w2dc_newuser_notification'),
		);

		$options['template']['menus']['notifications']['controls']['notifications']['fields'][] = array(
			'type' => 'textarea',
			'name' => 'w2dc_newlisting_admin_notification',
			'label' => __('Notification to admin about new listing creation', 'W2DC'),
			'default' => get_option('w2dc_newlisting_admin_notification'),
		);

		$options['template']['menus']['notifications']['controls']['notifications']['fields'][] = array(
			'type' => 'textarea',
			'name' => 'w2dc_editlisting_admin_notification',
			'label' => __('Notification to admin about listing modification and pending status', 'W2DC'),
			'default' => get_option('w2dc_editlisting_admin_notification'),
		);

		$options['template']['menus']['notifications']['controls']['notifications']['fields'][] = array(
			'type' => 'textarea',
			'name' => 'w2dc_approval_notification',
			'label' => __('Notification to author about successful listing approval', 'W2DC'),
			'default' => get_option('w2dc_approval_notification'),
		);

		$options['template']['menus']['notifications']['controls']['notifications']['fields'][] = array(
			'type' => 'textarea',
			'name' => 'w2dc_claim_notification',
			'label' => __('Notification of claim to current listing owner', 'W2DC'),
			'default' => get_option('w2dc_claim_notification'),
		);
		$options['template']['menus']['notifications']['controls']['notifications']['fields'][] = array(
			'type' => 'textarea',
			'name' => 'w2dc_claim_approval_notification',
			'label' => __('Notification of successful approval of claim', 'W2DC'),
			'default' => get_option('w2dc_claim_approval_notification'),
		);
		$options['template']['menus']['notifications']['controls']['notifications']['fields'][] = array(
			'type' => 'textarea',
			'name' => 'w2dc_claim_decline_notification',
			'label' => __('Notification of claim decline', 'W2DC'),
			'default' => get_option('w2dc_claim_decline_notification'),
		);
		
		return $options;
	}

	public function getSubmitPage() {
		global $w2dc_instance, $wpdb, $wp_rewrite;
		
		$w2dc_instance->submit_page_url = '';
		$w2dc_instance->submit_page_slug = '';
		$w2dc_instance->submit_page_id = 0;
		
		if ($submit_page = $wpdb->get_row("SELECT ID AS id, post_name AS slug FROM {$wpdb->posts} WHERE (post_content LIKE '%[" . W2DC_FSUBMIT_SHORTCODE . "]%' OR post_content LIKE '%[" . W2DC_FSUBMIT_SHORTCODE . " %') AND post_status = 'publish' AND post_type = 'page' LIMIT 1", ARRAY_A)) {
			$w2dc_instance->submit_page_id = $submit_page['id'];
			$w2dc_instance->submit_page_slug = $submit_page['slug'];
			
			// adapted for WPML
			global $sitepress;
			if (function_exists('wpml_object_id_filter') && $sitepress) {
				if ($tpage = apply_filters('wpml_object_id', $w2dc_instance->submit_page_id, 'page')) {
					$w2dc_instance->submit_page_id = $tpage;
					$w2dc_instance->submit_page_slug = get_post($w2dc_instance->submit_page_id)->post_name;
				}
			}
			
			if ($wp_rewrite->using_permalinks())
				$w2dc_instance->submit_page_url = get_permalink($w2dc_instance->submit_page_id);
			else
				$w2dc_instance->submit_page_url = add_query_arg('page_id', $w2dc_instance->submit_page_id, home_url('/'));
		}

		if (get_option('w2dc_fsubmit_button') && $w2dc_instance->submit_page_id === 0 && is_admin())
			w2dc_addMessage(sprintf(__("You enabled <b>Web 2.0 Directory Frontend submission addon</b>: sorry, but there isn't any page with [webdirectory-submit] shortcode. Create new page with [webdirectory-submit] shortcode or disable Frontend submission addon in settings.", 'W2DC')));
	}

	public function getDasboardPage() {
		global $w2dc_instance, $wpdb, $wp_rewrite;
		
		$w2dc_instance->dashboard_page_url = '';
		$w2dc_instance->dashboard_page_slug = '';
		$w2dc_instance->dashboard_page_id = 0;

		if ($dashboard_page = $wpdb->get_row("SELECT ID AS id, post_name AS slug FROM {$wpdb->posts} WHERE post_content LIKE '%[" . W2DC_DASHBOARD_SHORTCODE . "]%' AND post_status = 'publish' AND post_type = 'page' LIMIT 1", ARRAY_A)) {
			$w2dc_instance->dashboard_page_id = $dashboard_page['id'];
			$w2dc_instance->dashboard_page_slug = $dashboard_page['slug'];
			
			// adapted for WPML
			global $sitepress;
			if (function_exists('wpml_object_id_filter') && $sitepress) {
				if ($tpage = apply_filters('wpml_object_id', $w2dc_instance->dashboard_page_id, 'page')) {
					$w2dc_instance->dashboard_page_id = $tpage;
					$w2dc_instance->dashboard_page_slug = get_post($w2dc_instance->dashboard_page_id)->post_name;
				}
			}
			
			if ($wp_rewrite->using_permalinks())
				$w2dc_instance->dashboard_page_url = get_permalink($w2dc_instance->dashboard_page_id);
			else
				$w2dc_instance->dashboard_page_url = add_query_arg('page_id', $w2dc_instance->dashboard_page_id, home_url('/'));
		}
	}
	
	public function add_submit_button() {
		global $w2dc_instance;

		if (get_option('w2dc_fsubmit_button') && $w2dc_instance->submit_page_url) {
			$page_id = get_the_ID();
			if ($w2dc_instance->directories->isMultiDirectory() && ($page_id == $w2dc_instance->submit_page_id || $page_id == $w2dc_instance->dashboard_page_id)) {
				foreach ($w2dc_instance->directories->directories_array AS $directory) {
					echo '<a class="w2dc-submit-listing-link w2dc-btn w2dc-btn-primary" href="' . w2dc_submitUrl(array('directory' => $directory->id)) . '" rel="nofollow"><span class="w2dc-glyphicon w2dc-glyphicon-plus"></span> ' . sprintf(__('Submit new %s', 'W2DC'), $directory->single) . '</a> ';
				}
			} else {
				echo '<a class="w2dc-submit-listing-link w2dc-btn w2dc-btn-primary" href="' . w2dc_submitUrl() . '" rel="nofollow"><span class="w2dc-glyphicon w2dc-glyphicon-plus"></span> ' . sprintf(__('Submit new %s', 'W2DC'), $w2dc_instance->current_directory->single) . '</a> ';
			}
		}
	}

	public function add_claim_button($listing) {
		global $w2dc_instance;

		if ($listing && $listing->is_claimable && $w2dc_instance->dashboard_page_url && get_option('w2dc_claim_functionality') && $listing->post->post_author != get_current_user_id())
			echo '<a class="w2dc-claim-listing-link w2dc-btn w2dc-btn-primary" href="' . w2dc_dashboardUrl(array('listing_id' => $listing->post->ID, 'w2dc_action' => 'claim_listing')) . '" rel="nofollow"><span class="w2dc-glyphicon w2dc-glyphicon-flag"></span> ' . __('Is this your ad?', 'W2DC') . '</a> ';
	}

	public function add_logout_button() {
		global $w2dc_instance, $post;

		if ($post->ID == $w2dc_instance->dashboard_page_id)
			echo '<a class="w2dc-logout-link w2dc-btn w2dc-btn-primary" href="' . wp_logout_url(w2dc_directoryUrl()) . '" rel="nofollow"><span class="w2dc-glyphicon w2dc-glyphicon-log-out"></span> ' . __('Log out', 'W2DC') . '</a>';
	}
	
	public function remove_admin_bar() {
		if (get_option('w2dc_hide_admin_bar') && !current_user_can('administrator') && !is_admin()) {
			show_admin_bar(false);
			add_filter('show_admin_bar', '__return_false', 99999);
		}
	}

	public function restrict_dashboard() {
		global $w2dc_instance, $pagenow;

		if ($pagenow != 'admin-ajax.php' && $pagenow != 'async-upload.php')
			if (get_option('w2dc_hide_admin_bar') && !current_user_can('administrator') && is_admin()) {
				//w2dc_addMessage(__('You can not see dashboard!', 'W2DC'), 'error');
				wp_redirect(w2dc_directoryUrl());
				die();
			}
	}

	public function edit_listings_links($url, $post_id) {
		global $w2dc_instance;

		if (!is_admin() && $w2dc_instance->dashboard_page_url && ($post = get_post($post_id)) && $post->post_type == W2DC_POST_TYPE)
			return w2dc_dashboardUrl(array('w2dc_action' => 'edit_listing', 'listing_id' => $post_id));
	
		return $url;
	}
	
	/* public function listing_activation_post_status($listing, $is_renew) {
		if (!$is_renew) {
			if ($listing->post->post_status != 'publish') {
				if (get_option('w2dc_fsubmit_default_status') == 1) {
					$post_status = 'pending';
					$message = __('Listing awaiting moderators approval.', 'W2DC');
				} elseif (get_option('w2dc_fsubmit_default_status') == 2) {
					$post_status = 'draft';
					$message = __('Listing was saved successfully as draft! Contact site manager, please.', 'W2DC');
				} elseif (get_option('w2dc_fsubmit_default_status') == 3) {
					$post_status = 'publish';
					$message = false;
				}
				wp_update_post(array('ID' => $listing->post->ID, 'post_status' => $post_status));
				if ($message)
					w2dc_addMessage($message);
			}
		}
	} */
	
	public function add_user_profile_fields($user) { ?>
		<h3><?php _e('Directory billing information', 'W2DC'); ?></h3>
	
		<table class="form-table">
			<tr>
				<th><label for="w2dc_billing_name"><?php _e('Full name', 'W2DC'); ?></label></th>
				<td>
					<input type="text" name="w2dc_billing_name" id="w2dc_billing_name" value="<?php echo esc_attr(get_the_author_meta('w2dc_billing_name', $user->ID)); ?>" class="regular-text" /><br />
				</td>
			</tr>
			<tr>
				<th><label for="w2dc_billing_address"><?php _e('Full address', 'W2DC'); ?></label></th>
				<td>
					<textarea name="w2dc_billing_address" id="w2dc_billing_address" cols="30" rows="3"><?php echo esc_textarea(get_the_author_meta('w2dc_billing_address', $user->ID)); ?></textarea>
				</td>
			</tr>
		</table>
<?php }

	public function save_user_profile_fields($user_id) {
		if (!current_user_can('edit_user', $user_id))
			return false;

		update_user_meta($user_id, 'w2dc_billing_name', $_POST['w2dc_billing_name']);
		update_user_meta($user_id, 'w2dc_billing_address', $_POST['w2dc_billing_address']);
	}

	public function on_listing_approval($new_status, $old_status, $post) {
		global $w2dc_instance;

		if (get_option('w2dc_approval_notification')) {
			if (
				$post->post_type == W2DC_POST_TYPE &&
				'publish' == $new_status &&
				'pending' == $old_status &&
				($listing = $w2dc_instance->listings_manager->loadListing($post)) &&
				($author = get_userdata($listing->post->post_author))
			) {
				$subject = __('Approval of listing', 'W2DC');
					
				$body = str_replace('[author]', $author->display_name,
						str_replace('[listing]', $listing->post->post_title,
						str_replace('[link]', w2dc_dashboardUrl(),
				get_option('w2dc_approval_notification'))));
					
				w2dc_mail($author->user_email, $subject, $body);
			}
		}
	}
	
	public function enqueue_scripts_styles($load_scripts_styles = false) {
		global $w2dc_instance, $w2dc_fsubmit_enqueued;
		if (($w2dc_instance->frontend_controllers || $load_scripts_styles) && !$w2dc_fsubmit_enqueued) {
			if (!(function_exists('is_rtl') && is_rtl()))
				wp_register_style('w2dc_fsubmit', W2DC_FSUBMIT_RESOURCES_URL . 'css/submitlisting.css');
			else
				wp_register_style('w2dc_fsubmit', W2DC_FSUBMIT_RESOURCES_URL . 'css/submitlisting-rtl.css');
			wp_enqueue_style('w2dc_fsubmit');
			if (is_file(W2DC_FSUBMIT_RESOURCES_PATH . 'css/submitlisting-custom.css'))
				wp_register_style('w2dc_fsubmit-custom', W2DC_FSUBMIT_RESOURCES_URL . 'css/submitlisting-custom.css');
			
			wp_enqueue_style('w2dc_fsubmit-custom');

			$w2dc_fsubmit_enqueued = true;
		}
	}
	
	public function enqueue_login_scripts_styles() {
		global $action;
		$action = 'login';
		do_action('login_enqueue_scripts');
		do_action('login_head');
	}
}

function w2dc_install_fsubmit() {
	add_option('w2dc_fsubmit_default_status', 3);
	add_option('w2dc_fsubmit_login_mode', 1);

	w2dc_upgrade_fsubmit('1.5.0');
	w2dc_upgrade_fsubmit('1.5.4');
	w2dc_upgrade_fsubmit('1.6.2');
	w2dc_upgrade_fsubmit('1.8.3');
	w2dc_upgrade_fsubmit('1.8.4');
	w2dc_upgrade_fsubmit('1.9.0');
	w2dc_upgrade_fsubmit('1.9.7');
	w2dc_upgrade_fsubmit('1.10.0');
	w2dc_upgrade_fsubmit('1.12.7');
	w2dc_upgrade_fsubmit('1.13.0');
	w2dc_upgrade_fsubmit('1.14.0');
	
	add_option('w2dc_installed_fsubmit', 1);
}

function w2dc_upgrade_fsubmit($new_version) {
	if ($new_version == '1.5.0') {
		add_option('w2dc_fsubmit_edit_status', 3);
		add_option('w2dc_fsubmit_button', 1);
		add_option('w2dc_hide_admin_bar', 0);
		add_option('w2dc_newuser_notification', 'Hello [author],

your listing "[listing]" was successfully submitted.

You may manage your listing using following credentials:
login: [login]
password: [password]');
	}
	
	if ($new_version == '1.5.4')
		add_option('w2dc_allow_edit_profile', 1);

	if ($new_version == '1.6.2')
		add_option('w2dc_enable_frontend_translations', 1);

	if ($new_version == '1.8.3') {
		add_option('w2dc_claim_functionality', 0);
		add_option('w2dc_claim_approval', 1);
		add_option('w2dc_after_claim', 'active');
		add_option('w2dc_hide_claim_contact_form', 0);
		add_option('w2dc_claim_notification', 'Hello [author],

your listing "[listing]" was claimed by [claimer].

You may approve or reject this claim at
[link]

[message]');
		add_option('w2dc_claim_approval_notification', 'Hello [claimer],

congratulations, your claim for listing "[listing]" was successfully approved.

Now you may manage your listing at the dashboard
[link]');
		add_option('w2dc_newlisting_admin_notification', 'Hello,

user [user] created new listing "[listing]".

You may manage this listing at
[link]');
	}
	
	if ($new_version == '1.8.4') {
		add_option('w2dc_enable_tags', 1);
	}

	if ($new_version == '1.9.0') {
		add_option('w2dc_tospage', "0");
	}

	if ($new_version == '1.9.7') {
		add_option('w2dc_hide_claim_metabox', 0);
	}

	if ($new_version == '1.10.0') {
		add_option('w2dc_submit_login_page', "0");
		add_option('w2dc_dashboard_login_page', "0");
	}

	if ($new_version == '1.12.7') {
		add_option('w2dc_approval_notification', 'Hello [author],

your listing "[listing]" was successfully approved.
				
Now you may manage your listing at the dashboard
[link]');
		add_option('w2dc_claim_decline_notification', 'Hello [claimer],

your claim for listing "[listing]" was declined.');
	}
	
	if ($new_version == '1.13.0') {
		add_option('w2dc_woocommerce_functionality', 0);
		add_option('w2dc_woocommerce_mode', 'both');
	}

	if ($new_version == '1.14.0') {
		add_option('w2dc_editlisting_admin_notification', 'Hello,

user [user] modified listing "[listing]". Now it is pending review.

You may manage this listing at
[link]');
	}
}

function w2dc_submitUrl($path = '') {
	global $w2dc_instance;

	// adapted for WPML
	global $sitepress;
	if (function_exists('wpml_object_id_filter') && $sitepress) {
		if ($sitepress->get_option('language_negotiation_type') == 3) {
			// remove any previous value.
			$w2dc_instance->submit_page_url = remove_query_arg('lang', $w2dc_instance->submit_page_url);
		}
	}

	if (!is_array($path)) {
		if ($path) {
			// found that on some instances of WP "native" trailing slashes may be missing
			$url = rtrim($w2dc_instance->submit_page_url, '/') . '/' . rtrim($path, '/') . '/';
		} else
			$url = $w2dc_instance->submit_page_url;
	} else
		$url = add_query_arg($path, $w2dc_instance->submit_page_url);

	// adapted for WPML
	global $sitepress;
	if (function_exists('wpml_object_id_filter') && $sitepress) {
		$url = $sitepress->convert_url($url);
	}
	
	if (empty($path) || (is_array($path) && !key_exists('directory', $path))) {
		$url = add_query_arg('directory', $w2dc_instance->current_directory->id, $url);
	}

	return $url;
}

function w2dc_dashboardUrl($path = '') {
	global $w2dc_instance;
	
	if ($w2dc_instance->dashboard_page_url) {
		// adapted for WPML
		global $sitepress;
		if (function_exists('wpml_object_id_filter') && $sitepress) {
			if ($sitepress->get_option('language_negotiation_type') == 3) {
				// remove any previous value.
				$w2dc_instance->dashboard_page_url = remove_query_arg('lang', $w2dc_instance->dashboard_page_url);
			}
		}
	
		if (!is_array($path)) {
			if ($path) {
				// found that on some instances of WP "native" trailing slashes may be missing
				$url = rtrim($w2dc_instance->dashboard_page_url, '/') . '/' . rtrim($path, '/') . '/';
			} else
				$url = $w2dc_instance->dashboard_page_url;
		} else
			$url = add_query_arg($path, $w2dc_instance->dashboard_page_url);
	
		// adapted for WPML
		global $sitepress;
		if (function_exists('wpml_object_id_filter') && $sitepress) {
			$url = $sitepress->convert_url($url);
		}
	} else
		$url = w2dc_directoryUrl();

	return $url;
}

global $w2dc_fsubmit_instance;

$w2dc_fsubmit_instance = new w2dc_fsubmit_plugin();
$w2dc_fsubmit_instance->init();

?>
