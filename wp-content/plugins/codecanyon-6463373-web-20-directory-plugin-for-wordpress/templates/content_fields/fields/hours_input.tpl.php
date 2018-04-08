<div class="w2dc-form-group w2dc-field w2dc-field-input-block w2dc-field-input-block-<?php echo $content_field->id; ?>">
	<label class="w2dc-col-md-2 w2dc-control-label"><?php echo $content_field->name; ?></label>
	<div class="w2dc-col-md-10">
		<?php foreach ($week_days AS $key=>$day): ?>
		<div class="w2dc-week-day-wrap">
			<span class="w2dc-week-day"><?php echo $content_field->week_days_names[$key]; ?></span> <span class="w2dc-week-day-controls"><select name="<?php echo $day; ?>_from_hour_<?php echo $content_field->id; ?>" class="w2dc-week-day-input" /><?php echo $content_field->getOptionsHour($day.'_from'); ?></select>:<select name="<?php echo $day; ?>_from_minute_<?php echo $content_field->id; ?>" class="w2dc-week-day-input" /><?php echo $content_field->getOptionsMinute($day.'_from'); ?></select><?php if ($content_field->hours_clock == 12): ?> <select name="<?php echo $day; ?>_from_am_pm_<?php echo $content_field->id; ?>" class="w2dc-week-day-input" /><?php echo $content_field->getOptionsAmPm($day.'_from'); ?></select><?php endif; ?></span> &nbsp;&nbsp;-&nbsp;&nbsp; <span class="w2dc-week-day-controls"><select name="<?php echo $day; ?>_to_hour_<?php echo $content_field->id; ?>" class="w2dc-week-day-input" /><?php echo $content_field->getOptionsHour($day.'_to'); ?></select>:<select name="<?php echo $day; ?>_to_minute_<?php echo $content_field->id; ?>" class="w2dc-week-day-input" /><?php echo $content_field->getOptionsMinute($day.'_to'); ?></select><?php if ($content_field->hours_clock == 12): ?> <select name="<?php echo $day; ?>_to_am_pm_<?php echo $content_field->id; ?>" class="w2dc-week-day-input" /><?php echo $content_field->getOptionsAmPm($day.'_to'); ?></select><?php endif; ?></span> <input type="checkbox" name="<?php echo $day; ?>_closed_<?php echo $content_field->id; ?>" <?php checked($content_field->value[$day.'_closed'], 1); ?> class="closed_cb" value="1" /> <?php _e('Closed', 'W2DC'); ?>
		</div>
		<?php endforeach; ?>
		<div class="w2dc-week-day-clear-button">
			<script>
				(function($) {
					"use strict";
	
					$(function() {
						$("#clear_hours_<?php echo $content_field->id; ?>").click( function() {
							$("#clear_hours_<?php echo $content_field->id; ?>").parents(".w2dc-field-input-block-<?php echo $content_field->id; ?>").find('select').each( function() { $(this).val($(this).find("option:first").val()).removeAttr('disabled'); });
							$("#clear_hours_<?php echo $content_field->id; ?>").parents(".w2dc-field-input-block-<?php echo $content_field->id; ?>").find('input[type="checkbox"]').each( function() { $(this).attr('checked', false); });
							return false;
						});
					});
				})(jQuery);
			</script>
			<button id="clear_hours_<?php echo $content_field->id; ?>" class="w2dc-btn w2dc-btn-primary"><?php _e('Reset hours & minutes', 'W2DC'); ?></button>
		</div>
	</div>
</div>