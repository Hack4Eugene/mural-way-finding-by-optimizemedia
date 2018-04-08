		<?php if (!isset($frontend_controller->custom_home) || !$frontend_controller->custom_home || (($frontend_controller->custom_home && get_option('w2dc_listings_on_index')) || !$frontend_controller->is_home)): ?>
		<div class="w2dc-content" id="w2dc-controller-<?php echo $frontend_controller->hash; ?>" data-controller-hash="<?php echo $frontend_controller->hash; ?>" <?php if (isset($frontend_controller->custom_home) && $frontend_controller->custom_home): ?>data-custom-home="1"<?php endif; ?>>
			<?php if ($frontend_controller->request_by != 'directory_controller'): ?><style type="text/css" scoped><?php if ($frontend_controller->args['listing_thumb_width']): ?>@media screen and (min-width: 800px) { <?php if (!is_rtl()): ?> #w2dc-controller-<?php echo $frontend_controller->hash; ?> .w2dc-listings-block .w2dc-listing-logo-wrap { width: <?php echo $frontend_controller->args['listing_thumb_width']; ?>px; <?php if ($frontend_controller->args['wrap_logo_list_view']): ?>margin-right: 20px; margin-bottom: 10px;<?php endif; ?> } <?php else: ?> #w2dc-controller-<?php echo $frontend_controller->hash; ?> .w2dc-listings-block .w2dc-listing-logo-wrap { margin-left: 20px; margin-right: 0; } <?php endif; ?> #w2dc-controller-<?php echo $frontend_controller->hash; ?> .w2dc-listings-block figure.w2dc-listing-logo .w2dc-listing-logo-img img { width: <?php echo $frontend_controller->args['listing_thumb_width']; ?>px; } <?php if (!is_rtl()): ?> #w2dc-controller-<?php echo $frontend_controller->hash; ?> .w2dc-listings-block .w2dc-listing-text-content-wrap { <?php if (!$frontend_controller->args['wrap_logo_list_view']): ?>margin-left: <?php echo $frontend_controller->args['listing_thumb_width']; ?>px; margin-right: 0;<?php else: ?>margin-left: 0;<?php endif; ?> } <?php else: ?> #w2dc-controller-<?php echo $frontend_controller->hash; ?> .w2dc-listings-block .w2dc-listing-text-content-wrap { <?php if (!$frontend_controller->args['wrap_logo_list_view']): ?>margin-right: <?php echo $frontend_controller->args['listing_thumb_width']; ?>px; margin-left: 0;<?php else: ?>margin-right: 0;<?php endif; ?> } <?php endif; ?> }<?php endif; ?></style><?php endif; ?>
			<script>
			w2dc_controller_args_array['<?php echo $frontend_controller->hash; ?>'] = <?php echo json_encode(array_merge(array('controller' => $frontend_controller->request_by, 'base_url' => $frontend_controller->base_url), $frontend_controller->args)); ?>;
			</script>
			<?php if ($frontend_controller->do_initial_load): ?>
			<div class="w2dc-container-fluid w2dc-listings-block <?php if (($frontend_controller->args['listings_view_type'] == 'grid' && !isset($_COOKIE['w2dc_listings_view_'.$frontend_controller->hash])) || (isset($_COOKIE['w2dc_listings_view_'.$frontend_controller->hash]) && $_COOKIE['w2dc_listings_view_'.$frontend_controller->hash] == 'grid')): ?>w2dc-listings-grid w2dc-listings-grid-<?php echo $frontend_controller->args['listings_view_grid_columns']; ?><?php endif; ?>">
				<div class="w2dc-row w2dc-listings-block-header">
					<?php if (!$frontend_controller->args['hide_count']): ?>
					<div class="w2dc-found-listings">
						<?php printf(__('Found <span class="w2dc-badge">%d</span> %s', 'W2DC'), $frontend_controller->query->found_posts, _n($w2dc_instance->current_directory->single, $w2dc_instance->current_directory->plural, $frontend_controller->query->found_posts)); ?>
					</div>
					<?php endif; ?>
		
					<?php if ($frontend_controller->query->found_posts): ?>
					<div class="w2dc-options-links">
						<?php if ($frontend_controller->query->found_posts): ?>
						<?php if (!$frontend_controller->args['hide_order'] && !w2dc_is_relevanssi_search()): // adapted for Relevanssi ?>
						<?php $ordering = w2dc_orderLinks($frontend_controller->base_url, $frontend_controller->args, true, $frontend_controller->hash); ?>
						<?php if ($ordering['struct']):?>
						<div class="w2dc-orderby-links-label"><?php _e('Order by: ', 'W2DC'); ?></div>
						<div class="w2dc-orderby-links w2dc-btn-group" role="group">
							<?php foreach ($ordering['struct'] AS $field_slug=>$link): ?>
							<a class="w2dc-btn w2dc-btn-default <?php if ($link['class']): ?>w2dc-btn-primary<?php endif; ?>" href="<?php echo $link['url']; ?>" data-controller-hash="<?php echo $frontend_controller->hash; ?>" data-orderby="<?php echo $field_slug; ?>" data-order="<?php echo $link['order']; ?>" rel="nofollow">
								<?php if ($link['class']): ?>
								<span class="w2dc-glyphicon w2dc-glyphicon-arrow-<?php if ($link['class'] == 'ascending'): ?>up<?php elseif ($link['class'] == 'descending'): ?>down<?php endif; ?>" aria-hidden="true"></span>
								<?php endif; ?>
								<?php echo $link['field_name']; ?>
							</a>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>
						<?php endif; ?>
						<?php if ($frontend_controller->args['show_views_switcher']): ?>
						<div class="w2dc-views-links w2dc-pull-right">
							<div class="w2dc-btn-group" role="group">
								<a class="w2dc-btn <?php if (($frontend_controller->args['listings_view_type'] == 'list' && !isset($_COOKIE['w2dc_listings_view_'.$frontend_controller->hash])) || (isset($_COOKIE['w2dc_listings_view_'.$frontend_controller->hash]) && $_COOKIE['w2dc_listings_view_'.$frontend_controller->hash] == 'list')): ?>w2dc-btn-primary<?php else: ?>w2dc-btn-default<?php endif; ?> w2dc-list-view-btn" href="javascript: void(0);" title="<?php _e('List View', 'W2DC'); ?>" data-shortcode-hash="<?php echo $frontend_controller->hash; ?>">
									<span class="w2dc-glyphicon w2dc-glyphicon-list" aria-hidden="true"></span>
								</a>
								<a class="w2dc-btn <?php if (($frontend_controller->args['listings_view_type'] == 'grid' && !isset($_COOKIE['w2dc_listings_view_'.$frontend_controller->hash])) || (isset($_COOKIE['w2dc_listings_view_'.$frontend_controller->hash]) && $_COOKIE['w2dc_listings_view_'.$frontend_controller->hash] == 'grid')): ?>w2dc-btn-primary<?php else: ?>w2dc-btn-default<?php endif; ?> w2dc-grid-view-btn" href="javascript: void(0);" title="<?php _e('Grid View', 'W2DC'); ?>" data-shortcode-hash="<?php echo $frontend_controller->hash; ?>" data-columns="<?php echo $frontend_controller->args['listings_view_grid_columns']; ?>">
									<span class="w2dc-glyphicon w2dc-glyphicon-th-large" aria-hidden="true"></span>
								</a>
							</div>
						</div>
						<?php endif; ?>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</div>

				<?php if ($frontend_controller->listings): ?>
				<div class="w2dc-listings-block-content">
					<?php while ($frontend_controller->query->have_posts()): ?>
					<?php $frontend_controller->query->the_post(); ?>
					<article id="post-<?php the_ID(); ?>" class="w2dc-row w2dc-listing <?php if ($frontend_controller->listings[get_the_ID()]->level->featured) echo 'w2dc-featured'; ?> <?php if ($frontend_controller->listings[get_the_ID()]->level->sticky) echo 'w2dc-sticky'; ?>">
						<?php $frontend_controller->listings[get_the_ID()]->display(); ?>
					</article>
					<?php endwhile; ?>
				</div>

					<?php if (!$frontend_controller->args['hide_paginator']): ?>
					<?php w2dc_renderPaginator($frontend_controller->query, $frontend_controller->hash, get_option('w2dc_show_more_button')); ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>