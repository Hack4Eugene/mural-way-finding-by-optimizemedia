<table class="app-message-log widefat">
	<tr>
		<th><?php _e('Logged Date', 'W2DC'); ?></th>
		<th><?php _e('Message', 'W2DC'); ?></th>
	</tr>
	<?php $logs = $invoice->log; ?>
	<?php krsort($logs, SORT_NUMERIC); ?>
	<?php foreach ($logs AS $date=>$message): ?>
	<tr class="major">
		<td>
			<span class="timestamp">
				<?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), intval($date)); ?>
			</span>
		</td>
		<td>
			<span class="message"><?php echo $message; ?></span>
		</td>
	</tr>
	<?php endforeach; ?>
</table>