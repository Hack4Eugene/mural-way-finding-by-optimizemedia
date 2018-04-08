<?php 

class w2dc_term_description_controller extends w2dc_frontend_controller {

	public function init($args = array()) {
		parent::init($args);

		$shortcode_atts = array_merge(array(
				
		), $args);

		$this->args = $shortcode_atts;

		apply_filters('w2dc_frontend_controller_construct', $this);
	}

	public function display() {
		global $w2dc_instance;

		$term_description = null;
		if ($directory_controller = $w2dc_instance->getShortcodeProperty('webdirectory')) {
			if ($directory_controller->is_category && $directory_controller->category->term_id)
				$term_description = term_description($directory_controller->category->term_id, W2DC_CATEGORIES_TAX);
			if ($directory_controller->is_location && $directory_controller->location->term_id)
				$term_description = term_description($directory_controller->location->term_id, W2DC_LOCATIONS_TAX);
			if ($directory_controller->is_tag && $directory_controller->tag->term_id)
				$term_description = term_description($directory_controller->tag->term_id, W2DC_TAGS_TAX);

			if ($term_description) {
				echo '<div class="archive-meta">';
					echo $term_description;
				echo '</div>';
			}
		}
	}
}

?>