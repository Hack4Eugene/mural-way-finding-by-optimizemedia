<?php w2dc_renderTemplate('admin_header.tpl.php'); ?>

<?php screen_icon('edit-pages'); ?>
<h2>
	<?php _e('Directory Reset', 'W2DC'); ?>
</h2>

<h3>Are you sure you want to reset settings?</h3>
<a href="<?php echo admin_url('admin.php?page=w2dc_reset&reset=settings'); ?>">Reset settings</a>

<?php w2dc_renderTemplate('admin_footer.tpl.php'); ?>