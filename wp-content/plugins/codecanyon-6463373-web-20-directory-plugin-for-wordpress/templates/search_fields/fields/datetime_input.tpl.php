<script>
	(function($) {
		"use strict";
	
		$(function() {
			$("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-min-<?php echo $random_id; ?>").datepicker({
				changeMonth: true,
				changeYear: true,
				showButtonPanel: true,
				dateFormat: '<?php echo $dateformat; ?>',
				onSelect: function(dateText) {
					var tmstmp_str;
					var sDate = $("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-min-<?php echo $random_id; ?>").datepicker("getDate");
					if (sDate) {
						sDate.setMinutes(sDate.getMinutes() - sDate.getTimezoneOffset());
						tmstmp_str = $.datepicker.formatDate('@', sDate)/1000;
					} else 
						tmstmp_str = 0;
					$("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-max-<?php echo $random_id; ?>").datepicker('option', 'minDate', sDate);
	
					$("input[name=field_<?php echo $search_field->content_field->slug; ?>_min]").val(tmstmp_str);
				}
			});
			<?php
			if ($lang_code = w2dc_getDatePickerLangCode(get_locale())): ?>
			$("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-min-<?php echo $random_id; ?>").datepicker($.datepicker.regional[ "<?php echo $lang_code; ?>" ]);
			<?php endif; ?>
	
			$("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-max-<?php echo $random_id; ?>").datepicker({
				changeMonth: true,
				changeYear: true,
				showButtonPanel: true,
				dateFormat: '<?php echo $dateformat; ?>',
				onSelect: function(dateText) {
					var tmstmp_str;
					var sDate = $("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-max-<?php echo $random_id; ?>").datepicker("getDate");
					if (sDate) {
						sDate.setMinutes(sDate.getMinutes() - sDate.getTimezoneOffset());
						tmstmp_str = $.datepicker.formatDate('@', sDate)/1000;
					} else 
						tmstmp_str = 0;
					$("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-min-<?php echo $random_id; ?>").datepicker('option', 'maxDate', sDate);
	
					$("input[name=field_<?php echo $search_field->content_field->slug; ?>_max]").val(tmstmp_str);
				}
			});
			<?php
			if ($lang_code = w2dc_getDatePickerLangCode(get_locale())): ?>
			$("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-max-<?php echo $random_id; ?>").datepicker($.datepicker.regional[ "<?php echo $lang_code; ?>" ]);
			<?php endif; ?>
	
			<?php if ($search_field->min_max_value['max']): ?>
			$("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-max-<?php echo $random_id; ?>").datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', '<?php echo date('d/m/Y', $search_field->min_max_value['max']); ?>'));
			$("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-min-<?php echo $random_id; ?>").datepicker('option', 'maxDate', $("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-max-<?php echo $random_id; ?>").datepicker('getDate'));
			<?php endif; ?>
			$("#reset-date-max-<?php echo $random_id; ?>").click(function() {
				$.datepicker._clearDate('#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-max-<?php echo $random_id; ?>');
			})
	
			<?php if ($search_field->min_max_value['min']): ?>
			$("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-min-<?php echo $random_id; ?>").datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', '<?php echo date('d/m/Y', $search_field->min_max_value['min']); ?>'));
			$("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-max-<?php echo $random_id; ?>").datepicker('option', 'minDate', $("#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-min-<?php echo $random_id; ?>").datepicker('getDate'));
			<?php endif; ?>
			$("#reset-date-min-<?php echo $random_id; ?>").click(function() {
				$.datepicker._clearDate('#w2dc-field-input-<?php echo $search_field->content_field->id; ?>-min-<?php echo $random_id; ?>');
			})
		});
	})(jQuery);
</script>
<?php if ($columns == 1) $col_md = 12; else $col_md = 6; ?>
<div class="w2dc-row w2dc-field-search-block-<?php echo $search_field->content_field->id; ?> w2dc-field-search-block-<?php echo $random_id; ?> w2dc-field-search-block-<?php echo $search_field->content_field->id; ?>_<?php echo $random_id; ?>">
	<div class="w2dc-col-md-12">
		<label><?php echo $search_field->content_field->name; ?></label>
	</div>
	<div class="w2dc-col-md-<?php echo $col_md; ?> w2dc-form-group">
		<div class="w2dc-row w2dc-form-horizontal">
			<div class="w2dc-col-md-7 w2dc-has-feedback">
				<input type="text" class="w2dc-form-control" id="w2dc-field-input-<?php echo $search_field->content_field->id; ?>-min-<?php echo $random_id; ?>" placeholder="<?php esc_attr_e('Start date', 'W2DC'); ?>" />
				<span class="w2dc-glyphicon w2dc-glyphicon-calendar w2dc-form-control-feedback"></span>
				<input type="hidden" name="field_<?php echo $search_field->content_field->slug; ?>_min" value="<?php echo esc_attr($search_field->min_max_value['min']); ?>"/>
			</div>
			<div class="w2dc-col-md-5">
				<input type="button" class="w2dc-btn w2dc-btn-primary w2dc-form-control" id="reset-date-min-<?php echo $random_id; ?>" value="<?php esc_attr_e('reset date', 'W2DC')?>" />
			</div>
		</div>
	</div>
	<div class="w2dc-col-md-<?php echo $col_md; ?> w2dc-form-group">
		<div class="w2dc-row w2dc-form-horizontal">
			<div class="w2dc-col-md-7 w2dc-has-feedback">
				<input type="text" class="w2dc-form-control" id="w2dc-field-input-<?php echo $search_field->content_field->id; ?>-max-<?php echo $random_id; ?>" placeholder="<?php esc_attr_e('End date', 'W2DC'); ?>" />
				<span class="w2dc-glyphicon w2dc-glyphicon-calendar w2dc-form-control-feedback"></span>
				<input type="hidden" name="field_<?php echo $search_field->content_field->slug; ?>_max" value="<?php echo esc_attr($search_field->min_max_value['max']); ?>"/>
			</div>
			<div class="w2dc-col-md-5">
				<input type="button" class="w2dc-btn w2dc-btn-primary w2dc-form-control" id="reset-date-max-<?php echo $random_id; ?>" value="<?php esc_attr_e('reset date', 'W2DC')?>" />
			</div>
		</div>
	</div>
</div>