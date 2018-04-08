(function($) {
	"use strict";
	
	$(function() {
		if (level_categories.level_categories_array.length > 0)
			removeUnnecessaryCategories();
	
		function removeUnnecessaryCategories() {
			$('ul.w2dc-categorychecklist li').each(function(i) {
				if ($(this).find('>ul>li').length > 0) {
					if ($.inArray($(this).find('>label>input[type="checkbox"]').val(), level_categories.level_categories_array) == -1) {
						$(this).find('>label>input[type="checkbox"]').attr('disabled', 'disabled');
						var passed = false;
						$(this).find('>ul>li>label>input[type="checkbox"]').each(function() {
							if ($.inArray($(this).val(), level_categories.level_categories_array) != -1) {
								passed = true;
							}
						});
						if (!passed) {
							$(this).remove();
							removeUnnecessaryCategories();
							return false;
						}
					}
				} else if ($.inArray($(this).find('>label>input[type="checkbox"]').val(), level_categories.level_categories_array) == -1) {
					$(this).remove();
					removeUnnecessaryCategories();
					return false;
				}
			});
			$("ul.w2dc-categorychecklist ul.children").filter( function() {
			    return $.trim($(this).html()) == '';
			}).remove()
		}
		
		$('ul.w2dc-categorychecklist li').each(function() {
			if ($(this).children('ul').length > 0) {
				$(this).addClass('parent');
				$(this).prepend('<span class="w2dc-category-parent"></span>');
				if ($(this).find('ul input[type="checkbox"]:checked').length > 0)
					$(this).find('.w2dc-category-parent').prepend('<span class="w2dc-category-has-checked"></span>');
			} else
				$(this).prepend('<span class="w2dc-category-empty"></span>');
		});
		$('ul.w2dc-categorychecklist li ul').each(function() {
			$(this).hide();
		});
		$('ul.w2dc-categorychecklist li.parent > .w2dc-category-parent').click(function() {
			$(this).parent().toggleClass('active');
			$(this).parent().children('ul').slideToggle('fast');
		});
		$('ul.w2dc-categorychecklist li input[type="checkbox"]').change(function() {
			$('ul.w2dc-categorychecklist li').each(function() {
				if ($(this).children('ul').length > 0) {
					if ($(this).find('ul input[type="checkbox"]:checked').length > 0) {
						if ($(this).find('.w2dc-category-parent .w2dc-category-has-checked').length == 0)
							$(this).find('.w2dc-category-parent').prepend('<span class="w2dc-category-has-checked"></span>');
					} else
							$(this).find('.w2dc-category-parent .w2dc-category-has-checked').remove();
				}
			});
		});
		
		$("input[name=tax_input\\[w2dc-category\\]\\[\\]]").change(function() {w2dc_manageCategories($(this))});
		$("#w2dc-category-pop input[type=checkbox]").change(function() {w2dc_manageCategories($(this))});
		
		function w2dc_manageCategories(checked_object) {
			if (checked_object.is(":checked") && level_categories.level_categories_number != 'unlimited') {
				if ($("input[name=tax_input\\[w2dc-category\\]\\[\\]]:checked").length > level_categories.level_categories_number) {
					alert(level_categories.level_categories_notice_number);
					$("#in-w2dc-category-"+checked_object.val()).attr("checked", false);
					$("#in-popular-w2dc-category-"+checked_object.val()).attr("checked", false);
					checked_object.trigger("change");
				}
			}
	
			if (checked_object.is(":checked") && level_categories.level_categories_array.length > 0) {
				var result = false;
				if ($.inArray(checked_object.val(), level_categories.level_categories_array) == -1) {
					alert(level_categories.level_categories_notice_disallowed);
					$("#in-w2dc-category-"+checked_object.val()).attr("checked", false);
					$("#in-popular-w2dc-category-"+checked_object.val()).attr("checked", false);
					checked_object.trigger("change");
				} else
					return true;
			} else
				return true;
		}
		
		$(".w2dc-expand-terms").click(function() {
			$('ul.w2dc-categorychecklist li.parent').each(function() {
				$(this).addClass('active');
				$(this).children('ul').slideDown('fast');
			});
		});
		$(".w2dc-collapse-terms").click(function() {
			$('ul.w2dc-categorychecklist li.parent').each(function() {
				$(this).removeClass('active');
				$(this).children('ul').slideUp('fast');
			});
		});
	});
})(jQuery);
