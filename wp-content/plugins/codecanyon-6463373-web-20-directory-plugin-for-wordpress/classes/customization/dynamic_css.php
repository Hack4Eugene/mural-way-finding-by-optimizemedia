<?php if (w2dc_get_dynamic_option('w2dc_listing_title_font')): ?>
header.w2dc-listing-header h2 {
	font-size: <?php echo w2dc_get_dynamic_option('w2dc_listing_title_font'); ?>px;
}
<?php endif; ?>

<?php if (w2dc_get_dynamic_option('w2dc_links_color')): ?>
div.w2dc-content a,
div.w2dc-content a:visited,
div.w2dc-content a:focus,
div.w2dc-content h2 a,
div.w2dc-content h2 a:visited,
div.w2dc-content h2 a:focus,
div.w2dc-content.w2dc-widget a,
div.w2dc-content.w2dc-widget a:visited,
div.w2dc-content.w2dc-widget a:focus,
div.w2dc-content .w2dc-pagination > li > a,
div.w2dc-content .w2dc-pagination > li > a:visited,
div.w2dc-content .w2dc-pagination > li > a:focus,
div.w2dc-content .w2dc-btn-default, div.w2dc-content div.w2dc-btn-default:visited, div.w2dc-content .w2dc-btn-default:focus {
	color: <?php echo w2dc_get_dynamic_option('w2dc_links_color'); ?>;
}
<?php endif; ?>
<?php if (w2dc_get_dynamic_option('w2dc_links_hover_color')): ?>
div.w2dc-content a:hover,
div.w2dc-content h2 a:hover,
div.w2dc-content.w2dc-widget a:hover,
div.w2dc-content .w2dc-pagination > li > a:hover {
	color: <?php echo w2dc_get_dynamic_option('w2dc_links_hover_color'); ?>;
}
<?php endif; ?>

<?php if (w2dc_get_dynamic_option('w2dc_categories_1_color') && w2dc_get_dynamic_option('w2dc_categories_2_color')): ?>
.w2dc-content .w2dc-categories-root {
	background-color: <?php echo w2dc_get_dynamic_option('w2dc_categories_1_color'); ?>;
}
.w2dc-content .w2dc-categories-column {
	background-color: <?php echo w2dc_get_dynamic_option('w2dc_categories_2_color'); ?>;
}
<?php endif; ?>
<?php if (w2dc_get_dynamic_option('w2dc_categories_text_color')): ?>
div.w2dc-categories-columns,
div.w2dc-categories-columns a,
div.w2dc-categories-columns a:hover,
div.w2dc-categories-columns a:visited,
div.w2dc-categories-columns a:focus {
	color: <?php echo w2dc_get_dynamic_option('w2dc_categories_text_color'); ?>;
}
<?php endif; ?>

<?php if (w2dc_get_dynamic_option('w2dc_locations_1_color') && w2dc_get_dynamic_option('w2dc_locations_2_color')): ?>
.w2dc-content .w2dc-locations-root {
	background-color: <?php echo w2dc_get_dynamic_option('w2dc_locations_1_color'); ?>;
}
.w2dc-content .w2dc-locations-column {
	background-color: <?php echo w2dc_get_dynamic_option('w2dc_locations_2_color'); ?>;
}
<?php endif; ?>
<?php if (w2dc_get_dynamic_option('w2dc_locations_text_color')): ?>
div.w2dc-locations-columns,
div.w2dc-locations-columns a,
div.w2dc-locations-columns a:hover,
div.w2dc-locations-columns a:visited,
div.w2dc-locations-columns a:focus {
	color: <?php echo w2dc_get_dynamic_option('w2dc_locations_text_color'); ?>;
}
<?php endif; ?>

<?php if (w2dc_get_dynamic_option('w2dc_featured_color')): ?>
.w2dc-content .w2dc-featured,
.w2dc-content .w2dc-panel-default > .w2dc-panel-heading.w2dc-featured {
	background-color: <?php echo w2dc_get_dynamic_option('w2dc_featured_color'); ?>;
}
<?php endif; ?>

