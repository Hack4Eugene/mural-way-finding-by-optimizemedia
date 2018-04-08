<?php 

class w2dc_media_manager {
	
	public function __construct() {
		global $pagenow;

		if ($pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'admin-ajax.php') {
			add_action('add_meta_boxes', array($this, 'addMediaMetabox'));

			add_action('wp_ajax_w2dc_upload_image', array($this, 'upload_image'));
			add_action('wp_ajax_nopriv_w2dc_upload_image', array($this, 'upload_image'));

			add_action('wp_ajax_w2dc_upload_media_image', array($this, 'upload_media_image'));
		}
		// This is really strange thing, that users may see ANY attachments owned by other users, so we need this hack
		add_filter('pre_get_posts', array($this, 'prevent_users_see_other_media'));
	}

	public function prevent_users_see_other_media($wp_query) {
		global $current_user;
		if (is_admin() && $current_user && !current_user_can('edit_others_posts') && isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] == 'attachment') {
			$wp_query->set('author', $current_user->ID);
			add_filter('views_upload', array($this, 'fix_media_counts'));
		}
	}
	public function fix_media_counts($views) {
		global $wpdb, $current_user, $post_mime_types, $avail_post_mime_types;
		$views = array();
		$count = $wpdb->get_results( "
				SELECT post_mime_type, COUNT( * ) AS num_posts
				FROM {$wpdb->posts}
				WHERE post_type = 'attachment'
				AND post_author = $current_user->ID
				AND post_status != 'trash'
				GROUP BY post_mime_type
				", ARRAY_A );
		foreach($count as $row)
			$_num_posts[$row['post_mime_type']] = $row['num_posts'];
		$_total_posts = array_sum($_num_posts);
		$detached = isset($_REQUEST['detached']) || isset($_REQUEST['find_detached']);
		if (!isset($total_orphans))
			$total_orphans = $wpdb->get_var("
					SELECT COUNT( * )
					FROM {$wpdb->posts}
					WHERE post_type = 'attachment'
					AND post_author = $current_user->ID
					AND post_status != 'trash'
					AND post_parent < 1
					");
		$matches = wp_match_mime_types(array_keys($post_mime_types), array_keys($_num_posts));
		foreach ($matches as $type => $reals)
			foreach ($reals as $real)
			$num_posts[$type] = (isset($num_posts[$type])) ? $num_posts[$type] + $_num_posts[$real] : $_num_posts[$real];
		$class = (empty($_GET['post_mime_type']) && !$detached && !isset($_GET['status'])) ? ' class="current"' : '';
		$views['all'] = "<a href='upload.php'$class>" . sprintf(__('All <span class="count">(%s)</span>', 'W2DC'), number_format_i18n($_total_posts)) . '</a>';
		foreach ($post_mime_types as $mime_type => $label) {
			$class = '';
			if (!wp_match_mime_types($mime_type, $avail_post_mime_types))
				continue;
			if (!empty($_GET['post_mime_type']) && wp_match_mime_types($mime_type, $_GET['post_mime_type']))
				$class = ' class="current"';
			if (!empty( $num_posts[$mime_type]))
				$views[$mime_type] = "<a href='upload.php?post_mime_type=$mime_type'$class>" . sprintf(translate_nooped_plural($label[2], $num_posts[$mime_type]), $num_posts[$mime_type]) . '</a>';
		}
		$views['detached'] = '<a href="upload.php?detached=1"' . ($detached ? ' class="current"' : '') . '>' . sprintf(__( 'Unattached <span class="count">(%s)</span>', 'W2DC'), $total_orphans) . '</a>';
		return $views;
	}

	public function addMediaMetabox($post_type) {
		if ($post_type == W2DC_POST_TYPE && ($level = w2dc_getCurrentListingInAdmin()->level) && ($level->images_number > 0 || $level->videos_number > 0)) {
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts_styles'));

			add_meta_box('w2dc_media_metabox',
					__('Listing media', 'W2DC'),
					array($this, 'mediaMetabox'),
					W2DC_POST_TYPE,
					'normal',
					'high');
		}
	}

	public function mediaMetabox() {
		$listing = w2dc_getCurrentListingInAdmin();

		w2dc_renderTemplate('media_metabox.tpl.php', array('listing' => $listing));
	}

	public function upload_image() {
		global $w2dc_instance;

		$result = array('error_msg' => '', 'uploaded_file' => '');

		if (wp_verify_nonce($_GET['_wpnonce'], 'upload_images') && isset($_FILES['browse_file']) && ($_FILES['browse_file']['size'] > 0) && isset($_GET['post_id']) && ($post_id = $_GET['post_id'])) {
			if (($listing = $w2dc_instance->listings_manager->loadListing($post_id))) {
				if (w2dc_current_user_can_edit_listing($post_id) || !$listing->post->post_author) {
					if (!$existed_images = get_post_meta($post_id, '_attached_image'))
						$existed_images_count = 0;
					else 
						$existed_images_count = count($existed_images);
	
					// Check if available count of images was not exceeded
					//if ($listing->level->images_number > $existed_images_count) {
						// Get the type of the uploaded file. This is returned as "type/extension"
						$arr_file_type = wp_check_filetype(basename($_FILES['browse_file']['name']));
						$uploaded_file_type = $arr_file_type['type'];
					
						// Set an array containing a list of acceptable formats
						$allowed_file_types = array('image/jpg', 'image/jpeg', 'image/gif', 'image/png');
					
						// If the uploaded file is the right format
						if (in_array($uploaded_file_type, $allowed_file_types)) {
							// Options array for the wp_handle_upload function. 'test_upload' => false
							$upload_overrides = array('test_form' => false);
					
							// Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array
							$uploaded_file = wp_handle_upload($_FILES['browse_file'], $upload_overrides);
			
							// If the wp_handle_upload call returned a local path for the image
							if (isset($uploaded_file['file'])) {
								// The wp_insert_attachment function needs the literal system path, which was passed back from wp_handle_upload
								$file_name_and_location = $uploaded_file['file'];
			
								// Set up options array to add this file as an attachment
								$attachment = array(
										'post_mime_type' => $uploaded_file_type,
										'post_title' => '',
										'post_content' => '',
										'post_status' => 'inherit'
								);
	
								$real_crop_option = get_option('thumbnail_crop');
								if (!isset($_GET['crop']) && get_option('thumbnail_crop'))
									update_option('thumbnail_crop', 0);
								elseif (isset($_GET['crop']) && !get_option('thumbnail_crop'))
									update_option('thumbnail_crop', 1);
			
								// Run the wp_insert_attachment function. This adds the file to the media library and generates the thumbnails. If you wanted to attch this image to a post, you could pass the post id as a third param and it'd magically happen.
								$attach_id = wp_insert_attachment($attachment, $file_name_and_location, $post_id);
								require_once(ABSPATH . 'wp-admin/includes/image.php');
								$attach_data = wp_generate_attachment_metadata($attach_id, $file_name_and_location);
								wp_update_attachment_metadata($attach_id,  $attach_data);
								
								update_option('thumbnail_crop', $real_crop_option);
			
								// insert attachment ID to the post meta
								add_post_meta($post_id, '_attached_image', $attach_id);
								
								$src = wp_get_attachment_image_src($attach_id, 'thumbnail');
								
								$result['uploaded_file'] = $src[0];
								$result['attachment_id'] = $attach_id;
							} else // wp_handle_upload returned some kind of error. the return does contain error details, so you can use it here if you want.
								$result['error_msg'] = 'There was a problem with your upload: ' . $uploaded_file['error'];
						} else // wrong file type
							$result['error_msg'] = __('Please upload only image files (jpg, gif or png).', 'W2DC');
					//} else
						//$result['error_msg'] = __('Can not upload images for this listing.', 'W2DC');
				} else
					$result['error_msg'] = __('You do not have permissions to edit this listing!', 'W2DC');
			} else
				$result['error_msg'] = __('Wrong listing ID.', 'W2DC');
		} else // no file was passed
			$result['error_msg'] = __('Choose image to upload first!', 'W2DC');
		
		echo json_encode($result);
		die();
	}
	
	public function upload_media_image() {
		global $w2dc_instance;

		if (wp_verify_nonce($_POST['_wpnonce'], 'upload_images') && isset($_POST['attachment_id']) && isset($_POST['post_id']) && ($post_id = $_POST['post_id'])) {
			if (($listing = $w2dc_instance->listings_manager->loadListing($post_id))) {
				if (w2dc_current_user_can_edit_listing($post_id)) {
					// insert attachment ID to the post meta
					add_post_meta($post_id, '_attached_image', $_POST['attachment_id']);
				}
			}
		}
		
		die();
	}
	
	public function validateAttachments($level, &$errors) {
		if ($level->images_number || $level->videos_number) {
			$validation = new w2dc_form_validation();
			if ($level->images_number) {
				$validation->set_rules('attached_image_id[]', __('Listing images ID', 'W2DC'));
				$validation->set_rules('attached_image_title[]', __('Listing images caption', 'W2DC'));
				if ($level->logo_enabled)
					$validation->set_rules('attached_image_as_logo', __('Logo image selection', 'W2DC'));
			}
			if ($level->videos_number) {
				$validation->set_rules('attached_video_id[]', __('Listing video ID', 'W2DC'));
			}
	
			if (!$validation->run())
				$errors[] = $validation->error_array();
			
			return $validation->result_array();
		}
	}
	
	public function saveAttachments($level, $post_id, $validation_results) {
		if ($level->images_number) {
			if (!$existed_images = get_post_meta($post_id, '_attached_image'))
				$existed_images = array();

			if ($validation_results['attached_image_id[]']) {
				// remove unauthorized images
				$validation_results['attached_image_id[]'] = array_slice($validation_results['attached_image_id[]'], 0, $level->images_number, true);
	
				foreach ($validation_results['attached_image_id[]'] AS $key=>$attachment_id) {
					if (in_array($attachment_id, $existed_images))
						unset($existed_images[array_search($attachment_id, $existed_images)]);
					
					// adapted for WPML
					global $sitepress;
					if (function_exists('wpml_object_id_filter') && $sitepress)
						$attachment_id = apply_filters('wpml_object_id', $attachment_id, 'attachment', true);
		
					wp_update_post(array('ID' => $attachment_id, 'post_title' => $validation_results['attached_image_title[]'][$key]));
				}
			}

			foreach ($existed_images AS $attachment_id) {
				//wp_delete_attachment($attachment_id, true);
				delete_post_meta($post_id, '_attached_image', $attachment_id);
			}
	
			if ($level->logo_enabled) {
				if (isset($validation_results['attached_image_as_logo']) && is_numeric($validation_results['attached_image_as_logo']))
					$logo_id = $validation_results['attached_image_as_logo'];
				elseif ($existed_images = get_post_meta($post_id, '_attached_image'))
					$logo_id = $existed_images[0];
		
				if (isset($logo_id)) {
					update_post_meta($post_id, '_attached_image_as_logo', $logo_id);
					update_post_meta($post_id, '_thumbnail_id', $logo_id);
				} else
					delete_post_meta($post_id, '_attached_image_as_logo');
			}
		}

		delete_post_meta($post_id, '_attached_video_id');
		if ($level->videos_number && $validation_results['attached_video_id[]']) {
			// remove unauthorized videos
			$validation_results['attached_video_id[]'] = array_slice($validation_results['attached_video_id[]'], 0, $level->videos_number, true);

			foreach ($validation_results['attached_video_id[]'] AS $key=>$video_id) {
				add_post_meta($post_id, '_attached_video_id', $video_id);
			}
		}
	}
	
	public function admin_enqueue_scripts_styles() {
		if (get_option('w2dc_images_lightbox') || is_admin()) {
			wp_enqueue_style('w2dc_media_styles');
			wp_enqueue_script('w2dc_media_scripts_lightbox');
		}
		
		if (current_user_can('upload_files'))
			wp_enqueue_media();
		else 
			wp_enqueue_script('w2dc_media_scripts');
	}
}

?>