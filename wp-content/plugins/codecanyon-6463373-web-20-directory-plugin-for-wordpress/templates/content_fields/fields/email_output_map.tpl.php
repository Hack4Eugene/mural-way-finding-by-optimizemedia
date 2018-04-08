<?php if ($content_field->value): ?>
	<a href="mailto:<?php echo antispambot($content_field->value); ?>"><?php echo antispambot($content_field->value); ?></a>
<?php endif; ?>