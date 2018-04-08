<?php 

class w2dc_breadcrumbs_controller extends w2dc_frontend_controller {

	public function init($args = array()) {
		parent::init($args);

		$shortcode_atts = array_merge(array(
				
		), $args);

		$this->args = $shortcode_atts;

		apply_filters('w2dc_frontend_controller_construct', $this);
	}

	public function display() {
		global $w2dc_instance;

		if ($directory_controller = $w2dc_instance->getShortcodeProperty('webdirectory')) {
			if ($breadcrumbs = $directory_controller->getBreadCrumbs()) {
				echo '<div class="w2dc-content">';
					echo '<ol class="w2dc-breadcrumbs">';
						echo $breadcrumbs;
					echo '</ol>';
				echo '</div>';
			}
		}
	}
}

?>