<?php if (w2dc_get_dynamic_option('w2dc_button_1_color') && w2dc_get_dynamic_option('w2dc_button_2_color') && w2dc_get_dynamic_option('w2dc_button_text_color')): ?>
<?php if (!w2dc_get_dynamic_option('w2dc_button_gradient')): ?>
div.w2dc-content .w2dc-btn-primary, div.w2dc-content a.w2dc-btn-primary, div.w2dc-content input[type="submit"], div.w2dc-content input[type="button"],
div.w2dc-content .w2dc-btn-primary:visited, div.w2dc-content a.w2dc-btn-primary:visited, div.w2dc-content input[type="submit"]:visited, div.w2dc-content input[type="button"]:visited,
div.w2dc-content .w2dc-btn-primary:focus, div.w2dc-content a.w2dc-btn-primary:focus, div.w2dc-content input[type="submit"]:focus, div.w2dc-content input[type="button"]:focus,
div.w2dc-content .w2dc-btn-primary[disabled], div.w2dc-content a.w2dc-btn-primary[disabled],
div.w2dc-content .w2dc-btn-primary[disabled]:focus, div.w2dc-content a.w2dc-btn-primary[disabled]:focus,
form.w2dc-content .w2dc-btn-primary, form.w2dc-content a.w2dc-btn-primary, form.w2dc-content input[type="submit"], form.w2dc-content input[type="button"],
form.w2dc-content .w2dc-btn-primary:visited, form.w2dc-content a.w2dc-btn-primary:visited, form.w2dc-content input[type="submit"]:visited, form.w2dc-content input[type="button"]:visited,
form.w2dc-content .w2dc-btn-primary:focus, form.w2dc-content a.w2dc-btn-primary:focus, form.w2dc-content input[type="submit"]:focus, form.w2dc-content input[type="button"]:focus,
form.w2dc-content .w2dc-btn-primary[disabled], form.w2dc-content a.w2dc-btn-primary[disabled],
form.w2dc-content .w2dc-btn-primary[disabled]:focus, form.w2dc-content a.w2dc-btn-primary[disabled]:focus,
div.w2dc-content .wpcf7-form .wpcf7-submit,
div.w2dc-content .wpcf7-form .wpcf7-submit:visited,
div.w2dc-content .wpcf7-form .wpcf7-submit:focus {
	color: <?php echo w2dc_get_dynamic_option('w2dc_button_text_color'); ?>;
	background-color: <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?>;
	background-image: none;
	border-color: <?php echo w2dc_adjust_brightness(w2dc_get_dynamic_option('w2dc_button_1_color'), -20); ?>;
}
div.w2dc-content .w2dc-btn-primary:hover, div.w2dc-content a.w2dc-btn-primary:hover, div.w2dc-content input[type="submit"]:hover, div.w2dc-content input[type="button"]:hover,
form.w2dc-content .w2dc-btn-primary:hover, form.w2dc-content a.w2dc-btn-primary:hover, form.w2dc-content input[type="submit"]:hover, form.w2dc-content input[type="button"]:hover,
div.w2dc-content .wpcf7-form .wpcf7-submit:hover {
	color: <?php echo w2dc_get_dynamic_option('w2dc_button_text_color'); ?>;
	background-color: <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?>;
	background-image: none;
	border-color: <?php echo w2dc_adjust_brightness(w2dc_get_dynamic_option('w2dc_button_2_color'), -20); ?>;
	text-decoration: none;
}
<?php else: ?>
div.w2dc-content .w2dc-btn-primary, div.w2dc-content a.w2dc-btn-primary, div.w2dc-content input[type="submit"], div.w2dc-content input[type="button"],
div.w2dc-content .w2dc-btn-primary:visited, div.w2dc-content a.w2dc-btn-primary:visited, div.w2dc-content input[type="submit"]:visited, div.w2dc-content input[type="button"]:visited,
div.w2dc-content .w2dc-btn-primary:focus, div.w2dc-content a.w2dc-btn-primary:focus, div.w2dc-content input[type="submit"]:focus, div.w2dc-content input[type="button"]:focus,
div.w2dc-content .w2dc-btn-primary[disabled], div.w2dc-content a.w2dc-btn-primary[disabled],
div.w2dc-content .w2dc-btn-primary[disabled]:focus, div.w2dc-content a.w2dc-btn-primary[disabled]:focus,
form.w2dc-content .w2dc-btn-primary, form.w2dc-content a.w2dc-btn-primary, form.w2dc-content input[type="submit"], form.w2dc-content input[type="button"],
form.w2dc-content .w2dc-btn-primary:visited, form.w2dc-content a.w2dc-btn-primary:visited, form.w2dc-content input[type="submit"]:visited, form.w2dc-content input[type="button"]:visited,
form.w2dc-content .w2dc-btn-primary:focus, form.w2dc-content a.w2dc-btn-primary:focus, form.w2dc-content input[type="submit"]:focus, form.w2dc-content input[type="button"]:focus,
form.w2dc-content .w2dc-btn-primary[disabled], form.w2dc-content a.w2dc-btn-primary[disabled],
form.w2dc-content .w2dc-btn-primary[disabled]:focus, form.w2dc-content a.w2dc-btn-primary[disabled]:focus,
div.w2dc-content .w2dc-directory-frontpanel input[type="button"],
div.w2dc-content .wpcf7-form .wpcf7-submit,
div.w2dc-content .wpcf7-form .wpcf7-submit:visited,
div.w2dc-content .wpcf7-form .wpcf7-submit:focus {
	background: <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?> !important;
	background: -moz-linear-gradient(top, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?> 100%) !important;
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?>), color-stop(100%, <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?>)) !important;
	background: -webkit-linear-gradient(top, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?> 100%) !important;
	background: -o-linear-gradient(top, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?> 100%) !important;
	background: -ms-linear-gradient(top, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?> 100%) !important;
	background: linear-gradient(to bottom, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?> 100%) !important;
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr= <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?> , endColorstr= <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?> ,GradientType=0 ) !important;
	color: <?php echo w2dc_get_dynamic_option('w2dc_button_text_color'); ?>;
	background-position: center !important;
	padding: 7px 13px;
	border: none;
}
div.w2dc-content .w2dc-btn-primary:hover, div.w2dc-content a.w2dc-btn-primary:hover, div.w2dc-content input[type="submit"]:hover, div.w2dc-content input[type="button"]:hover,
form.w2dc-content .w2dc-btn-primary:hover, form.w2dc-content a.w2dc-btn-primary:hover, form.w2dc-content input[type="submit"]:hover, form.w2dc-content input[type="button"]:hover,
div.w2dc-content .w2dc-directory-frontpanel input[type="button"]:hover,
div.w2dc-content .wpcf7-form .wpcf7-submit:hover {
	background: <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?> !important;
	background: -moz-linear-gradient(top, <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?> 100%) !important;
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?>), color-stop(100%, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?>)) !important;
	background: -webkit-linear-gradient(top, <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?> 100%) !important;
	background: -o-linear-gradient(top, <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?> 100%) !important;
	background: -ms-linear-gradient(top, <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?> 100%) !important;
	background: linear-gradient(to bottom, <?php echo w2dc_get_dynamic_option('w2dc_button_2_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?> 100%) !important;
	color: <?php echo w2dc_get_dynamic_option('w2dc_button_text_color'); ?>;
	background-position: center !important;
	/*padding: 7px 13px;*/
	border: none;
	text-decoration: none;
}
<?php endif; ?>
.w2dc-content .w2dc-map-draw-panel-wrapper button.w2dc-btn.w2dc-btn-primary {
	border-color: <?php echo w2dc_get_dynamic_option('w2dc_button_text_color'); ?>;
}
.w2dc-content select:not(.w2dc-week-day-input) {
	background-image:
	linear-gradient(50deg, transparent 50%, <?php echo w2dc_get_dynamic_option('w2dc_button_text_color'); ?> 50%),
	linear-gradient(130deg, <?php echo w2dc_get_dynamic_option('w2dc_button_text_color'); ?> 50%, transparent 50%),
	linear-gradient(to right, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?>, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?>) !important;
}
.w2dc-content select:not(.w2dc-week-day-input):focus {
	background-image:
	linear-gradient(130deg, transparent 50%, <?php echo w2dc_get_dynamic_option('w2dc_button_text_color'); ?> 50%),
	linear-gradient(50deg, <?php echo w2dc_get_dynamic_option('w2dc_button_text_color'); ?> 50%, transparent 50%),
	linear-gradient(to right, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?>, <?php echo w2dc_get_dynamic_option('w2dc_button_1_color'); ?>) !important;
}
<?php endif; ?>

<?php if (w2dc_get_dynamic_option('w2dc_search_1_color') && w2dc_get_dynamic_option('w2dc_search_2_color')): ?>
.w2dc-content.w2dc-search-form {
	background: <?php echo w2dc_get_dynamic_option('w2dc_search_1_color'); ?>;
	background: -moz-linear-gradient(top, <?php echo w2dc_get_dynamic_option('w2dc_search_1_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_search_2_color'); ?> 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, <?php echo w2dc_get_dynamic_option('w2dc_search_1_color'); ?>), color-stop(100%, <?php echo w2dc_get_dynamic_option('w2dc_search_2_color'); ?>));
	background: -webkit-linear-gradient(top, <?php echo w2dc_get_dynamic_option('w2dc_search_1_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_search_2_color'); ?> 100%);
	background: -o-linear-gradient(top, <?php echo w2dc_get_dynamic_option('w2dc_search_1_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_search_2_color'); ?> 100%);
	background: -ms-linear-gradient(top, <?php echo w2dc_get_dynamic_option('w2dc_search_1_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_search_2_color'); ?> 100%);
	background: linear-gradient(to bottom, <?php echo w2dc_get_dynamic_option('w2dc_search_1_color'); ?> 0%, <?php echo w2dc_get_dynamic_option('w2dc_search_2_color'); ?> 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr= <?php echo w2dc_get_dynamic_option('w2dc_search_1_color'); ?> , endColorstr= <?php echo w2dc_get_dynamic_option('w2dc_search_2_color'); ?> ,GradientType=0 );
	border: 1px solid #dddddd;
}
<?php endif; ?>
<?php if (w2dc_get_dynamic_option('w2dc_search_text_color')): ?>
form.w2dc-content.w2dc-search-form,
form.w2dc-content.w2dc-search-form a,
form.w2dc-content.w2dc-search-form a:hover,
form.w2dc-content.w2dc-search-form a:visited,
form.w2dc-content.w2dc-search-form a:focus,
form.w2dc-content a.w2dc-advanced-search-label,
form.w2dc-content a.w2dc-advanced-search-label:hover,
form.w2dc-content a.w2dc-advanced-search-label:visited,
form.w2dc-content a.w2dc-advanced-search-label:focus {
	color: <?php echo w2dc_get_dynamic_option('w2dc_search_text_color'); ?>;
}
<?php endif; ?>

