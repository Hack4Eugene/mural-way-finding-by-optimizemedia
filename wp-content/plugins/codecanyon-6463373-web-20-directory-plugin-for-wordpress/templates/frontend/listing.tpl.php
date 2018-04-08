		<?php if ($listing->level->logo_enabled && ($listing->logo_image || (get_option('w2dc_enable_nologo') && get_option('w2dc_nologo_url')))): ?>
		<div class="w2dc-pull-left w2dc-listing-logo-wrap w2dc-anim-style-<?php echo $listing->logo_animation_effect; ?>">
			<?php do_action('w2dc_listing_pre_logo_wrap_html', $listing); ?>
			<figure class="w2dc-listing-logo <?php if ($listing->level->listings_own_page): ?>w2dc-listings-own-page<?php endif; ?>">
				<?php if ($listing->level->listings_own_page): ?>
				<a href="<?php the_permalink(); ?>" class="w2dc-listing-logo-img-wrap" <?php if ($listing->level->nofollow): ?>rel="nofollow"<?php endif; ?>>
				<?php else: ?>
				<div class="w2dc-listing-logo-img-wrap">
				<?php endif; ?>
				<?php if ($listing->logo_image): ?>
					<?php $img_src = $listing->get_logo_url(array(800, 600)); ?>
				<?php else: ?>
					<?php $img_src = get_option('w2dc_nologo_url'); ?>
				<?php endif; ?>
					<div class="w2dc-listing-logo-img" style="background-image: url('<?php echo $img_src; ?>');">
						<img src="<?php echo $img_src; ?>" />
					</div>
				<?php if ($listing->level->listings_own_page): ?>
				</a>
				<?php else: ?>
				</div>
				<?php endif; ?>
				<?php if ($listing->level->listings_own_page): ?>
				<figcaption>
					<div class="w2dc-figcaption">
						<div class="w2dc-figcaption-middle">
							<ul class="w2dc-figcaption-options">
								<li class="w2dc-listing-figcaption-option">
									<a href="<?php the_permalink(); ?>" <?php if ($listing->level->nofollow): ?>rel="nofollow"<?php endif; ?>>
										<span class="w2dc-glyphicon w2dc-glyphicon-play" title="<?php esc_attr_e('more info >>', 'W2DC'); ?>"></span>
									</a>
								</li>
					
								<?php if ($listing->level->google_map && $listing->isMap() && $listing->locations): ?>
								<li class="w2dc-listing-figcaption-option" style="display: none;">
									<a href="javascript:void(0);" class="w2dc-show-on-map" data-location-id="<?php echo $listing->locations[0]->id; ?>">
										<span class="w2dc-glyphicon w2dc-glyphicon-map-marker" title="<?php esc_attr_e('view on map', 'W2DC'); ?>"></span>
									</a>
								</li>
								<?php endif; ?>
					
								<?php if (w2dc_comments_open() && !get_option('w2dc_hide_comments_number_on_index')): ?>
								<li class="w2dc-listing-figcaption-option">
									<a href="<?php the_permalink(); ?>#comments-tab" <?php if ($listing->level->nofollow): ?>rel="nofollow"<?php endif; ?>>
										<span class="w2dc-glyphicon w2dc-glyphicon-comment" title="<?php echo sprintf(_n('%d reply', '%d replies', $listing->post->comment_count, 'W2DC'), $listing->post->comment_count); ?>"></span>
									</a>
								</li>
								<?php endif; ?>
					
								<?php if ($listing->level->images_number && count($listing->images) > 1): ?>
								<li class="w2dc-listing-figcaption-option">
									<a href="<?php the_permalink(); ?>#images" <?php if ($listing->level->nofollow): ?>rel="nofollow"<?php endif; ?>>
										<span class="w2dc-glyphicon w2dc-glyphicon-picture" title="<?php echo sprintf(_n('%d image', '%d images', count($listing->images), 'W2DC'), count($listing->images)); ?>"></span>
									</a>
								</li>
								<?php endif; ?>
					
								<?php if ($listing->level->videos_number && $listing->videos): ?>
								<li class="w2dc-listing-figcaption-option">
									<a href="<?php the_permalink(); ?>#videos-tab" <?php if ($listing->level->nofollow): ?>rel="nofollow"<?php endif; ?>>
										<span class="w2dc-glyphicon w2dc-glyphicon-facetime-video" title="<?php echo sprintf(_n('%d video', '%d videos', count($listing->videos), 'W2DC'), count($listing->videos)); ?>"></span>
									</a>
								</li>
								<?php endif; ?>
					
								<?php if (get_option('w2dc_listing_contact_form') && (!$listing->is_claimable || !get_option('w2dc_hide_claim_contact_form')) && ($listing_owner = get_userdata($listing->post->post_author)) && $listing_owner->user_email): ?>
								<li class="w2dc-listing-figcaption-option">
									<a href="<?php the_permalink(); ?>#contact-tab" <?php if ($listing->level->nofollow): ?>rel="nofollow"<?php endif; ?>>
										<span class="w2dc-glyphicon w2dc-glyphicon-user" title="<?php esc_attr_e('contact us', 'W2DC'); ?>"></span>
									</a>
								</li>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				</figcaption>
				<?php endif; ?>
			</figure>
		</div>
		<?php endif; ?>

		<?php if ($listing->level->sticky && ($w2dc_instance->order_by_date || get_option('w2dc_orderby_sticky_featured'))): ?>
		<div class="w2dc-sticky-icon" title="<?php esc_attr_e('sticky listing', 'W2DC'); ?>"></div>
		<?php endif; ?>

		<?php if ($w2dc_instance->getShortcodeProperty('webdirectory', 'is_favourites') && w2dc_checkQuickList($listing->post->ID)): ?>
		<div class="w2dc-remove-from-favourites-list" listingid="<?php the_ID(); ?>" title="<?php echo esc_attr(__('Remove from favourites list', 'W2DC')); ?>"></div>
		<?php endif; ?>

		<div class="w2dc-clearfix <?php if ($listing->level->logo_enabled && ($listing->logo_image || (get_option('w2dc_enable_nologo') && get_option('w2dc_nologo_url')))): ?>w2dc-listing-text-content-wrap<?php else: ?>w2dc-listing-text-content-wrap-nologo<?php endif; ?>">
			<header class="w2dc-listing-header">
				<?php if (!$listing->level->listings_own_page): ?>
				<h2><?php echo $listing->title(); ?></h2><?php do_action('w2dc_listing_title_html', $listing, false); ?>
				<?php else: ?>
				<h2><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr($listing->title()); ?>" <?php if ($listing->level->nofollow): ?>rel="nofollow"<?php endif; ?>><?php echo $listing->title(); ?></a> <?php do_action('w2dc_listing_title_html', $listing, false); ?></h2>
				<?php endif; ?>
				<?php if (!get_option('w2dc_hide_listings_creation_date')): ?>
				<em class="w2dc-listing-date" datetime="<?php echo date("Y-m-d", mysql2date('U', $listing->post->post_date)); ?>T<?php echo date("H:i", mysql2date('U', $listing->post->post_date)); ?>"><?php echo get_the_date(); ?> <?php echo get_the_time(); ?></em>
				<?php endif; ?>
			</header>
			
			<?php do_action('w2dc_listing_pre_content_html', $listing); ?>

			<?php $listing->renderContentFields(false); ?>

			<?php do_action('w2dc_listing_post_content_html', $listing); ?>
		</div>