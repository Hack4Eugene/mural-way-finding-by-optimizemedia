<?php if ($content_field->value): ?>
<div class="w2dc-field w2dc-field-output-block w2dc-field-output-block-<?php echo $content_field->type; ?> w2dc-field-output-block-<?php echo $content_field->id; ?>">
	<?php if ($content_field->icon_image || !$content_field->is_hide_name): ?>
	<span class="w2dc-field-caption">
		<?php if ($content_field->icon_image): ?>
		<span class="w2dc-field-icon w2dc-fa w2dc-fa-lg <?php echo $content_field->icon_image; ?>"></span>
		<?php endif; ?>
		<?php if (!$content_field->is_hide_name): ?>
		<span class="w2dc-field-name"><?php echo $content_field->name?>:</span>
		<?php endif; ?>
	</span>
	<?php endif; ?>
	<span class="w2dc-field-content">
		<?php if ($content_field->is_phone && wp_is_mobile()): ?><a href="tel:<?php echo $content_field->value; ?>"><?php endif; ?>
		<?php if ($content_field->is_phone) echo antispambot($content_field->value); else echo $content_field->value; ?>
		<?php if ($content_field->is_phone && wp_is_mobile()): ?></a><?php endif; ?>
	</span>
</div>
<?php endif; ?>