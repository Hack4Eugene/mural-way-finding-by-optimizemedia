<div class="w2dc-content">
	<?php w2dc_renderMessages(); ?>

	<h2><?php echo sprintf(__('Edit listing "%s"', 'W2DC'), $w2dc_instance->current_listing->title()); ?></h2>

	<form action="" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="referer" value="<?php echo $frontend_controller->referer; ?>" />
		<?php wp_nonce_field('w2dc_submit', '_submit_nonce'); ?>

		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Listing title', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></h3>
			<div class="w2dc-submit-section-inside">
				<input type="text" name="post_title" style="width: 100%" class="w2dc-form-control" value="<?php if ($w2dc_instance->current_listing->post->post_title != __('Auto Draft')) echo esc_attr($w2dc_instance->current_listing->post->post_title); ?>" />
			</div>
		</div>

		<?php if (post_type_supports(W2DC_POST_TYPE, 'editor')): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php echo $w2dc_instance->content_fields->getContentFieldBySlug('content')->name; ?><?php if ($w2dc_instance->content_fields->getContentFieldBySlug('content')->is_required): ?><span class="w2dc-red-asterisk">*</span><?php endif; ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php wp_editor($w2dc_instance->current_listing->post->post_content, 'post_content', array('media_buttons' => false, 'editor_class' => 'w2dc-editor-class')); ?>
			</div>
			<?php if ($w2dc_instance->content_fields->getContentFieldBySlug('content')->description): ?><p class="description"><?php echo $w2dc_instance->content_fields->getContentFieldBySlug('content')->description; ?></p><?php endif; ?>
		</div>
		<?php endif; ?>

		<?php if (post_type_supports(W2DC_POST_TYPE, 'excerpt')): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php echo $w2dc_instance->content_fields->getContentFieldBySlug('summary')->name; ?><?php if ($w2dc_instance->content_fields->getContentFieldBySlug('summary')->is_required): ?><span class="w2dc-red-asterisk">*</span><?php endif; ?></h3>
			<div class="w2dc-submit-section-inside">
				<textarea name="post_excerpt" class="w2dc-form-control" rows="4"><?php echo esc_textarea($w2dc_instance->current_listing->post->post_excerpt)?></textarea>
			</div>
			<?php if ($w2dc_instance->content_fields->getContentFieldBySlug('summary')->description): ?><p class="description"><?php echo $w2dc_instance->content_fields->getContentFieldBySlug('summary')->description; ?></p><?php endif; ?>
		</div>
		<?php endif; ?>
		
		<?php do_action('w2dc_edit_listing_metaboxes_pre', $w2dc_instance->current_listing); ?>

		<?php if (!$w2dc_instance->current_listing->level->eternal_active_period && (get_option('w2dc_change_expiration_date') || current_user_can('manage_options'))): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Listing expiration date', 'W2DC'); ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php $w2dc_instance->listings_manager->listingExpirationDateMetabox($w2dc_instance->current_listing->post); ?>
			</div>
		</div>
		<?php endif; ?>
		
		<?php if (get_option('w2dc_listing_contact_form') && get_option('w2dc_custom_contact_email')): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Contact email', 'W2DC'); ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php $w2dc_instance->listings_manager->listingContactEmailMetabox($w2dc_instance->current_listing->post); ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if (get_option('w2dc_claim_functionality') && !get_option('w2dc_hide_claim_metabox')): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Listing claim', 'W2DC'); ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php $w2dc_instance->listings_manager->listingClaimMetabox($w2dc_instance->current_listing->post); ?>
			</div>
		</div>
		<?php endif; ?>
	
		<?php if ($w2dc_instance->current_listing->level->categories_number > 0 || $w2dc_instance->current_listing->level->unlimited_categories): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php echo $w2dc_instance->content_fields->getContentFieldBySlug('categories_list')->name; ?><?php if ($w2dc_instance->content_fields->getContentFieldBySlug('categories_list')->is_required): ?><span class="w2dc-red-asterisk">*</span><?php endif; ?></h3>
			<div class="w2dc-submit-section-inside">
				<a href="javascript:void(0);" class="w2dc-expand-terms"><?php _e('Expand All', 'W2DC'); ?></a> | <a href="javascript:void(0);" class="w2dc-collapse-terms"><?php _e('Collapse All', 'W2DC'); ?></a>
				<div class="w2dc-categories-tree-panel w2dc-editor-class" id="<?php echo W2DC_CATEGORIES_TAX; ?>-all">
					<?php w2dc_terms_checklist($w2dc_instance->current_listing->post->ID); ?>
				</div>
				<?php if ($w2dc_instance->content_fields->getContentFieldBySlug('categories_list')->description): ?><p class="description"><?php echo $w2dc_instance->content_fields->getContentFieldBySlug('categories_list')->description; ?></p><?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if (get_option('w2dc_enable_tags')): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php echo $w2dc_instance->content_fields->getContentFieldBySlug('listing_tags')->name; ?> <i>(<?php _e('select existing or type new', 'W2DC'); ?>)</i></h3>
			<div class="w2dc-submit-section-inside">
				<?php w2dc_tags_selectbox($w2dc_instance->current_listing->post->ID); ?>
				<?php if ($w2dc_instance->content_fields->getContentFieldBySlug('listing_tags')->description): ?><p class="description"><?php echo $w2dc_instance->content_fields->getContentFieldBySlug('listing_tags')->description; ?></p><?php endif; ?>
			</div>
		</div>
		<?php endif; ?>
	
		<?php if ($w2dc_instance->content_fields->isNotCoreContentFields()): ?>
		<div class="w2dc-submit-section">
			<div class="w2dc-submit-section-inside">
				<?php $w2dc_instance->content_fields_manager->contentFieldsMetabox($w2dc_instance->current_listing->post); ?>
			</div>
		</div>
		<?php endif; ?>
	
		<?php if ($w2dc_instance->current_listing->level->images_number > 0 || $w2dc_instance->current_listing->level->videos_number > 0): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Listing Media', 'W2DC'); ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php $w2dc_instance->media_manager->mediaMetabox(); ?>
			</div>
		</div>
		<?php endif; ?>
	
		<?php if ($w2dc_instance->current_listing->level->locations_number > 0): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Listing locations', 'W2DC'); ?><?php if ($w2dc_instance->content_fields->getContentFieldBySlug('address')->is_required): ?><span class="w2dc-red-asterisk">*</span><?php endif; ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php if ($w2dc_instance->content_fields->getContentFieldBySlug('address')->description): ?><p class="description"><?php echo $w2dc_instance->content_fields->getContentFieldBySlug('address')->description; ?></p><?php endif; ?>
				<?php $w2dc_instance->locations_manager->listingLocationsMetabox($w2dc_instance->current_listing->post); ?>
			</div>
		</div>
		<?php endif; ?>
		
		<?php do_action('w2dc_edit_listing_metaboxes_post', $w2dc_instance->current_listing); ?>

		<?php require_once(ABSPATH . 'wp-admin/includes/template.php'); ?>
		<?php submit_button(__('Save changes', 'W2DC'), 'w2dc-btn w2dc-btn-primary', 'submit', false); ?>
		&nbsp;&nbsp;&nbsp;
		<?php submit_button(__('Cancel', 'W2DC'), 'w2dc-btn w2dc-btn-primary', 'cancel', false); ?>
	</form>
</div>