<?php if (w2dc_get_dynamic_option('w2dc_primary_color')): ?>
.w2dc-content .w2dc-map-info-window-title {
	background-color: <?php echo w2dc_get_dynamic_option('w2dc_primary_color'); ?>;
}
.w2dc-content .w2dc-label-primary {
	background-color: <?php echo w2dc_get_dynamic_option('w2dc_primary_color'); ?>;
}
div.w2dc-content .w2dc-pagination > li.w2dc-active > a,
div.w2dc-content .w2dc-pagination > li.w2dc-active > span,
div.w2dc-content .w2dc-pagination > li.w2dc-active > a:hover,
div.w2dc-content .w2dc-pagination > li.w2dc-active > span:hover,
div.w2dc-content .w2dc-pagination > li.w2dc-active > a:focus,
div.w2dc-content .w2dc-pagination > li.w2dc-active > span:focus {
	background-color: <?php echo w2dc_get_dynamic_option('w2dc_primary_color'); ?>;
	border-color: <?php echo w2dc_get_dynamic_option('w2dc_primary_color'); ?>;
	color: #FFFFFF;
}
figure.w2dc-listing-logo figcaption {
	background-color: <?php echo w2dc_get_dynamic_option('w2dc_primary_color'); ?>;
}
.w2dc-found-listings .w2dc-badge {
	background-color: <?php echo w2dc_get_dynamic_option('w2dc_primary_color'); ?>;
}
.w2dc-content .w2dc-choose-plan:hover {
	border: 4px solid <?php echo w2dc_get_dynamic_option('w2dc_primary_color'); ?>;
}
.statVal span.ui-rater-rating {
	background-color: <?php echo w2dc_get_dynamic_option('w2dc_primary_color'); ?>;
}
.w2dc-content .w2dc-map-draw-panel-wrapper {
	background-color: <?php echo w2dc_hex2rgba(w2dc_get_dynamic_option('w2dc_primary_color'), 0.6); ?>;
}
.w2dc-content.w2dc-search-map-form .w2dc-search-overlay {
	background-color: <?php echo w2dc_hex2rgba(w2dc_get_dynamic_option('w2dc_primary_color'), 0.8); ?>;
}
<?php endif; ?>

