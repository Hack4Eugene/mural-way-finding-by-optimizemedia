		<div class="w2dc-content w2dc-category-page">
			<?php w2dc_renderMessages(); ?>
			
			<?php w2dc_renderTemplate('frontend/frontpanel_buttons.tpl.php'); ?>

			<?php if ($frontend_controller->getPageTitle()): ?>
			<header class="w2dc-page-header">
				<h2>
					<?php echo $frontend_controller->getPageTitle(); ?>
				</h2>

				<?php if ($frontend_controller->breadcrumbs): ?>
				<ol class="w2dc-breadcrumbs">
					<?php echo $frontend_controller->getBreadCrumbs(); ?>
				</ol>
				<?php endif; ?>

				<?php if (term_description($frontend_controller->category->term_id, W2DC_CATEGORIES_TAX)): ?>
				<div class="archive-meta"><?php echo term_description($frontend_controller->category->term_id, W2DC_CATEGORIES_TAX); ?></div>
				<?php endif; ?>
			</header>
			<?php endif; ?>
	
			<?php
			if (get_option('w2dc_main_search'))
				$frontend_controller->search_form->display();
			?>

			<?php 
			if ($w2dc_instance->current_directory->categories)
				$exact_categories = $w2dc_instance->current_directory->categories;
			else
				$exact_categories = array();
			?>
			<?php w2dc_renderSubCategories(get_query_var('category-w2dc'), get_option('w2dc_categories_columns'), get_option('w2dc_show_category_count'), $exact_categories); ?>

			<?php if (get_option('w2dc_map_on_excerpt')): ?>
			<?php $frontend_controller->google_map->display(false, false, get_option('w2dc_enable_radius_search_circle'), get_option('w2dc_enable_clusters'), true, true, false, get_option('w2dc_default_map_height'), false, 10, get_option('w2dc_map_style'), get_option('w2dc_search_on_map'), get_option('w2dc_enable_draw_panel'), false, get_option('w2dc_enable_full_screen'), get_option('w2dc_enable_wheel_zoom'), get_option('w2dc_enable_dragging_touchscreens'), get_option('w2dc_center_map_onclick')); ?>
			<?php endif; ?>

			<?php w2dc_renderTemplate('frontend/listings_block.tpl.php', array('frontend_controller' => $frontend_controller)); ?>
		</div>