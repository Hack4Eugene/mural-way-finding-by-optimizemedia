<script>
	(function($) {
		"use strict";
	
		$(function() {
			$("#w2dc-field-input-<?php echo $settings['field_id']; ?>_min").datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: '<?php echo $dateformat; ?>',
				onSelect: function(dateText) {
					var tmstmp_str;
					var sDate = $("#w2dc-field-input-<?php echo $settings['field_id']; ?>_min").datepicker("getDate");
					if (sDate) {
						sDate.setMinutes(sDate.getMinutes() - sDate.getTimezoneOffset());
						tmstmp_str = $.datepicker.formatDate('@', sDate)/1000;
					} else 
						tmstmp_str = 0;
					$("#w2dc-field-input-<?php echo $settings['field_id']; ?>_max").datepicker('option', 'minDate', sDate);
	
					$("input[name=<?php echo $settings['param_name']; ?>]").val(tmstmp_str);
				}
			});
			<?php
			if ($lang_code = w2dc_getDatePickerLangCode(get_locale())): ?>
			$("#w2dc-field-input-<?php echo $settings['field_id']; ?>_min").datepicker($.datepicker.regional[ "<?php echo $lang_code; ?>" ]);
			<?php endif; ?>
	
			<?php if ($value): ?>
			$("#w2dc-field-input-<?php echo $settings['field_id']; ?>_min").datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', '<?php echo date('d/m/Y', $value); ?>'));
			$("#w2dc-field-input-<?php echo $settings['field_id']; ?>_max").datepicker('option', 'minDate', $("#w2dc-field-input-<?php echo $settings['field_id']; ?>_min").datepicker('getDate'));
			<?php endif; ?>
			$("#reset_date_min_<?php echo $settings['field_id']; ?>").click(function() {
				$.datepicker._clearDate('#w2dc-field-input-<?php echo $settings['field_id']; ?>_min');
			})
		});
	})(jQuery);
</script>
<input type="text" id="w2dc-field-input-<?php echo $settings['field_id']; ?>_min" placeholder="<?php esc_attr_e('Start date', 'W2DC'); ?>" class="w2dc-form-control" size="9" />
<input type="hidden" name="<?php echo $settings['param_name']; ?>" value="<?php echo esc_attr($value); ?>" class="wpb_vc_param_value" />
<input type="button" id="reset_date_min_<?php echo $settings['field_id']; ?>" value="<?php esc_attr_e('reset date', 'W2DC')?>" />