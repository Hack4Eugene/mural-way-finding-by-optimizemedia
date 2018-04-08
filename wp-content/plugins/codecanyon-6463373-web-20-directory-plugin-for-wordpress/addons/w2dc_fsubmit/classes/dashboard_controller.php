<?php 

class w2dc_dashboard_controller extends w2dc_frontend_controller {

	public function init($args = array()) {
		global $w2dc_instance, $w2dc_fsubmit_instance, $sitepress;
		
		parent::init($args);

		if (!is_user_logged_in()) {
			if (w2dc_get_wpml_dependent_option('w2dc_dashboard_login_page') && w2dc_get_wpml_dependent_option('w2dc_dashboard_login_page') != get_the_ID()) {
				$url = get_permalink(w2dc_get_wpml_dependent_option('w2dc_dashboard_login_page'));
				$url = add_query_arg('redirect_to', urlencode(get_permalink()), $url);
				wp_redirect($url);
			} else {
				add_action('wp_enqueue_scripts', array($w2dc_fsubmit_instance, 'enqueue_login_scripts_styles'));
				$this->template = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'login_form.tpl.php');
			}
		} else {
			if (isset($_POST['referer']))
				$this->referer = $_POST['referer'];
			else
				$this->referer = wp_get_referer();
			if (isset($_POST['cancel']) && isset($_POST['referer'])) {
				wp_redirect($_POST['referer']);
				die();
			}

			if (!$w2dc_instance->action) {
				if (get_query_var('page'))
					$paged = get_query_var('page');
				elseif (get_query_var('paged'))
					$paged = get_query_var('paged');
				else
					$paged = 1;
			} else
				$paged = -1;
			
			$args = array(
					'post_type' => W2DC_POST_TYPE,
					//'author' => get_current_user_id(),
					'paged' => $paged,
					'posts_per_page' => 20,
					'post_status' => 'any'
			);
			add_filter('posts_where', array($this, 'add_claimed_listings_where'));
			$this->query = new WP_Query($args);
			remove_filter('posts_where', array($this, 'add_claimed_listings_where'));
			wp_reset_postdata();
			
			$this->listings_count = $this->query->found_posts;
			
			$this->active_tab = 'listings';

			if (!$w2dc_instance->action) {
				$this->processQuery(false);

				$this->template = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'dashboard.tpl.php');
				$this->subtemplate = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'listings.tpl.php');
			} elseif ($w2dc_instance->action == 'edit_listing' && isset($_GET['listing_id']) && is_numeric($_GET['listing_id'])) {
				$listing_id = w2dc_getValue($_GET, 'listing_id');
				if ($listing_id && w2dc_current_user_can_edit_listing($listing_id)) {
					$listing = $w2dc_instance->listings_manager->loadListing($listing_id);
					
					if (isset($_POST['submit'])) {
						$errors = array();

						if (!isset($_POST['post_title']) || !trim($_POST['post_title']) || $_POST['post_title'] == __('Auto Draft')) {
							$errors[] = __('Listing title field required', 'W2DC');
							$post_title = __('Auto Draft');
						} else
							$post_title = trim($_POST['post_title']);

						$post_categories_ids = array();
						if ($listing->level->categories_number > 0 || $listing->level->unlimited_categories) {
							if ($post_categories_ids = $w2dc_instance->categories_manager->validateCategories($listing->level, $_POST, $errors)) {
								foreach ($post_categories_ids AS $key=>$id)
									$post_categories_ids[$key] = intval($id);
							}
							wp_set_object_terms($listing->post->ID, $post_categories_ids, W2DC_CATEGORIES_TAX);
						}

						if (get_option('w2dc_enable_tags')) {
							if ($post_tags_ids = $w2dc_instance->categories_manager->validateTags($_POST, $errors)) {
								foreach ($post_tags_ids AS $key=>$id)
									$post_tags_ids[$key] = intval($id);
							}
							wp_set_object_terms($listing->post->ID, $post_tags_ids, W2DC_TAGS_TAX);
						}
						
						$w2dc_instance->content_fields->saveValues($listing->post->ID, $post_categories_ids, $listing->level->id, $errors, $_POST);
						
						if ($listing->level->locations_number) {
							if ($validation_results = $w2dc_instance->locations_manager->validateLocations($listing->level, $errors)) {
								$w2dc_instance->locations_manager->saveLocations($listing->level, $listing->post->ID, $validation_results);
							}
						}
						
						if ($listing->level->images_number || $listing->level->videos_number) {
							if ($validation_results = $w2dc_instance->media_manager->validateAttachments($listing->level, $errors))
								$w2dc_instance->media_manager->saveAttachments($listing->level, $listing->post->ID, $validation_results);
						}
						
						if (get_option('w2dc_listing_contact_form') && get_option('w2dc_custom_contact_email')) {
							$w2dc_form_validation = new w2dc_form_validation();
							$w2dc_form_validation->set_rules('contact_email', __('Contact email', 'W2DC'), 'valid_email');
						
							if (!$w2dc_form_validation->run()) {
								$errors[] = $w2dc_form_validation->error_array();
							} else {
								update_post_meta($listing->post->ID, '_contact_email', $w2dc_form_validation->result_array('contact_email'));
							}
						}

						if ($errors) {
							$postarr = array(
									'ID' => $listing_id,
									'post_title' => $post_title,
									'post_content' => (isset($_POST['post_content']) ? $_POST['post_content'] : ''),
									'post_excerpt' => (isset($_POST['post_excerpt']) ? $_POST['post_excerpt'] : '')
							);
							$result = wp_update_post($postarr, true);
							if (is_wp_error($result))
								$errors[] = $result->get_error_message();

							foreach ($errors AS $error)
								w2dc_addMessage($error, 'error');
							$listing = $w2dc_instance->listings_manager->loadListing($listing_id);
						} else {
							if (!$listing->level->eternal_active_period) {
								if (get_option('w2dc_change_expiration_date') || current_user_can('manage_options'))
									$w2dc_instance->listings_manager->changeExpirationDate();
								else {
									$expiration_date = w2dc_sumDates(time(), $listing->level->active_days, $listing->level->active_months, $listing->level->active_years);
									add_post_meta($listing->post->ID, '_expiration_date', $expiration_date);
								}
							}

							if (get_option('w2dc_claim_functionality') && !get_option('w2dc_hide_claim_metabox'))
								if (isset($_POST['is_claimable']))
									update_post_meta($listing->post->ID, '_is_claimable', true);
								else
									update_post_meta($listing->post->ID, '_is_claimable', false);

							if ($listing->post->post_status == 'publish') {
								if (get_option('w2dc_fsubmit_edit_status') == 1) {
									$post_status = 'pending';
								} elseif (get_option('w2dc_fsubmit_edit_status') == 2) {
									$post_status = 'draft';
								} elseif (get_option('w2dc_fsubmit_edit_status') == 3 || !get_option('w2dc_fsubmit_edit_status')) {
									$post_status = 'publish';
								}
							}
							if (get_option('w2dc_fsubmit_edit_status') == 1) {
								$message = esc_attr__("Listing was saved successfully! Now it's awaiting moderators approval.", 'W2DC');
							} elseif (get_option('w2dc_fsubmit_edit_status') == 2) {
								$message = __('Listing was saved successfully as draft! Contact site manager, please.', 'W2DC');
							} elseif (get_option('w2dc_fsubmit_edit_status') == 3 || !get_option('w2dc_fsubmit_edit_status')) {
								$message = __('Listing was saved successfully!', 'W2DC');
							}

							$postarr = array(
									'ID' => $listing_id,
									'post_title' => $post_title,
									'post_content' => (isset($_POST['post_content']) ? $_POST['post_content'] : ''),
									'post_excerpt' => (isset($_POST['post_excerpt']) ? $_POST['post_excerpt'] : '')
							);
							if (isset($post_status))
								$postarr['post_status'] = $post_status;

							$result = wp_update_post($postarr, true);
							if (is_wp_error($result)) {
								w2dc_addMessage($result->get_error_message(), 'error');
							} else {
								w2dc_addMessage($message);
								
								if (get_option('w2dc_editlisting_admin_notification')) {
									// Wehn listing was published and became pending after modification
									if ($listing->post->post_status == 'publish' && $post_status == 'pending') {
										$author = wp_get_current_user();
	
										$subject = __('Notification about listing modification (do not reply)', 'W2DC');
										$body = str_replace('[user]', $author->display_name,
												str_replace('[listing]', $listing->title(),
												str_replace('[link]', admin_url('post.php?post='.$listing->post->ID.'&action=edit'),
										get_option('w2dc_editlisting_admin_notification'))));
			
										w2dc_mail(w2dc_get_admin_notification_email(), $subject, $body);
									}
								}
								
								if (!$this->referer || $post_status != 'publish')
									$this->referer = w2dc_dashboardUrl();
								wp_redirect($this->referer);
								die();
							}
						}
					}

					$this->template = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'dashboard.tpl.php');
					$this->subtemplate = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'edit_listing.tpl.php');
					if ($listing->level->categories_number > 0 || $listing->level->unlimited_categories) {
						add_action('wp_enqueue_scripts', array($w2dc_instance->categories_manager, 'admin_enqueue_scripts_styles'));
					}
					
					if ($listing->level->locations_number > 0) {
						add_action('wp_enqueue_scripts', array($w2dc_instance->locations_manager, 'admin_enqueue_scripts_styles'));
					}
	
					if ($listing->level->images_number > 0 || $listing->level->videos_number > 0)
						add_action('wp_enqueue_scripts', array($w2dc_instance->media_manager, 'admin_enqueue_scripts_styles'));
				}
			} elseif ($w2dc_instance->action == 'raiseup_listing' && isset($_GET['listing_id']) && is_numeric($_GET['listing_id'])) {
				$listing_id = w2dc_getValue($_GET, 'listing_id');
				if ($listing_id && w2dc_current_user_can_edit_listing($listing_id) && ($listing = $w2dc_instance->listings_manager->loadListing($listing_id)) && $listing->status == 'active') {
					$this->action = 'show';
					if (isset($_GET['raiseup_action']) && $_GET['raiseup_action'] == 'raiseup') {
						if ($listing->processRaiseUp())
							w2dc_addMessage(__('Listing was raised up successfully!', 'W2DC'));
						/* else
							w2dc_addMessage(__('An error has occurred and listing was not raised up', 'W2DC'), 'error'); */
						$this->action = $_GET['raiseup_action'];
						$this->referer = $_GET['referer'];
					}
					$this->template = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'dashboard.tpl.php');
					$this->subtemplate = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'raise_up.tpl.php');
				} else
					wp_die('You are not able to manage this listing', 'W2DC');
			} elseif ($w2dc_instance->action == 'renew_listing' && isset($_GET['listing_id']) && is_numeric($_GET['listing_id'])) {
				$listing_id = w2dc_getValue($_GET, 'listing_id');
				if ($listing_id && w2dc_current_user_can_edit_listing($listing_id) && ($listing = $w2dc_instance->listings_manager->loadListing($listing_id))) {
					$this->action = 'show';
					if (isset($_GET['renew_action']) && $_GET['renew_action'] == 'renew') {
						if ($listing->processActivate(true))
							w2dc_addMessage(__('Listing was renewed successfully!', 'W2DC'));
						/* else
							w2dc_addMessage(__('An error has occurred and listing was not renewed', 'W2DC'), 'error'); */
						$this->action = $_GET['renew_action'];
						$this->referer = $_GET['referer'];
					}
					$this->template = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'dashboard.tpl.php');
					$this->subtemplate = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'renew.tpl.php');
				} else
					wp_die('You are not able to manage this listing', 'W2DC');
			} elseif ($w2dc_instance->action == 'delete_listing' && isset($_GET['listing_id']) && is_numeric($_GET['listing_id'])) {
				$listing_id = w2dc_getValue($_GET, 'listing_id');
				if ($listing_id && w2dc_current_user_can_edit_listing($listing_id) && ($listing = $w2dc_instance->listings_manager->loadListing($listing_id))) {
					if (isset($_GET['delete_action']) && $_GET['delete_action'] == 'delete') {
						if (wp_delete_post($listing_id, true) !== FALSE) {
							$w2dc_instance->listings_manager->delete_listing_data($listing_id);

							w2dc_addMessage(__('Listing was deleted successfully!', 'W2DC'));
							wp_redirect(w2dc_dashboardUrl());
							die();
						} else 
							w2dc_addMessage(__('An error has occurred and listing was not deleted', 'W2DC'), 'error');
					}
					$this->template = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'dashboard.tpl.php');
					$this->subtemplate = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'delete.tpl.php');
				} else
					wp_die('You are not able to manage this listing', 'W2DC');
			} elseif ($w2dc_instance->action == 'upgrade_listing' && isset($_GET['listing_id']) && is_numeric($_GET['listing_id'])) {
				$listing_id = w2dc_getValue($_GET, 'listing_id');
				if ($listing_id && w2dc_current_user_can_edit_listing($listing_id) && ($listing = $w2dc_instance->listings_manager->loadListing($listing_id)) && $listing->status == 'active') {
					$this->action = 'show';
					if (isset($_GET['upgrade_action']) && $_GET['upgrade_action'] == 'upgrade') {
						$w2dc_form_validation = new w2dc_form_validation();
						$w2dc_form_validation->set_rules('new_level_id', __('New level ID', 'W2DC'), 'required|integer');

						if ($w2dc_form_validation->run()) {
							if ($listing->changeLevel($w2dc_form_validation->result_array('new_level_id')))
								w2dc_addMessage(__('Listing level was changed successfully!', 'W2DC'));
							$this->action = $_GET['upgrade_action'];
						} else
							w2dc_addMessage(__('New level must be selected!', 'W2DC'), 'error');

						$this->referer = $_GET['referer'];
					}
					$this->template = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'dashboard.tpl.php');
					$this->subtemplate = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'upgrade.tpl.php');
				} else
					wp_die('You are not able to manage this listing', 'W2DC');
			} elseif ($w2dc_instance->action == 'view_stats' && isset($_GET['listing_id']) && is_numeric($_GET['listing_id'])) {
				$listing_id = w2dc_getValue($_GET, 'listing_id');
				if (get_option('w2dc_enable_stats') && $listing_id && w2dc_current_user_can_edit_listing($listing_id) && ($listing = $w2dc_instance->listings_manager->loadListing($listing_id))) {
					add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'));
					$this->template = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'dashboard.tpl.php');
					$this->subtemplate = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'view_stats.tpl.php');
				} else
					wp_die('You are not able to manage this listing', 'W2DC');
			} elseif ($w2dc_instance->action == 'profile') {
				if (get_option('w2dc_allow_edit_profile')) {
					$user_id = get_current_user_id();
					$current_user = wp_get_current_user();
	
					include_once ABSPATH . 'wp-admin/includes/user.php';
	
					if (isset($_POST['user_id'])) {
						if ($_POST['user_id'] == $user_id) {
							global $wpdb;
		
							if (!is_multisite()) {
								$errors = edit_user($user_id);
								update_user_meta($user_id, 'w2dc_billing_name', $_POST['w2dc_billing_name']);
								update_user_meta($user_id, 'w2dc_billing_address', $_POST['w2dc_billing_address']);
							} else {
								$user = get_userdata($user_id);
							
								// Update the email address in signups, if present.
								if ($user->user_login && isset($_POST['email']) && is_email($_POST['email']) && $wpdb->get_var($wpdb->prepare("SELECT user_login FROM {$wpdb->signups} WHERE user_login = %s", $user->user_login)))
									$wpdb->query($wpdb->prepare("UPDATE {$wpdb->signups} SET user_email = %s WHERE user_login = %s", $_POST['email'], $user->user_login));
							
								// We must delete the user from the current blog if WP added them after editing.
								$delete_role = false;
								$blog_prefix = $wpdb->get_blog_prefix();
								if ($user_id != $current_user->ID) {
									$cap = $wpdb->get_var("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = '{$user_id}' AND meta_key = '{$blog_prefix}capabilities' AND meta_value = 'a:0:{}'");
									if (!is_network_admin() && null == $cap && $_POST['role'] == '') {
										$_POST['role'] = 'contributor';
										$delete_role = true;
									}
								}
								if (!isset($errors) || (isset($errors) && is_object($errors) && false == $errors->get_error_codes()))
									$errors = edit_user($user_id);
								if ( $delete_role ) // stops users being added to current blog when they are edited
									delete_user_meta($user_id, $blog_prefix . 'capabilities');
							}
							
							if (!is_wp_error($errors)) {
								w2dc_addMessage(__('Your profile was successfully updated!', 'W2DC'));
								wp_redirect(w2dc_dashboardUrl(array('w2dc_action' => 'profile')));
								die();
							}
						} else
							wp_die('You are not able to manage profile', 'W2DC');
					}
	
					$this->user = get_user_to_edit($user_id);
	
					wp_enqueue_script('password-strength-meter');
					wp_enqueue_script('user-profile');
					
					$this->template = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'dashboard.tpl.php');
					$this->subtemplate = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'profile.tpl.php');
					$this->active_tab = 'profile';
				} else
					wp_die('You are not able to manage profile', 'W2DC');
			} elseif ($w2dc_instance->action == 'claim_listing' && isset($_GET['listing_id']) && is_numeric($_GET['listing_id'])) {
				$listing_id = w2dc_getValue($_GET, 'listing_id');
				if ($listing_id && ($listing = $w2dc_instance->listings_manager->loadListing($listing_id)) && $listing->is_claimable) {
					$claimer_id = get_current_user_id();
					if ($listing->post->post_author != $claimer_id) {
						$this->action = 'show';
						if (isset($_GET['claim_action']) && $_GET['claim_action'] == 'claim') {
							if (isset($_POST['claim_message']) && $_POST['claim_message'])
								$claimer_message = $_POST['claim_message'];
							else
								$claimer_message = '';
							if ($listing->claim->updateRecord($claimer_id, $claimer_message, 'pending')) {
								update_post_meta($listing->post->ID, '_is_claimable', false);
								if (get_option('w2dc_claim_approval')) {
									if (get_option('w2dc_claim_notification')) {
										$author = get_userdata($listing->post->post_author);
										$claimer = get_userdata($claimer_id);

										$subject = __('Claim notification', 'W2DC');
		
										$body = str_replace('[author]', $author->display_name,
												str_replace('[listing]', $listing->post->post_title,
												str_replace('[claimer]', $claimer->display_name,
												str_replace('[link]', w2dc_dashboardUrl(array('listing_id' => $listing->post->ID, 'w2dc_action' => 'process_claim')),
												str_replace('[message]', $claimer_message,
										get_option('w2dc_claim_notification'))))));
		
										w2dc_mail($author->user_email, $subject, $body);
									}
									w2dc_addMessage(__('Listing was claimed successfully!', 'W2DC'));
								} else {
									// Automatically process claim without approval
									$listing->claim->approve();
									w2dc_addMessage(__('Listing claim was approved successfully!', 'W2DC'));
								}
							}

							$this->action = $_GET['claim_action'];
						}
						$this->template = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'dashboard.tpl.php');
						$this->subtemplate = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'claim.tpl.php');
					} else
						wp_die('This is your own listing', 'W2DC');
				}
			} elseif ($w2dc_instance->action == 'process_claim' && isset($_GET['listing_id']) && is_numeric($_GET['listing_id'])) {
				$listing_id = w2dc_getValue($_GET, 'listing_id');
				if ($listing_id && w2dc_current_user_can_edit_listing($listing_id) && ($listing = $w2dc_instance->listings_manager->loadListing($listing_id)) && $listing->claim->isClaimed()) {
					$this->action = 'show';
					if (isset($_GET['claim_action']) && ($_GET['claim_action'] == 'approve' || $_GET['claim_action'] == 'decline')) {
						if ($_GET['claim_action'] == 'approve') {
							$listing->claim->approve();
							if (get_option('w2dc_claim_approval_notification')) {
								$claimer = get_userdata($listing->claim->claimer_id);

								$subject = __('Approval of claim notification', 'W2DC');
							
								$body = str_replace('[claimer]', $claimer->display_name,
										str_replace('[listing]', $listing->post->post_title,
										str_replace('[link]', w2dc_dashboardUrl(),
								get_option('w2dc_claim_approval_notification'))));
							
								w2dc_mail($claimer->user_email, $subject, $body);
							}
							w2dc_addMessage(__('Listing claim was approved successfully!', 'W2DC'));
						} elseif ($_GET['claim_action'] == 'decline') {
							$listing->claim->deleteRecord();
							if (get_option('w2dc_claim_decline_notification')) {
								$claimer = get_userdata($listing->claim->claimer_id);

								$subject = __('Claim decline notification', 'W2DC');
									
								$body = str_replace('[claimer]', $claimer->display_name,
										str_replace('[listing]', $listing->post->post_title,
										get_option('w2dc_claim_decline_notification')));
									
								w2dc_mail($claimer->user_email, $subject, $body);
							}
							update_post_meta($listing->post->ID, '_is_claimable', true);
							w2dc_addMessage(__('Listing claim was declined!', 'W2DC'));
						}
						$this->action = 'processed';
						$this->referer = $_GET['referer'];
					}

					$this->template = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'dashboard.tpl.php');
					$this->subtemplate = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'claim_process.tpl.php');
				} else
					wp_die('You are not able to manage this listing', 'W2DC');
			// adapted for WPML
			}  elseif (function_exists('wpml_object_id_filter') && $sitepress && get_option('w2dc_enable_frontend_translations') && $w2dc_instance->action == 'add_translation' && isset($_GET['listing_id']) && isset($_GET['to_lang'])) {
				$master_post_id = $_GET['listing_id'];
				$lang_code = $_GET['to_lang'];

				global $iclTranslationManagement;

				require_once( ICL_PLUGIN_PATH . '/inc/translation-management/translation-management.class.php' );
				if (!isset($iclTranslationManagement))
					$iclTranslationManagement = new TranslationManagement;
				
				$post_type = get_post_type($master_post_id);
				if ($sitepress->is_translated_post_type($post_type)) {
					// WPML has special option sync_post_status, that controls post_status duplication
					if ($new_listing_id = $iclTranslationManagement->make_duplicate($master_post_id, $lang_code)) {
						$iclTranslationManagement->reset_duplicate_flag($new_listing_id);
						w2dc_addMessage(__('Translation was successfully created!', 'W2DC'));
						do_action('wpml_switch_language', $lang_code);
						wp_redirect(add_query_arg(array('w2dc_action' => 'edit_listing', 'listing_id' => $new_listing_id), get_permalink(apply_filters('wpml_object_id', $w2dc_instance->dashboard_page_id, 'page', true, $lang_code))));
					} else {
						w2dc_addMessage(__('Translation was not created!', 'W2DC'), 'error');
						wp_redirect(w2dc_dashboardUrl());
					}
					die();
				}
			}
		}

		apply_filters('w2dc_frontend_controller_construct', $this);
	}

	public function display() {
		$output =  w2dc_renderTemplate($this->template, array('frontend_controller' => $this), true);
		wp_reset_postdata();

		return $output;
	}

	public function add_claimed_listings_where($where = '') {
		global $wpdb;
		
		$claimed_posts = '';
		$claimed_posts_ids = array();

		$results = $wpdb->get_results("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_claimer_id' AND meta_value='" . get_current_user_id() . "'", ARRAY_A);
		foreach ($results AS $row)
			$claimed_posts_ids[] = $row['post_id'];
		if ($claimed_posts_ids)
			$claimed_posts = " OR {$wpdb->posts}.ID IN (".implode(',', $claimed_posts_ids).") ";
		$where .= " AND ({$wpdb->posts}.post_author IN (".get_current_user_id().")" . $claimed_posts . ")";
		
		return $where;
	}
	
	public function enqueue_scripts_styles() {
		wp_register_script('w2dc_stats', W2DC_RESOURCES_URL . 'js/chart.min.js');
		wp_enqueue_script('w2dc_stats');
	}
}

?>