<?php if (w2dc_get_dynamic_option('w2dc_listings_bottom_margin') >= 0): ?>
.w2dc-listings-block article.w2dc-listing {
	margin-bottom: <?php echo w2dc_get_dynamic_option('w2dc_listings_bottom_margin'); ?>px;
}
<?php endif; ?>

<?php if (w2dc_get_dynamic_option('w2dc_listing_thumb_width')): ?>
/* It works with devices width more than 768 pixels. */
@media screen and (min-width: 768px) {
	.w2dc-listings-block .w2dc-listing-logo-wrap {
		width: <?php echo w2dc_get_dynamic_option('w2dc_listing_thumb_width'); ?>px;
		<?php if (w2dc_get_dynamic_option('w2dc_wrap_logo_list_view')): ?>
		margin-right: 20px;
		margin-bottom: 10px;
		<?php endif; ?>
	}
	.rtl .w2dc-listings-block .w2dc-listing-logo-wrap {
		margin-left: 20px;
		margin-right: 0;
	}
	.w2dc-listings-block figure.w2dc-listing-logo .w2dc-listing-logo-img img {
		width: <?php echo w2dc_get_dynamic_option('w2dc_listing_thumb_width'); ?>px;
	}
	.w2dc-listings-block .w2dc-listing-text-content-wrap {
		<?php if (!w2dc_get_dynamic_option('w2dc_wrap_logo_list_view')): ?>
		margin-left: <?php echo w2dc_get_dynamic_option('w2dc_listing_thumb_width'); ?>px;
		margin-right: 0;
		<?php endif; ?>
	}
	.rtl .w2dc-listings-block .w2dc-listing-text-content-wrap {
		<?php if (!w2dc_get_dynamic_option('w2dc_wrap_logo_list_view')): ?>
		margin-right: <?php echo w2dc_get_dynamic_option('w2dc_listing_thumb_width'); ?>px;
		margin-left: 0;
		<?php endif; ?>
	}
}
<?php endif; ?>

