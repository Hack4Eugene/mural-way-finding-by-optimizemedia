<?php echo $args['before_widget']; ?>
<?php if (!empty($title))
echo $args['before_title'] . $title . $args['after_title'];
?>
<div class="w2dc-content w2dc-widget w2dc_recent_listings_widget">
	<ul>
		<?php foreach ($listings AS $listing): ?>
		<li class="w2dc-widget-listing w2dc-clearfix<?php if ($listing->level->featured): ?> w2dc-featured<?php endif; ?>">
			<div class="w2dc-widget-listing-logo">
			<?php if ($listing->logo_image): ?>
				<?php $img_src = $listing->get_logo_url(array(45, 45)); ?>
			<?php else: ?>
				<?php $img_src = get_option('w2dc_nologo_url'); ?>
			<?php endif; ?>
			<?php if ($listing->level->listings_own_page): ?>
				<a href="<?php echo get_permalink($listing->post->ID); ?>" title="<?php echo esc_attr($listing->title()); ?>" <?php if ($listing->level->nofollow): ?>rel="nofollow"<?php endif; ?>>
					<img src="<?php echo $img_src; ?>" width="45" />
				</a>
			<?php else: ?>
				<img src="<?php echo $img_src; ?>" width="45" />
			<?php endif; ?>
			</div>
	
			<div class="w2dc-widget-listing-title">
				<?php if (!$listing->level->listings_own_page): ?>
				<?php echo $listing->title(); ?>
				<?php else: ?>
				<a href="<?php echo get_permalink($listing->post->ID); ?>" title="<?php echo esc_attr($listing->title()); ?>" <?php if ($listing->level->nofollow): ?>rel="nofollow"<?php endif; ?>><?php echo $listing->title(); ?></a>
				<?php endif; ?>
				<?php if (!get_option('w2dc_hide_listings_creation_date')): ?>
				<br />
				<?php echo human_time_diff(mysql2date('U', $listing->post->post_date), time()); ?> <?php _e('ago', 'W2DC'); ?>
				<?php endif; ?>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php echo $args['after_widget']; ?>