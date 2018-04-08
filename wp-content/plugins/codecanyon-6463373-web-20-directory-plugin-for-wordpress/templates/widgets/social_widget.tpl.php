<?php echo $args['before_widget']; ?>
<?php if (!empty($title))
echo $args['before_title'] . $title . $args['after_title'];
?>
<div class="w2dc-content w2dc-widget w2dc-social-widget">
	<ul class="w2dc-social w2dc-clearfix">
		<?php if ($instance['is_facebook']): ?>
		<li>
			<a target="_blank" href="<?php echo esc_url($instance['facebook']); ?>">
				<img src="<?php echo W2DC_RESOURCES_URL; ?>images/social/social_facebook_box_blue.png">
			</a>
		</li>
		<?php endif; ?>

		<?php if ($instance['is_twitter']): ?>
		<li>
			<a target="_blank" href="<?php echo esc_url($instance['twitter']); ?>">
				<img src="<?php echo W2DC_RESOURCES_URL; ?>images/social/social_twitter_box_blue.png">
			</a>
		</li>
		<?php endif; ?>
		
		<?php if ($instance['is_google']): ?>
		<li>
			<a target="_blank" href="<?php echo esc_url($instance['google']); ?>">
				<img src="<?php echo W2DC_RESOURCES_URL; ?>images/social/social_google_box_blue.png">
			</a>
		</li>
		<?php endif; ?>
		
		<?php if ($instance['is_linkedin']): ?>
		<li>
			<a target="_blank" href="<?php echo esc_url($instance['linkedin']); ?>">
				<img src="<?php echo W2DC_RESOURCES_URL; ?>images/social/social_linkedin_box_blue.png">
			</a>
		</li>
		<?php endif; ?>
		
		<?php if ($instance['is_youtube']): ?>
		<li>
			<a target="_blank" href="<?php echo esc_url($instance['youtube']); ?>">
				<img src="<?php echo W2DC_RESOURCES_URL; ?>images/social/social_youtube.png">
			</a>
		</li>
		<?php endif; ?>
		
		<?php if ($instance['is_rss']): ?>
		<li>
			<a target="_blank" href="<?php echo esc_url($instance['rss']); ?>">
				<img src="<?php echo W2DC_RESOURCES_URL; ?>images/social/social_rss_box_orange.png">
			</a>
		</li>
		<?php endif; ?>
	</ul>
</div>
<?php echo $args['after_widget']; ?>