<?php if (w2dc_get_dynamic_option('w2dc_grid_view_logo_ratio')): ?>
.w2dc-listings-block.w2dc-listings-grid figure.w2dc-listing-logo .w2dc-listing-logo-img-wrap:before {
	padding-top: <?php echo w2dc_get_dynamic_option('w2dc_grid_view_logo_ratio'); ?>%;
}
<?php endif; ?>

<?php if (w2dc_get_dynamic_option('w2dc_share_buttons_width')): ?>
.w2dc-content .w2dc-share-button img {
	max-width: <?php echo get_option('w2dc_share_buttons_width'); ?>px;
}
.w2dc-content .w2dc-share-buttons {
	height: <?php echo get_option('w2dc_share_buttons_width')+10; ?>px;
}
<?php endif; ?>

<?php if (!w2dc_get_dynamic_option('w2dc_100_single_logo_width')): ?>
/* It works with devices width more than 768 pixels. */
@media screen and (min-width: 768px) {
	.w2dc-single-listing-logo-wrap {
		max-width: <?php echo w2dc_get_dynamic_option('w2dc_single_logo_width'); ?>px;
		float: left;
		margin: 0 20px 20px 0;
	}
	.rtl .w2dc-single-listing-logo-wrap {
		float: right;
		margin: 0 0 20px 20px;
	}
	/* temporarily */
	/*.w2dc-single-listing-text-content-wrap {
		margin-left: <?php echo w2dc_get_dynamic_option('w2dc_single_logo_width')+20; ?>px;
	}*/
}
<?php endif; ?>

<?php if (w2dc_get_dynamic_option('w2dc_hide_search_on_map_mobile')): ?>
/* It works with devices width less than 768 pixels. */
@media screen and (max-width: 768px) {
	.w2dc-search-map-block {
		display: none !important;
	}
}
<?php endif; ?>

<?php if (w2dc_get_dynamic_option('w2dc_big_slide_bg_mode')): ?>
article.w2dc-listing .w2dc-single-listing-logo-wrap .w2dc-big-slide {
	background-size: <?php echo w2dc_get_dynamic_option('w2dc_big_slide_bg_mode'); ?>;
}
<?php endif; ?>