<?php w2dc_renderTemplate('admin_header.tpl.php'); ?>

<?php screen_icon('edit-pages'); ?>
<h2>
	<?php _e('Directory Debug', 'W2DC'); ?>
</h2>

<textarea style="width: 100%; height: 700px">
geolocation response = <?php var_dump($geolocation_response); ?>


$w2dc_instance->index_pages_all = <?php var_dump($w2dc_instance->index_pages_all); ?>

$w2dc_instance->listing_page_id = <?php echo $w2dc_instance->listing_page_id; ?>

<?php if (isset($w2dc_instance->submit_page_id)): ?>
$w2dc_instance->submit_page_id = <?php echo $w2dc_instance->submit_page_id; ?>
<?php endif; ?>

<?php if (isset($w2dc_instance->dashboard_page_id)): ?>
$w2dc_instance->dashboard_page_id = <?php echo $w2dc_instance->dashboard_page_id; ?>
<?php endif; ?>

<?php foreach ($rewrite_rules AS $key=>$rule)
echo $key . '
' . $rule . '

';
?>


<?php foreach ($settings AS $setting)
echo $setting['option_name'] . ' = ' . $setting['option_value'] . '

';
?>


<?php var_dump($levels); ?>


<?php var_dump($content_fields); ?>
</textarea>

<?php w2dc_renderTemplate('admin_footer.tpl.php'); ?>